<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\LoginRequest;
use App\Models\Business\BusinessUser;
use App\Models\Business\OfficeUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * مصادقة الـ API التوكنية (Sanctum) للواجهة الأمامية المنفصلة.
 *
 * الواجهة (React) على سيرفر مختلف ترسل:
 *   headers: { Authorization: Bearer <token> }
 *
 * الرد الموحّد: { isSuccess, value, error, statusCode }
 */
class ApiAuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     * تسجيل الدخول كـ Business (عميل/أدمن/مشرف) أو Office.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        // 1) محاولة Business
        $user = BusinessUser::where('email', $credentials['email'])->first();
        if ($user && Hash::check($credentials['password'], $user->password)) {
            if (!($user->is_active ?? true)) {
                return response()->json([
                    'isSuccess'  => false,
                    'value'      => null,
                    'error'      => ['message' => 'حسابك موقوف. تواصل مع الإدارة.', 'code' => 'ACCOUNT_DISABLED'],
                    'statusCode' => 403,
                ], 403);
            }

            $token = $user->createToken('api-token', ['business'])->plainTextToken;

            return response()->json([
                'isSuccess'  => true,
                'value'      => [
                    'token'      => $token,
                    'token_type' => 'Bearer',
                    'user'       => $this->businessPayload($user),
                ],
                'error'      => null,
                'statusCode' => 200,
            ]);
        }

        // 2) محاولة Office
        $officeUser = OfficeUser::where('email', $credentials['email'])->first();
        if ($officeUser && Hash::check($credentials['password'], $officeUser->password)) {
            if (
                !($officeUser->is_active ?? false) ||
                !$officeUser->office ||
                !($officeUser->office->is_active ?? false)
            ) {
                return response()->json([
                    'isSuccess'  => false,
                    'value'      => null,
                    'error'      => ['message' => 'حساب المكتب موقوف. تواصل مع الإدارة.', 'code' => 'ACCOUNT_DISABLED'],
                    'statusCode' => 403,
                ], 403);
            }

            $token = $officeUser->createToken('api-token', ['office'])->plainTextToken;

            return response()->json([
                'isSuccess'  => true,
                'value'      => [
                    'token'      => $token,
                    'token_type' => 'Bearer',
                    'user'       => [
                        'id'          => $officeUser->id,
                        'name'        => $officeUser->name,
                        'email'       => $officeUser->email,
                        'role'        => $officeUser->role,
                        'type'        => 'office',
                        'office_id'   => $officeUser->office_id,
                        'is_active'   => (bool) $officeUser->is_active,
                    ],
                ],
                'error'      => null,
                'statusCode' => 200,
            ]);
        }

        // 3) فشل
        return response()->json([
            'isSuccess'  => false,
            'value'      => null,
            'error'      => ['message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.', 'code' => 'INVALID_CREDENTIALS'],
            'statusCode' => 401,
        ], 401);
    }

    /**
     * POST /api/v1/auth/register
     * إنشاء حساب Business جديد وإرجاع توكن مباشرة.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', 'unique:App\Models\Business\BusinessUser,email'],
            'phone'       => ['required', 'string', 'max:30'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
            'account_type' => ['nullable', 'in:establishment,individual'],
        ]);

        $accountType = $request->input('account_type', 'individual');

        $user = BusinessUser::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'phone'        => $validated['phone'],
            'password'     => Hash::make($validated['password']),
            'role'         => 'user',
            'account_type' => $accountType,
            'is_active'    => true,
        ]);

        $token = $user->createToken('api-token', ['business'])->plainTextToken;

        return response()->json([
            'isSuccess'  => true,
            'value'      => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => $this->businessPayload($user),
            ],
            'error'      => null,
            'statusCode' => 201,
        ], 201);
    }

    /**
     * GET /api/v1/auth/me
     * المستخدم الحالي. يعمل مع Business أو Office عبر التوكن.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'isSuccess'  => false,
                'value'      => null,
                'error'      => ['message' => 'غير مصادق عليه.', 'code' => 'UNAUTHENTICATED'],
                'statusCode' => 401,
            ], 401);
        }

        if ($user instanceof BusinessUser) {
            $payload = $this->businessPayload($user);
        } elseif ($user instanceof OfficeUser) {
            $payload = [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'role'      => $user->role,
                'type'      => 'office',
                'office_id' => $user->office_id,
                'is_active' => (bool) $user->is_active,
            ];
        } else {
            $payload = $user->toArray();
        }

        return response()->json([
            'isSuccess'  => true,
            'value'      => ['user' => $payload],
            'error'      => null,
            'statusCode' => 200,
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     * إلغاء التوكن الحالي.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()?->delete();
        }

        return response()->json([
            'isSuccess'  => true,
            'value'      => ['message' => 'تم تسجيل الخروج بنجاح.'],
            'error'      => null,
            'statusCode' => 200,
        ]);
    }

    private function businessPayload(BusinessUser $user): array
    {
        return [
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'phone'           => $user->phone,
            'role'            => $user->role,
            'type'            => 'business',
            'account_type'    => $user->account_type,
            'is_active'       => (bool) ($user->is_active ?? true),
            'is_admin'        => $user->isAdmin(),
            'profile_photo'   => $user->profile_photo,
            'city'            => $user->city,
            'region'          => $user->region,
        ];
    }
}
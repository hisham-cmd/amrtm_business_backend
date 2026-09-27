<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\LoginRequest;
use App\Models\Business\BusinessUser;
use App\Models\Business\OfficeUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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
     *
     * يقبل حسابين:
     *   - individual   : عميل فرد  (اسم الأب/الجد/العائلة، رقم الهوية، القطاع، الحالة الوظيفية)
     *   - establishment: عميل منشأة (الاسم النظامي، نوع الكيان، السجل التجاري، العنوان…)
     *
     * ⚠️ كان يقبل 5 حقول فقط ويحفظها، فكانت كل بيانات النموذج الأخرى تُسقط
     *    بصمت رغم وجودها في BusinessUser::$fillable. نتحقق ونحفظها الآن.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', 'unique:App\Models\Business\BusinessUser,email'],
            'phone'       => ['required', 'string', 'max:30'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
            'account_type' => ['nullable', 'in:establishment,individual'],

            // ── عميل فرد ──────────────────────────────────────────────
            'father_name'      => ['nullable', 'string', 'max:255'],
            'grandfather_name' => ['nullable', 'string', 'max:255'],
            'family_name'      => ['nullable', 'string', 'max:255'],
            'id_number'        => ['nullable', 'string', 'max:30'],
            'job_sector'       => ['nullable', 'in:government,private'],
            'employment_status'=> ['nullable', 'in:retired,affiliated'],

            // ── عميل منشأة ────────────────────────────────────────────
            'legal_name'            => ['nullable', 'string', 'max:255'],
            'entity_type'           => ['nullable', 'string', 'max:100'],
            'representative_name'   => ['nullable', 'string', 'max:255'],
            'representative_role'   => ['nullable', 'string', 'max:255'],
            'cr_number'             => ['nullable', 'string', 'max:50'],
            'cr_expiry_date'        => ['nullable', 'date'],
            'license_expiry_date'   => ['nullable', 'date'],

            // ── عنوان مشترك ───────────────────────────────────────────
            'phone_dial'      => ['nullable', 'string', 'max:10'],
            'country'         => ['nullable', 'string', 'max:100'],
            'region'          => ['nullable', 'string', 'max:100'],
            'city'            => ['nullable', 'string', 'max:100'],
            'district'        => ['nullable', 'string', 'max:100'],
            'street'          => ['nullable', 'string', 'max:255'],
            'building_number' => ['nullable', 'string', 'max:50'],
            'office_number'   => ['nullable', 'string', 'max:50'],
            'postal_code'     => ['nullable', 'string', 'max:20'],

            // ── ملف الصورة الشخصية ─────────────────────────────────────
            'profile_photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $accountType = $validated['account_type'] ?? 'individual';

        /*
         * الحقول التي تُحفظ كما هي (نصية) — مطابقة لـ BusinessUser::$fillable.
         * لا نحفظ ما لم يرسله النموذج إطلاقاً.
         */
        $persisted = [
            'phone_dial', 'country', 'region', 'city', 'district', 'street',
            'building_number', 'office_number', 'postal_code',
            'father_name', 'grandfather_name', 'family_name', 'id_number',
            'job_sector', 'employment_status',
            'legal_name', 'entity_type', 'representative_name', 'representative_role',
            'cr_number', 'cr_expiry_date', 'license_expiry_date',
        ];

        $data = [
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'phone'        => $validated['phone'],
            'password'     => Hash::make($validated['password']),
            'role'         => 'user',
            'account_type' => $accountType,
            'is_active'    => true,
        ];

        foreach ($persisted as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] !== null && $validated[$field] !== '') {
                $data[$field] = $validated[$field];
            }
        }

        /*
         * الصورة الشخصية.
         *
         * ⚠️ لا نستخدم ->store() على disk public هنا: BusinessUser::getAvatarUrlAttribute()
         * يبني العنوان من  '/media/uploads/' . $file  ، و MediaController يخدم
         * الملف من  public_path('images/uploads/')  وباسم **مسطّح** فقط
         * (يرفض أي مسار يحتوي '/')  عبر MediaController::show.
         * لذلك نكتب الملف في public/images/uploads ونخزّن الاسم فقط.
         */
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $dir  = public_path('images/uploads');
            File::ensureDirectoryExists($dir);

            $ext  = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $name = 'avatar-' . bin2hex(random_bytes(12)) . '.' . $ext;
            $file->move($dir, $name);

            $data['profile_photo'] = $name;
        }

        $user = BusinessUser::create($data);

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
            // رابط الصورة الحقيقية من قاعدة البيانات، أو null — لا صورة رمزية مولّدة.
            'avatar_url'      => $user->avatar_url,
            'initials'        => $user->initials,
            'city'            => $user->city,
            'region'          => $user->region,
            // حقول العميل الفرد
            'father_name'     => $user->father_name,
            'family_name'     => $user->family_name,
            'id_number'       => $user->id_number,
            'job_sector'      => $user->job_sector,
            'employment_status' => $user->employment_status,
            // حقول عميل المنشأة
            'legal_name'      => $user->legal_name,
            'entity_type'     => $user->entity_type,
            'cr_number'       => $user->cr_number,
            'cr_expiry_date'  => $user->cr_expiry_date,
        ];
    }
}
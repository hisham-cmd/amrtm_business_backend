<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\LoginRequest;
use App\Http\Requests\Business\RegisterRequest;
use App\Models\Business\BusinessUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AmrtmAuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('amrtm.auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        /*
        |--------------------------------------------------------------------------
        | 1) محاولة تسجيل الدخول كمستخدم Business
        |--------------------------------------------------------------------------
        | يشمل:
        | - العميل role = user
        | - الأدمن role = admin
        | - المشرف role = supervisor
        |--------------------------------------------------------------------------
        */

        if (Auth::guard('business')->attempt($credentials, $remember)) {

            $user = Auth::guard('business')->user();

            /*
            | التحقق من حالة الحساب
            */

            if (!($user->is_active ?? true)) {

                Auth::guard('business')->logout();

                return back()
                    ->withErrors([
                        'email' => 'حسابك موقوف. تواصل مع الإدارة.',
                    ])
                    ->withInput();
            }

            /*
            | تجديد الجلسة
            */

            $request->session()->regenerate();

            /*
            |--------------------------------------------------------------------------
            | Admin / Supervisor
            |--------------------------------------------------------------------------
            */

            if (
                method_exists($user, 'isAdmin') &&
                $user->isAdmin()
            ) {
                return redirect()->route('amrtm.dashboard.hub');
            }

            /*
            |--------------------------------------------------------------------------
            | Business User / Client
            |--------------------------------------------------------------------------
            */

            return redirect()->to(
                $this->safeRedirect((string) $request->input('redirect')) ?? route('amrtm.dashboard.hub')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 2) لو مش Business نجرب Office
        |--------------------------------------------------------------------------
        */

        if (Auth::guard('office')->attempt($credentials, $remember)) {

            $officeUser = Auth::guard('office')->user();

            /*
            |--------------------------------------------------------------------------
            | التحقق من حالة مستخدم المكتب والمكتب نفسه
            |--------------------------------------------------------------------------
            */

            if (
                !($officeUser->is_active ?? false) ||
                !$officeUser->office ||
                !($officeUser->office->is_active ?? false)
            ) {

                Auth::guard('office')->logout();

                return back()
                    ->withErrors([
                        'email' => 'حساب المكتب موقوف. تواصل مع الإدارة.',
                    ])
                    ->withInput();
            }


            /*
            |--------------------------------------------------------------------------
            | تجديد الجلسة
            |--------------------------------------------------------------------------
            */

            $request->session()->regenerate();


            /*
            |--------------------------------------------------------------------------
            | التوجه إلى لوحة التحكم مباشرة
            |--------------------------------------------------------------------------
            | بيانات المكتب تُعبأ كاملة عند إنشاء الحساب عبر /provider-account/create،
            | لذلك لا حاجة لصفحة /office/complete.
            */

            return redirect()->to(
                $this->safeRedirect((string) $request->input('redirect')) ?? route('amrtm.dashboard.hub')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 3) لا Business ولا Office
        |--------------------------------------------------------------------------
        */

        return back()
            ->withErrors([
                'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
            ])
            ->withInput();
    }
    public function showRegisterForm(): RedirectResponse
    {
        return redirect()
            ->route('amrtm.login')
            ->with('_auth_mode', 'register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | الهوية الموثّقة عبر النفاذ الوطني (اختياري)
        |--------------------------------------------------------------------------
        | إن أكمل المستخدم التحقق من هويته عبر نفاذ من نفس الجلسة، تُرفق
        | الهوية الموثّقة بالحساب الجديد، ويُرفض أي رقم هوية مخالف لها.
        */

        $verifiedIdentity = $request->session()->get('nafath_verified');
        $requestedIdNumber = $request->id_number;

        if (is_array($verifiedIdentity) && isset($verifiedIdentity['national_id'])) {
            if ($requestedIdNumber && $requestedIdNumber !== $verifiedIdentity['national_id']) {
                return back()
                    ->withErrors(['id_number' => 'رقم الهوية لا يطابق الهوية الموثّقة عبر نفاذ.'])
                    ->withInput();
            }

            $requestedIdNumber = $verifiedIdentity['national_id'];
        }

        $accountType = in_array($request->input('account_type'), ['establishment', 'individual'], true)
            ? $request->input('account_type')
            : 'individual';

        $profilePhoto = null;
        if ($request->hasFile('profile_photo')) {
            $photo = $request->file('profile_photo');
            $name = 'profile_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $photo->getClientOriginalName());
            $profilePhoto = $photo->storeAs('client-profiles', $name, 'public');
        }

        $user = BusinessUser::create([
            'name' => $request->name,
            'father_name' => $request->father_name,
            'grandfather_name' => $request->grandfather_name,
            'family_name' => $request->family_name,
            'id_number' => $requestedIdNumber,
            'nafath_verified_at' => is_array($verifiedIdentity) && isset($verifiedIdentity['verified_at'])
                ? $verifiedIdentity['verified_at']
                : null,
            'job_sector' => $request->job_sector,
            'employment_status' => $request->employment_status,
            'profile_photo' => $profilePhoto,
            'email' => $request->email,
            'phone' => $request->phone,
            'phone_dial' => $request->phone_dial,
            'role' => 'user',
            'account_type' => $accountType,
            'password' => Hash::make($request->password),
            'legal_name' => $request->legal_name,
            'entity_type' => $request->entity_type,
            'cr_number' => $request->cr_number,
            'cr_expiry_date' => $request->cr_expiry_date,
            'license_expiry_date' => $request->license_expiry_date,
            'country' => $request->country,
            'region' => $request->region,
            'city' => $request->city,
            'district' => $request->district,
            'street' => $request->street,
            'building_number' => $request->building_number,
            'office_number' => $request->office_number,
        ]);

        $request->session()->forget('nafath_verified');

        Auth::guard('business')->login($user);
        $request->session()->regenerate();

        return redirect()->route('amrtm.dashboard.hub')
            ->with('success', 'تم إنشاء حسابك بنجاح. مرحباً بك في منصة آمر تم!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('business')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('amrtm.login');
    }

    private function safeRedirect(?string $url): ?string
    {
        if (!is_string($url) || $url === '') {
            return null;
        }

        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }

        $parsed = parse_url($url);
        if (is_array($parsed) && isset($parsed['host']) && strtolower((string) $parsed['host']) === strtolower((string) request()->getHost())) {
            return $url;
        }

        return null;
    }
}

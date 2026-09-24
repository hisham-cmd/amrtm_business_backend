<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Models\Business\Office;
use App\Models\Business\OfficeUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class OfficeAuthController extends Controller
{
    public function showLogin()
    {
        return redirect(route('amrtm.login') . '?type=office');
    }
public function login(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ], [
        'email.required'    => 'البريد الإلكتروني مطلوب',
        'email.email'       => 'البريد الإلكتروني غير صحيح',
        'password.required' => 'كلمة المرور مطلوبة',
    ]);

    $credentials = $request->only('email', 'password');

    if (!Auth::guard('office')->attempt(
        $credentials,
        $request->boolean('remember')
    )) {
        return back()
            ->withErrors([
                'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'
            ])
            ->withInput();
    }

    $user = Auth::guard('office')->user();

    /*
    |--------------------------------------------------------------------------
    | التحقق من حالة الحساب والمكتب
    |--------------------------------------------------------------------------
    */

    if (!$user->is_active || !$user->office || !$user->office->is_active) {

        Auth::guard('office')->logout();

        return back()
            ->withErrors([
                'email' => 'حسابك موقوف. تواصل مع الإدارة.'
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
    | لذلك لا حاجة لصفحة استكمال البيانات /office/complete.
    */

    return redirect()
        ->route('amrtm.dashboard.hub');
}


public function showRegister()
{
    return redirect()
        ->route('amrtm.provider.account.create', ['type' => 'office']);
}


public function register(Request $request)
{
    $request->validate([
        'office_name_ar' => 'required|string|max:255',

        'office_name_en' => 'required|string|max:255',

        'type' => [
            'required',
            'in:law,services,customs,accounting,engineering,freelance'
        ],

        'phone' => 'required|string|max:20',

        'email' => [
            'required',
            'email',
            'unique:business.bs_office_users,email',
            'unique:business.bs_offices,email',
        ],

        'password' => 'required|string|min:8|confirmed',

        'name' => 'required|string|max:255',

        'city' => 'nullable|string|max:100',

        'cr_number' => 'nullable|string|max:50',

    ], [

        'office_name_ar.required' =>
            'اسم المكتب بالعربية مطلوب',

        'office_name_en.required' =>
            'اسم المكتب بالإنجليزية مطلوب',

        'type.required' =>
            'نوع المكتب مطلوب',

        'type.in' =>
            'نوع المكتب غير صحيح',

        'phone.required' =>
            'رقم الجوال مطلوب',

        'email.required' =>
            'البريد الإلكتروني مطلوب',

        'email.email' =>
            'البريد الإلكتروني غير صحيح',

        'email.unique' =>
            'هذا البريد الإلكتروني مسجل مسبقاً',

        'password.required' =>
            'كلمة المرور مطلوبة',

        'password.min' =>
            'كلمة المرور يجب أن تكون 8 أحرف على الأقل',

        'password.confirmed' =>
            'كلمتا المرور غير متطابقتين',

        'name.required' =>
            'اسم المسؤول مطلوب',
    ]);


    /*
    |--------------------------------------------------------------------------
    | إنشاء المكتب
    |--------------------------------------------------------------------------
    */

    $office = Office::create([

        'type' => $request->type,

        'name_ar' => $request->office_name_ar,

        'name_en' => $request->office_name_en,

        'phone' => $request->phone,

        'email' => $request->email,

        'city' => $request->city,

        'cr_number' => $request->cr_number,

        // الحساب مسموح له بالدخول
        'is_active' => true,

        // لكن المكتب لم يتم اعتماده بعد
        'is_verified' => false,
    ]);


    /*
    |--------------------------------------------------------------------------
    | إنشاء مستخدم المكتب
    |--------------------------------------------------------------------------
    */

    $user = OfficeUser::create([

        'office_id' => $office->id,

        'name' => $request->name,

        'email' => $request->email,

        'password' => Hash::make($request->password),

        'role' => 'owner',

        'is_active' => true,
    ]);


    /*
    |--------------------------------------------------------------------------
    | تسجيل الدخول مباشرة
    |--------------------------------------------------------------------------
    */

    Auth::guard('office')->login($user);

    $request->session()->regenerate();


    /*
    |--------------------------------------------------------------------------
    | المكتب جديد → التوجه إلى لوحة التحكم مباشرة
    |--------------------------------------------------------------------------
    | بيانات المكتب تُعبأ كاملة عند إنشاء الحساب عبر /provider-account/create،
    | لذلك لا حاجة لصفحة /office/complete.
    */

    return redirect()
        ->route('amrtm.dashboard.hub');
}

    public function logout(Request $request)
    {
        Auth::guard('office')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('amrtm.login') . '?type=office');
    }
}
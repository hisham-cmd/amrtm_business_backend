<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompleteOfficeProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        /*
        |--------------------------------------------------------------------------
        | التسجيل المؤقت
        |--------------------------------------------------------------------------
        */

        if ($request->session()->has('office_register')) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | مكتب موجود ومسجل دخول
        |--------------------------------------------------------------------------
        */

        if (auth()->guard('office')->check()) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | لا يوجد تسجيل
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('amrtm.office.login')
            ->withErrors([
                'email' => 'يجب تسجيل الدخول أو إنشاء حساب مكتب أولاً.'
            ]);
    }
}
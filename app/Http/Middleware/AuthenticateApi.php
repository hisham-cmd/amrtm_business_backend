<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateApi extends Authenticate
{
    /**
     * مسارات الـ API لا تعيد توجيه إلى صفحات تسجيل الدخول أبداً،
     * بل تُطلق AuthenticationException ليلتقطها معالج الاستثناءات
     * ويعيد 401 JSON بصيغة مغلفة. (React-ready)
     */
    protected function redirectTo(Request $request): ?string
    {
        return null;
    }

    /**
     * تحديد المستخدم الحالي:
     * 1) توكن Sanctum (Bearer) → مصادقة الواجهة المنفصلة (سيرفر مختلف).
     * 2) الجلسة → الواجهة الحالية (Blade) داخل نفس الأصل.
     */
    protected function authenticate($request, array $guards): void
    {
        // 1) محاولة المصادقة عبر التوكن أولاً (sanctum)
        if ($request->bearerToken()) {
            foreach ($guards as $guard) {
                if ($guard === 'business' || $guard === 'office') {
                    $tokenGuard = $guard . '_token';
                    if (Auth::guard($tokenGuard)->check()) {
                        Auth::shouldUse($tokenGuard);
                        return;
                    }
                }
            }
        }

        // 2) ثم الجلسة العادية
        parent::authenticate($request, $guards);
    }
}
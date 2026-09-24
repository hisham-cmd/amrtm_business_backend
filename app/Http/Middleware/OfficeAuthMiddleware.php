<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OfficeAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1) مصادقة توكنية (Sanctum) — الواجهة المنفصلة على سيرفر مختلف
        if ($request->bearerToken() && Auth::guard('office_token')->check()) {
            Auth::shouldUse('office_token');
            $user = Auth::guard('office_token')->user();
            // عيّن المستخدم على الحارس الأصلي لتعمل أكواد auth('office')->user()
            Auth::guard('office')->setUser($user);

            if (!$user->is_active || !$user->office || !$user->office->is_active) {
                return $this->unauthorized($request, 'حسابك موقوف. تواصل مع الإدارة.');
            }

            return $next($request);
        }

        // 2) جلسة تقليدية
        if (!Auth::guard('office')->check()) {
            if ($this->isApiRequest($request)) {
                return $this->unauthorized($request);
            }
            return redirect()->route('amrtm.office.login')
                ->with('error', 'يجب تسجيل الدخول أولاً');
        }

        $user = Auth::guard('office')->user();
        if (!$user->is_active || !$user->office->is_active) {
            Auth::guard('office')->logout();
            if ($this->isApiRequest($request)) {
                return $this->unauthorized($request, 'حسابك موقوف. تواصل مع الإدارة.');
            }
            return redirect()->route('amrtm.office.login')
                ->with('error', 'حسابك موقوف. تواصل مع الإدارة.');
        }

        return $next($request);
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    private function unauthorized(Request $request, string $message = 'غير مصرح'): Response
    {
        return response()->json([
            'isSuccess'  => false,
            'value'      => null,
            'error'      => ['message' => $message, 'code' => 'UNAUTHENTICATED'],
            'statusCode' => 401,
        ], 401);
    }
}
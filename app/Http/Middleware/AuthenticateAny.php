<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAny
{
    /**
     * يسمح بدخول مستخدمي Guard الـ business أو office.
     * يُستخدم للوحة التحكم الموحّدة التي يصلها العميل والمادرة ومنشآت المكاتب.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('business')->check() && ! Auth::guard('office')->check()) {
            if ($request->expectsJson() || $request->is('api/*') || $request->is('*/api/*')) {
                return response()->json([
                    'isSuccess' => false,
                    'value' => null,
                    'error' => ['message' => 'غير مصادق عليه.', 'code' => 'UNAUTHENTICATED'],
                    'statusCode' => 401,
                ], 401);
            }

            return redirect()->route('amrtm.login')
                ->with('error', 'يجب تسجيل الدخول أولاً');
        }

        return $next($request);
    }
}

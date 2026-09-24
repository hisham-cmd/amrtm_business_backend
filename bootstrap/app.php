<?php

use App\Http\Middleware\AuditAdminActions;
use App\Http\Middleware\AuthenticateAny;
use App\Http\Middleware\AuthenticateApi;
use App\Http\Middleware\BusinessGuestMiddleware;
use App\Http\Middleware\BusinessRoleMiddleware;
use App\Http\Middleware\CompleteOfficeProfile;
use App\Http\Middleware\OfficeAuthMiddleware;
use App\Http\Middleware\OfficeGuestMiddleware;
use App\Http\Middleware\PreventAdminFromUserActions;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo(fn () => route('amrtm.login'));
        $middleware->alias([
            'business-role' => BusinessRoleMiddleware::class,
            'no-admin' => PreventAdminFromUserActions::class,
            'audit-admin' => AuditAdminActions::class,
            'guest.business' => BusinessGuestMiddleware::class,
            'auth.office' => OfficeAuthMiddleware::class,
            'auth.api' => AuthenticateApi::class,
            'guest.office' => OfficeGuestMiddleware::class,
            'complete.office.profile' => CompleteOfficeProfile::class,
            'auth.any' => AuthenticateAny::class,
        ]);
        $middleware->web(append: [
            SetLocale::class,
            SecurityHeaders::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'contract-view/*',
            'payment/callback',
            'payment/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // مسارات الـ API غير المصادَق عليها → 401 JSON بدل redirect (React-ready)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'isSuccess' => false,
                    'value' => null,
                    'error' => ['message' => 'غير مصادق عليه. يرجى تسجيل الدخول.', 'code' => 'UNAUTHENTICATED'],
                    'statusCode' => 401,
                ], 401);
            }

            $guard = $e->guards()[0] ?? 'web';
            if ($guard === 'office') {
                return redirect()->guest(route('amrtm.office.login'));
            }
            if ($guard === 'business') {
                return redirect()->guest(route('amrtm.login'));
            }

            return redirect()->guest(route('login'));
        });

        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'انتهت صلاحية الجلسة. يرجى تسجيل الدخول مرة أخرى.',
                ], 419);
            }

            // Route to the business login page
            return redirect()
                ->route('amrtm.login')
                ->with('error', 'انتهت صلاحية الجلسة بسبب عدم النشاط. سجل الدخول مرة أخرى ثم أعد المحاولة.');
        });
    })->create();

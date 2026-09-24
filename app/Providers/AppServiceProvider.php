<?php

namespace App\Providers;

use App\Models\Business\BusinessUser;
use App\Models\ServiceRequest;
use App\Policies\ServiceRequestPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Force HTTPS in production or when running behind an SSL-terminating reverse proxy (Render, Cloudflare, etc.)
        if (
            $this->app->environment('production') ||
            request()->header('X-Forwarded-Proto') === 'https' ||
            request()->server('HTTP_X_FORWARDED_PROTO') === 'https'
        ) {
            URL::forceScheme('https');
        }

        // Register policy for service requests
        Gate::policy(ServiceRequest::class, ServiceRequestPolicy::class);

        // Use business guard for policy checks in business platform
        Gate::before(function (BusinessUser $user, string $ability) {
            // admin can do anything except user-only actions (handled per policy)
        });

        // Rate limiters
        RateLimiter::for('business-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())
                ->response(function () use ($request) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'محاولات كثيرة جداً. يرجى الانتظار دقيقة ثم المحاولة مجدداً.'], 429);
                    }
                    return back()
                        ->withErrors(['email' => 'محاولات كثيرة جداً. يرجى الانتظار دقيقة ثم المحاولة مجدداً.'])
                        ->withInput($request->only('email'));
                });
        });

        RateLimiter::for('business-register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip())
                ->response(function () use ($request) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'محاولات تسجيل كثيرة جداً. يرجى الانتظار ثم المحاولة مجدداً.'], 429);
                    }
                    return back()
                        ->withErrors(['email' => 'محاولات تسجيل كثيرة جداً. يرجى الانتظار ثم المحاولة مجدداً.'])
                        ->withInput($request->only('name', 'email', 'phone'));
                });
        });

        RateLimiter::for('business-api', function (Request $request) {
            return Limit::perMinute(60)->by(
                auth('business')->id() ?? $request->ip()
            );
        });

        RateLimiter::for('nafath-verify', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())
                ->response(function () use ($request) {
                    return response()->json([
                        'message' => 'محاولات بدء تحقق كثيرة جداً. يرجى الانتظار دقيقة ثم المحاولة مجدداً.',
                    ], 429);
                });
        });
    }
}
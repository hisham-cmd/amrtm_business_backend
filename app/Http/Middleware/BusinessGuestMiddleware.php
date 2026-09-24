<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BusinessGuestMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('business')->check()) {
            return redirect()->route('amrtm.dashboard.hub');
        }

        return $next($request);
    }
}

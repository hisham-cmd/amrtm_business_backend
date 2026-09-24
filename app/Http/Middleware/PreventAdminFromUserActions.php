<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventAdminFromUserActions
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('business')->user();

        if ($user && $user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'المدير لا يمكنه القيام بهذا الإجراء.'], 403);
            }
            return redirect()->route('amrtm.admin.dashboard')
                ->with('error', 'هذا الإجراء مخصص للمستخدمين فقط.');
        }

        return $next($request);
    }
}
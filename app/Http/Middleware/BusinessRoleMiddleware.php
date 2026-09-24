<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BusinessRoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth('business')->user();

        if (! $user) {
            if ($request->expectsJson() || $request->is('*/api/*')) {
                return response()->json(['message' => 'غير مصرح.'], 401);
            }
            return redirect()->route('amrtm.login');
        }

        $role = $user->role;

        // admin passes supervisor checks too
        $effective = [$role];
        if ($role === 'admin') {
            $effective[] = 'supervisor';
        }
        if (in_array($role, ['admin', 'supervisor'])) {
            $effective[] = 'manager';
        }

        foreach ($roles as $r) {
            if (in_array($r, $effective)) {
                return $next($request);
            }
        }

        if ($request->expectsJson() || $request->is('*/api/*')) {
            return response()->json(['message' => 'غير مصرح لك بالوصول.'], 403);
        }
        abort(403, 'غير مصرح لك بالوصول.');
    }
}

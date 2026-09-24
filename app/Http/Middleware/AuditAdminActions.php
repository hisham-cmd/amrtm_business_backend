<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditAdminActions
{
    private const AUDITED_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (in_array($request->method(), self::AUDITED_METHODS)) {
            $user = auth('business')->user();
            if ($user && $user->isAdmin()) {
                Log::channel('stack')->info('Admin action', [
                    'admin_id'   => $user->id,
                    'admin_name' => $user->name,
                    'method'     => $request->method(),
                    'url'        => $request->fullUrl(),
                    'ip'         => $request->ip(),
                    'status'     => $response->getStatusCode(),
                ]);
            }
        }

        return $response;
    }
}
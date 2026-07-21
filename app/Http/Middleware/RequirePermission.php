<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $payload = $request->attributes->get('bearer_payload', []);
        $permissions = $payload['Permissions'] ?? [];

        if (! in_array($permission, $permissions, true)) {
            return response()->json([
                'error' => 'Missing required permission.',
                'required_permission' => $permission,
            ], 403);
        }

        return $next($request);
    }
}

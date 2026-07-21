<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $payload = $request->attributes->get('bearer_payload', []);
        $roles = $payload['Roles'] ?? [];

        if (! in_array($role, $roles, true)) {
            return response()->json([
                'error' => 'Missing required role.',
                'required_role' => $role,
            ], 403);
        }

        return $next($request);
    }
}

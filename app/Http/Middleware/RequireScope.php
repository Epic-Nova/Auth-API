<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireScope
{
    public function handle(Request $request, Closure $next, string $scope): Response
    {
        $payload = $request->attributes->get('bearer_payload', []);
        $scopes = $payload['Scopes'] ?? [];
        if ($scopes === [] && is_string($payload['scope'] ?? null)) {
            $scopes = preg_split('/\s+/', trim($payload['scope'])) ?: [];
        }

        if (! in_array($scope, $scopes, true)) {
            return response()->json([
                'error' => 'insufficient_scope',
                'required_scope' => $scope,
            ], 403);
        }

        return $next($request);
    }
}

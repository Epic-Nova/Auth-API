<?php

namespace App\Http\Middleware;

use App\Services\BearerTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use RuntimeException;

class AuthenticateBearerToken
{
    public function __construct(private BearerTokenService $bearerTokenService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $payload = $this->bearerTokenService->resolvePayloadFromBearer($request->bearerToken());
        } catch (RuntimeException $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], 401);
        }

        $request->attributes->set('bearer_payload', $payload);

        return $next($request);
    }
}

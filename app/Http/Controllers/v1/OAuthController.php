<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\CapabilityDiscoveryController;
use App\Http\Controllers\Controller;
use App\Models\Authentication\IssuedToken;
use App\Models\Authentication\OAuthClient;
use App\Traits\IssuedTokenTrait;
use App\Traits\LoginTokenTrait;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Throwable;

class OAuthController extends Controller
{
    use IssuedTokenTrait;
    use LoginTokenTrait;

    public function index(): JsonResponse
    {
        return response()->json([
            'feature' => 'oauth',
            'version' => 'v1',
            'capabilities' => CapabilityDiscoveryController::getCapabilitiesForFeature('v1', 'oauth'),
        ]);
    }

    public function token(Request $request): JsonResponse
    {
        $this->mergeHttpBasicCredentials($request);
        $validated = $request->validate([
            'grant_type' => ['required', 'in:client_credentials'],
            'client_id' => ['required_without:client_secret', 'uuid'],
            'client_secret' => ['required_without:client_id', 'string', 'max:1024'],
            'scope' => ['nullable', 'string', 'max:2048'],
            'audience' => ['nullable', 'string', 'max:2048'],
        ]);

        $clientId = $this->resolveClientId($request, $validated);
        $rateLimitKey = 'oauth-token:'.$clientId.':'.$request->ip();
        $rateLimiter = new RateLimiter(Cache::store('redis'));
        if ($rateLimiter->tooManyAttempts($rateLimitKey, 10)) {
            return response()->json([
                'error' => 'slow_down',
                'error_description' => 'Too many token requests.',
            ], 429);
        }

        $rateLimiter->hit($rateLimitKey, 60);

        $client = $this->authenticateClient($request, $validated);
        if (! $client) {
            return $this->invalidClientResponse();
        }

        $requestedScopes = $this->splitSpaceDelimited($validated['scope'] ?? '');
        $requestedAudiences = $this->splitSpaceDelimited($validated['audience'] ?? '');
        if (! $this->isSubset($requestedScopes, $client->AllowedScopes ?? []) ||
            ! $this->isSubset($requestedAudiences, $client->AllowedAudiences ?? [])) {
            return response()->json([
                'error' => 'invalid_scope',
                'error_description' => 'Requested scope or audience is not allowed for this client.',
            ], 400);
        }

        $scopes = $requestedScopes === [] ? ($client->AllowedScopes ?? []) : $requestedScopes;
        $audiences = $requestedAudiences === [] ? ($client->AllowedAudiences ?? []) : $requestedAudiences;
        $ttl = min((int) $client->AccessTokenTtlSeconds, (int) config('auth.jwt.access_ttl', 3600));

        $tokens = $this->generateTokenPair(
            $audiences,
            $scopes,
            [],
            [],
            ['client_id:'.$client->OAuthClientIdentifier],
            $ttl,
            0,
            null,
            $client->OAuthClientIdentifier,
            'client_credentials',
        );

        return response()->json([
            'access_token' => $tokens['access_token'],
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'scope' => implode(' ', $scopes),
        ]);
    }

    public function introspect(Request $request): JsonResponse
    {
        $this->mergeHttpBasicCredentials($request);
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:16384'],
            'client_id' => ['required_without:client_secret', 'uuid'],
            'client_secret' => ['required_without:client_id', 'string', 'max:1024'],
        ]);

        if (! $this->authenticateClient($request, $validated)) {
            return $this->invalidClientResponse();
        }

        try {
            $payload = $this->verifyAndDecodeToken($validated['token']);
            $issuedToken = IssuedToken::query()->find($payload['jti'] ?? null);
            $active = $issuedToken && $issuedToken->TokenState === 'Active' && ! $issuedToken->TokenExpiry->isPast();

            if (! $active) {
                return response()->json(['active' => false]);
            }

            return response()->json([
                'active' => true,
                'client_id' => $payload['client_id'] ?? null,
                'scope' => $payload['scope'] ?? implode(' ', $payload['Scopes'] ?? []),
                'token_type' => 'Bearer',
                'exp' => $payload['exp'] ?? null,
                'iat' => $payload['iat'] ?? null,
                'sub' => $payload['sub'] ?? [],
                'aud' => $payload['aud'] ?? [],
            ]);
        } catch (Throwable) {
            return response()->json(['active' => false]);
        }
    }

    private function authenticateClient(Request $request, array $validated): ?OAuthClient
    {
        $clientId = $this->resolveClientId($request, $validated);
        $clientSecret = $this->resolveClientSecret($request, $validated);
        if ($clientId === '' || $clientSecret === '') {
            return null;
        }

        $client = OAuthClient::query()->find($clientId);
        if (! $client || ! $client->IsActive || ! Hash::check($clientSecret, $client->ClientSecretHash)) {
            return null;
        }

        return $client;
    }

    private function resolveClientId(Request $request, array $validated): string
    {
        return (string) ($request->getUser() ?: ($validated['client_id'] ?? ''));
    }

    private function resolveClientSecret(Request $request, array $validated): string
    {
        return (string) ($request->getPassword() ?: ($validated['client_secret'] ?? ''));
    }

    private function invalidClientResponse(): JsonResponse
    {
        return response()->json([
            'error' => 'invalid_client',
            'error_description' => 'Client authentication failed.',
        ], 401, ['WWW-Authenticate' => 'Basic realm="oauth"']);
    }

    private function mergeHttpBasicCredentials(Request $request): void
    {
        if (! $request->filled('client_id') && is_string($request->getUser()) && $request->getUser() !== '') {
            $request->merge(['client_id' => $request->getUser()]);
        }

        if (! $request->filled('client_secret') && is_string($request->getPassword()) && $request->getPassword() !== '') {
            $request->merge(['client_secret' => $request->getPassword()]);
        }
    }

    /** @return array<int, string> */
    private function splitSpaceDelimited(string $value): array
    {
        return array_values(array_unique(array_filter(preg_split('/\s+/', trim($value)) ?: [])));
    }

    /** @param array<int, string> $requested @param array<int, string> $allowed */
    private function isSubset(array $requested, array $allowed): bool
    {
        return array_diff($requested, $allowed) === [];
    }
}

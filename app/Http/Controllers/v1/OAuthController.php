<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\CapabilityDiscoveryController;
use App\Http\Controllers\Controller;
use App\Models\Authentication\Authenticatable;
use App\Models\Authentication\IssuedToken;
use App\Models\Authentication\OAuthClient;
use App\Models\Authentication\OAuthDeviceAuthorization;
use App\Services\BearerTokenService;
use App\Traits\IssuedTokenTrait;
use App\Traits\LoginTokenTrait;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

    /**
     * Create exactly one confidential OAuth client for a stable service identity.
     *
     * The bootstrap key is a KeyForge-to-Auth-API trust channel. It is never
     * persisted, returned, or written to application logs.
     */
    public function provisionApplication(Request $request): JsonResponse
    {
        if (! $this->hasValidKeyForgeBootstrapKey($request)) {
            return response()->json(['error' => 'forbidden'], 403);
        }

        $maximumTtl = (int) config('keyforge.oauth_client_maximum_ttl', 3600);
        $validated = $request->validate([
            'application_identity' => ['required', 'string', 'max:128', 'regex:/^[a-z][a-z0-9._:-]{2,127}$/'],
            'display_name' => ['nullable', 'string', 'max:128'],
            'scopes' => ['required', 'array', 'min:1', 'max:32'],
            'scopes.*' => ['string', 'max:128', 'regex:/^[a-z][a-z0-9._:-]{1,127}$/'],
            'audiences' => ['required', 'array', 'min:1', 'max:16'],
            'audiences.*' => ['string', 'max:255', 'regex:/^[A-Za-z0-9][A-Za-z0-9._:-]{1,254}$/'],
            'access_token_ttl_seconds' => ['nullable', 'integer', 'min:60', 'max:'.$maximumTtl],
        ]);

        $identity = $validated['application_identity'];
        $scopes = array_values(array_unique($validated['scopes']));
        $audiences = array_values(array_unique($validated['audiences']));
        $ttl = $validated['access_token_ttl_seconds'] ?? min(600, $maximumTtl);
        $clientName = (string) config('keyforge.oauth_client_name_prefix', 'keyforge:').$identity;

        $result = DB::connection('AuthConnection')->transaction(function () use ($identity, $scopes, $audiences, $ttl, $clientName): array {
            $existingClient = OAuthClient::query()
                ->where('ApplicationIdentity', $identity)
                ->lockForUpdate()
                ->first();

            if ($existingClient) {
                if (! $existingClient->IsActive ||
                    $existingClient->AllowedScopes !== $scopes ||
                    $existingClient->AllowedAudiences !== $audiences ||
                    (int) $existingClient->AccessTokenTtlSeconds !== $ttl) {
                    return ['status' => 'conflict'];
                }

                return [
                    'status' => 'existing',
                    'client_id' => $existingClient->OAuthClientIdentifier,
                ];
            }

            if (OAuthClient::query()->where('ClientName', $clientName)->lockForUpdate()->exists()) {
                return ['status' => 'conflict'];
            }

            $clientId = (string) Str::uuid();
            $clientSecret = Str::password(48, symbols: true);

            Authenticatable::query()->create([
                'AuthenticatableIdentifier' => $clientId,
                'AuthenticatableType' => 'Application',
            ]);

            OAuthClient::query()->create([
                'OAuthClientIdentifier' => $clientId,
                'ApplicationIdentity' => $identity,
                'ClientName' => $clientName,
                'ClientSecretHash' => Hash::make($clientSecret),
                'AllowedScopes' => $scopes,
                'AllowedAudiences' => $audiences,
                'AccessTokenTtlSeconds' => $ttl,
                'IsActive' => true,
            ]);

            return [
                'status' => 'created',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ];
        });

        if ($result['status'] === 'conflict') {
            return response()->json([
                'error' => 'application_configuration_conflict',
                'error_description' => 'The application identity already exists with a different immutable OAuth configuration.',
            ], 409);
        }

        if ($result['status'] === 'existing') {
            return response()->json([
                'application_identity' => $identity,
                'client_id' => $result['client_id'],
                'created' => false,
            ]);
        }

        return response()->json([
            'application_identity' => $identity,
            'client_id' => $result['client_id'],
            'client_secret' => $result['client_secret'],
            'created' => true,
        ], 201);
    }

    public function deviceAuthorize(Request $request): JsonResponse
    {
        $this->mergeHttpBasicCredentials($request);
        $validated = $request->validate([
            'client_id' => ['required_without:client_secret', 'uuid'],
            'client_secret' => ['required_without:client_id', 'string', 'max:1024'],
            'scope' => ['nullable', 'string', 'max:2048'],
            'audience' => ['nullable', 'string', 'max:2048'],
        ]);

        $client = $this->authenticateClient($request, $validated);
        if (! $client) {
            return $this->invalidClientResponse();
        }

        $scopes = $this->splitSpaceDelimited($validated['scope'] ?? '');
        $audiences = $this->splitSpaceDelimited($validated['audience'] ?? '');
        if (! $this->isSubset($scopes, $client->AllowedScopes ?? []) || ! $this->isSubset($audiences, $client->AllowedAudiences ?? [])) {
            return response()->json(['error' => 'invalid_scope'], 400);
        }

        $scopes = $scopes === [] ? ($client->AllowedScopes ?? []) : $scopes;
        $audiences = $audiences === [] ? ($client->AllowedAudiences ?? []) : $audiences;

        $deviceCode = Str::random(96);
        $userCode = Str::upper(Str::random(4).'-'.Str::random(4));
        $expiresAt = now()->addMinutes(10);

        OAuthDeviceAuthorization::query()->create([
            'OAuthDeviceAuthorizationIdentifier' => (string) Str::uuid(),
            'OAuthClientIdentifier' => $client->OAuthClientIdentifier,
            'DeviceCodeHash' => hash('sha256', $deviceCode),
            'UserCodeHash' => hash('sha256', $userCode),
            'RequestedScopes' => $scopes,
            'RequestedAudiences' => $audiences,
            'ExpiresAt' => $expiresAt,
        ]);

        $verificationUri = rtrim((string) config('app.url'), '/').'/api/v1/oauth/device-approve';

        return response()->json([
            'device_code' => $deviceCode,
            'user_code' => $userCode,
            'verification_uri' => $verificationUri,
            'verification_uri_complete' => $verificationUri.'?user_code='.urlencode($userCode),
            'expires_in' => now()->diffInSeconds($expiresAt),
            'interval' => 3,
        ]);
    }

    public function deviceApprove(Request $request, BearerTokenService $bearerTokenService): JsonResponse
    {
        if ($request->isMethod('GET')) {
            return response()->json([
                'message' => 'Authenticate with Nova ID, then POST the user_code to this endpoint using your Bearer token.',
                'user_code' => $request->query('user_code'),
                'approval_method' => 'POST',
            ]);
        }

        $validated = $request->validate([
            'user_code' => ['required', 'string', 'max:32'],
        ]);

        try {
            $userPayload = $bearerTokenService->resolvePayloadFromBearer($request->bearerToken());
        } catch (Throwable) {
            return response()->json(['error' => 'invalid_token'], 401);
        }

        $authorization = OAuthDeviceAuthorization::query()
            ->where('UserCodeHash', hash('sha256', Str::upper($validated['user_code'])))
            ->first();

        if (! $authorization || $authorization->AuthorizationState !== 'Pending') {
            return response()->json(['error' => 'invalid_user_code'], 400);
        }

        if ($authorization->ExpiresAt->isPast()) {
            $authorization->update(['AuthorizationState' => 'Expired']);

            return response()->json(['error' => 'expired_token'], 400);
        }

        $authenticatableIdentifier = $this->subjectValue($userPayload['sub'] ?? [], 'authenticatable_id:');
        if ($authenticatableIdentifier === null) {
            return response()->json(['error' => 'invalid_token'], 401);
        }

        $client = OAuthClient::query()->find($authorization->OAuthClientIdentifier);
        if (! $client || ! $client->IsActive) {
            return response()->json(['error' => 'invalid_client'], 401);
        }

        if (! $this->isSubset($authorization->RequestedScopes ?? [], $userPayload['Scopes'] ?? []) ||
            ! $this->isSubset($authorization->RequestedAudiences ?? [], $userPayload['aud'] ?? [])) {
            return response()->json(['error' => 'invalid_scope'], 403);
        }

        $authorization->update([
            'AuthorizationState' => 'Approved',
            'ApprovedByAuthenticatable' => $authenticatableIdentifier,
            'ApprovedAt' => now(),
        ]);

        return response()->json(['status' => 'approved']);
    }

    public function deviceToken(Request $request): JsonResponse
    {
        $this->mergeHttpBasicCredentials($request);
        $validated = $request->validate([
            'device_code' => ['required', 'string', 'max:256'],
            'client_id' => ['required_without:client_secret', 'uuid'],
            'client_secret' => ['required_without:client_id', 'string', 'max:1024'],
        ]);

        $client = $this->authenticateClient($request, $validated);
        if (! $client) {
            return $this->invalidClientResponse();
        }

        $authorization = OAuthDeviceAuthorization::query()
            ->where('DeviceCodeHash', hash('sha256', $validated['device_code']))
            ->first();

        if (! $authorization) {
            return response()->json(['error' => 'invalid_grant'], 400);
        }

        if ($authorization->OAuthClientIdentifier !== $client->OAuthClientIdentifier) {
            return response()->json(['error' => 'invalid_grant'], 400);
        }

        if ($authorization->ExpiresAt->isPast() && $authorization->AuthorizationState === 'Pending') {
            $authorization->update(['AuthorizationState' => 'Expired']);
        }

        if ($authorization->AuthorizationState === 'Pending') {
            return response()->json(['error' => 'authorization_pending'], 428);
        }

        if ($authorization->AuthorizationState === 'Expired') {
            return response()->json(['error' => 'expired_token'], 400);
        }

        if ($authorization->AuthorizationState !== 'Approved' || ! $authorization->ApprovedByAuthenticatable) {
            return response()->json(['error' => 'invalid_grant'], 400);
        }

        $ttl = min((int) $client->AccessTokenTtlSeconds, (int) config('auth.jwt.access_ttl', 3600));
        $tokens = $this->generateTokenPair(
            $authorization->RequestedAudiences ?? [],
            $authorization->RequestedScopes ?? [],
            [],
            [],
            [
                'authenticatable_id:'.$authorization->ApprovedByAuthenticatable,
                'client_id:'.$client->OAuthClientIdentifier,
            ],
            $ttl,
            0,
            null,
            $client->OAuthClientIdentifier,
            'urn:ietf:params:oauth:grant-type:device_code',
        );

        $authorization->update([
            'AuthorizationState' => 'Consumed',
            'ConsumedAt' => now(),
        ]);

        return response()->json([
            'access_token' => $tokens['access_token'],
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'scope' => implode(' ', $authorization->RequestedScopes ?? []),
        ]);
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

    private function subjectValue(mixed $subjects, string $prefix): ?string
    {
        foreach (is_array($subjects) ? $subjects : [$subjects] as $subject) {
            if (is_string($subject) && str_starts_with($subject, $prefix)) {
                return substr($subject, strlen($prefix));
            }
        }

        return null;
    }

    private function hasValidKeyForgeBootstrapKey(Request $request): bool
    {
        $configuredKey = (string) config('keyforge.oauth_provisioning_key', '');
        $providedKey = (string) $request->header('X-KeyForge-Bootstrap', '');

        if (strlen($configuredKey) < (int) config('keyforge.oauth_provisioning_key_minimum_length', 32) ||
            $providedKey === '') {
            return false;
        }

        return hash_equals($configuredKey, $providedKey);
    }
}

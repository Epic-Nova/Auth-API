<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\CapabilityDiscoveryController;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\AuthenticationLoginRequest;
use App\Models\AuthenticatableUpdateLog;
use App\Models\Authentication\Authenticatable;
use App\Models\Authentication\AuthenticationProvider;
use App\Models\Authentication\IssuedToken;
use App\Traits\IssuedTokenTrait;
use App\Traits\LoginTokenTrait;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthenticationController extends Controller
{
    use IssuedTokenTrait;
    use LoginTokenTrait;

    /**
     * Matches: GET /api/v1/account
     * Returns its own self-describing capabilities registry matrix dynamically.
     */
    public function index()
    {
        // Query the master registry specifically for v1 -> auth
        $myCapabilities = CapabilityDiscoveryController::getCapabilitiesForFeature('v1', 'authentication');

        return response()->json([
            'feature' => 'auth',
            'version' => 'v1',
            'capabilities' => $myCapabilities,
        ]);
    }

    /**
     * @param  AuthLoginRequest  $request
     *
     * @throws ConnectionException
     *
     * @todo: Draw from AuthenticationProvidersParameters the parameters required, if this is a latent provider we need to flag it in the db as for mfa the user needs his request saved
     * (Multi Request Providers that require more that one request. They return a state id to reidentify within the process)
     */
    public function login(AuthenticationLoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $authenticatable = Authenticatable::query()
            ->with([
                'account.profile',
                'roles.permissions',
                'roles.scopes',
                'permissions',
                'permissionCollections',
                'roles.permissionCollections',
            ])
            ->where('AuthenticatableIdentifier', $data['authenticatable_identifier'])
            ->first();

        if (! $authenticatable) {
            return response()->json([
                'error' => 'Authenticatable not found.',
            ], 404);
        }

        $provider = AuthenticationProvider::query()
            ->where('AuthenticationProviderIdentifier', $data['provider_identifier'])
            ->orWhere('ProviderFriendlyIdentifier', $data['provider_identifier'])
            ->first();

        if (! $provider) {
            return response()->json([
                'error' => 'Authentication provider not found.',
            ], 404);
        }

        if (! $provider->bCanUserLoginWithProvider) {
            return response()->json([
                'error' => 'Authentication provider does not support user login.',
            ], 403);
        }

        $providerParameters = $provider->parameters()->get();
        $providedParameters = collect($data['parameters'] ?? [])->keyBy('name');

        foreach ($providerParameters->where('bIsParameterRequired', true) as $providerParameter) {
            if (! $providedParameters->has($providerParameter->ParameterName)) {
                return response()->json([
                    'error' => 'Missing required authentication provider parameter.',
                    'required_parameter' => $providerParameter->ParameterName,
                ], 422);
            }
        }

        $attachedProvider = $authenticatable->attachedAuthenticationProviders()
            ->where('AuthenticationProviderIdentifier', $provider->AuthenticationProviderIdentifier)
            ->first();

        if (! $attachedProvider) {
            return response()->json([
                'error' => 'Authentication provider not attached to authenticatable.',
            ], 403);
        }

        if ($attachedProvider->ProviderState === 'Disabled') {
            return response()->json([
                'error' => 'Authentication provider is disabled for this authenticatable.',
            ], 403);
        }

        $useLocalBypass = (bool) ($data['login_bypass'] ?? false);
        if ($useLocalBypass) {
            if (! config('keyforge.local_login_bypass') ||
                $provider->ProviderFriendlyIdentifier !== 'com.epicnova.authentication_provider.local-bypass') {
                return response()->json([
                    'error' => 'Local login bypass is not enabled for this provider.',
                ], 403);
            }
        } else {
            $providerBaseUrl = rtrim((string) config('services.authentication_providers.base_url'), '/');
            if ($providerBaseUrl === '') {
                return response()->json([
                    'error' => 'Authentication provider service is not configured.',
                ], 500);
            }

            $providerResponse = Http::timeout((int) config('services.authentication_providers.timeout', 5))
                ->acceptJson()
                ->post($providerBaseUrl.'/api/auth/login', [
                    'authenticatable_identifier' => $authenticatable->AuthenticatableIdentifier,
                    'provider_identifier' => $provider->ProviderFriendlyIdentifier,
                    'provider_data' => $attachedProvider->ProviderData,
                    'input' => $data['input'],
                    'login_bypass' => false,
                    'parameters' => $data['parameters'] ?? [],
                ]);

            if (! $providerResponse->ok()) {
                Log::warning('Auth provider request failed.', [
                    'status' => $providerResponse->status(),
                    'provider' => $provider->ProviderFriendlyIdentifier,
                ]);

                return response()->json([
                    'error' => 'Authentication provider unavailable.',
                    'status' => $providerResponse->status(),
                    'provider' => $provider->ProviderFriendlyIdentifier,
                ], 502);
            }

            if (! $providerResponse->json('ok', false)) {
                return response()->json([
                    'error' => 'Invalid credentials.',
                ], 401);
            }
        }

        $roles = $authenticatable->roles;
        $roleIdentifiers = $roles->pluck('RoleIdentifier')->unique()->values()->all();
        $rolePermissions = $roles->flatMap->permissions;
        $roleScopes = $roles->flatMap->scopes;

        $permissions = collect()
            ->merge($authenticatable->permissions->pluck('PermissionIdentifier'))
            ->merge($rolePermissions->pluck('PermissionIdentifier'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $scopes = collect()
            ->merge($roleScopes->pluck('ScopeIdentifier'))
            ->merge($authenticatable->permissions->pluck('PermissionScope'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $audiences = collect($roleScopes->pluck('ScopeShard'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($audiences === []) {
            $audiences = ['com.epicnova.shard.core'];
        }

        $accountIdentifier = $authenticatable->account?->AccountIdentifier;
        $profileIdentifier = $authenticatable->account?->profile?->ProfileIdentifier;

        $subjects = array_values(array_filter([
            $authenticatable->AuthenticatableIdentifier
                ? 'authenticatable_id:'.$authenticatable->AuthenticatableIdentifier
                : null,
            $accountIdentifier ? 'account_id:'.$accountIdentifier : null,
            $profileIdentifier ? 'profile_id:'.$profileIdentifier : null,
        ]));

        $lifetime = (int) config('auth.jwt.access_ttl', 3600);

        $tokens = $this->generateTokenPair(
            $audiences,
            $scopes,
            $permissions,
            $roleIdentifiers,
            $subjects,
            $lifetime,
            0,
            $profileIdentifier
        );

        return response()->json([
            'token_type' => 'Bearer',
            'expires_in' => $lifetime,
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'access_token_jti' => $tokens['access_token_jti'],
            'refresh_token_jti' => $tokens['refresh_token_jti'],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $payload = $this->resolveBearerPayload($request);
        $this->revokeIssuedTokenByJti($payload['jti'] ?? null);

        return response()->json([
            'status' => 'logged_out',
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $payload = $this->resolveBearerPayload($request);

        $tokens = $this->generateTokenPair(
            $payload['aud'] ?? [],
            $payload['Scopes'] ?? [],
            $payload['Permissions'] ?? [],
            $payload['Roles'] ?? [],
            $payload['sub'] ?? [],
            (int) config('auth.jwt.access_ttl', 3600),
            0,
            is_string($payload['CurrentProfile'] ?? null) ? $payload['CurrentProfile'] : null
        );

        $this->revokeIssuedTokenByJti($payload['jti'] ?? null);

        return response()->json([
            'token_type' => 'Bearer',
            'expires_in' => (int) config('auth.jwt.access_ttl', 3600),
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'access_token_jti' => $tokens['access_token_jti'],
            'refresh_token_jti' => $tokens['refresh_token_jti'],
        ]);
    }

    public function validate(Request $request): JsonResponse
    {
        $payload = $this->resolveBearerPayload($request);

        return response()->json([
            'valid' => true,
            'payload' => $payload,
        ]);
    }

    public function revoke(Request $request): JsonResponse
    {
        $payload = $this->resolveBearerPayload($request);
        $this->revokeIssuedTokenByJti($request->string('jti')->toString() ?: ($payload['jti'] ?? null));

        return response()->json([
            'status' => 'revoked',
        ]);
    }

    public function introspect(Request $request): JsonResponse
    {
        $payload = $this->resolveBearerPayload($request);
        $issuedToken = IssuedToken::query()->find($payload['jti'] ?? null);

        return response()->json([
            'active' => (bool) $issuedToken && $issuedToken->TokenState === 'Active',
            'payload' => $payload,
            'token_state' => $issuedToken?->TokenState,
            'token_expiry' => $issuedToken?->TokenExpiry,
        ]);
    }

    public function exchange(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'not_implemented',
        ], 501);
    }

    public function listImpersonations(Request $request): JsonResponse
    {
        return response()->json([
            'impersonations' => [],
        ]);
    }

    public function impersonate(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'not_implemented',
        ], 501);
    }

    public function revokeImpersonation(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'not_implemented',
        ], 501);
    }

    public function listDelegation(Request $request): JsonResponse
    {
        return response()->json([
            'delegations' => [],
        ]);
    }

    public function delegate(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'not_implemented',
        ], 501);
    }

    public function revokeDelegation(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'not_implemented',
        ], 501);
    }

    public function lookupUpdateLogs(Request $request): JsonResponse
    {
        $authenticatableIdentifier = $request->string('authenticatable_identifier')->toString();

        $query = AuthenticatableUpdateLog::query();
        if ($authenticatableIdentifier !== '') {
            $query->where('AuthenticatableIdentifier', $authenticatableIdentifier);
        }

        return response()->json([
            'update_logs' => $query->orderByDesc('CreatedAt')->get(),
        ]);
    }

    public function createUpdateLog(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'not_implemented',
        ], 501);
    }

    public function updateUpdateLog(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'not_implemented',
        ], 501);
    }

    public function deleteUpdateLog(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'not_implemented',
        ], 501);
    }

    public function lookupIssuedTokens(Request $request): JsonResponse
    {
        $authenticatableIdentifier = $request->string('authenticatable_identifier')->toString();

        $query = IssuedToken::query();
        if ($authenticatableIdentifier !== '') {
            $query->where('IssuedToAuthenticatable', $authenticatableIdentifier);
        }

        return response()->json([
            'issued_tokens' => $query->orderByDesc('IssuedAt')->get(),
        ]);
    }

    public function revokeIssuedToken(Request $request): JsonResponse
    {
        $tokenJti = $request->string('jti')->toString();
        $revoked = $this->revokeIssuedTokenByJti($tokenJti !== '' ? $tokenJti : null);

        return response()->json([
            'status' => $revoked ? 'revoked' : 'not_found',
        ], $revoked ? 200 : 404);
    }

    public function revokeAllIssuedTokens(Request $request): JsonResponse
    {
        $authenticatableIdentifier = $request->string('authenticatable_identifier')->toString();

        $query = IssuedToken::query();
        if ($authenticatableIdentifier !== '') {
            $query->where('IssuedToAuthenticatable', $authenticatableIdentifier);
        }

        $query->update(['TokenState' => 'Revoked']);

        return response()->json([
            'status' => 'revoked_all',
        ]);
    }

    private function resolveBearerPayload(Request $request): array
    {
        $payload = $request->attributes->get('bearer_payload');
        if (is_array($payload) && $payload !== []) {
            return $payload;
        }

        $bearerToken = $request->bearerToken();
        if (! is_string($bearerToken) || $bearerToken === '') {
            return [];
        }

        return $this->verifyAndDecodeToken($bearerToken);
    }

    private function revokeIssuedTokenByJti(?string $jti): bool
    {
        if (! is_string($jti) || $jti === '') {
            return false;
        }

        $issuedToken = IssuedToken::query()->find($jti);
        if (! $issuedToken) {
            return false;
        }

        $issuedToken->TokenState = 'Revoked';
        $issuedToken->save();

        return true;
    }
}

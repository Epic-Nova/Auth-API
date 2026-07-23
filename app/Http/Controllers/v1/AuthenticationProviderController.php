<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\CapabilityDiscoveryController;
use App\Http\Controllers\Controller;
use App\Models\Authentication\AttachedAuthenticationProvider;
use App\Models\Authentication\AuthenticationProvider;
use App\Models\Authentication\AuthenticationProviderScriptFunctionBlacklist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthenticationProviderController extends Controller
{
    /**
     * Matches: GET /api/v1/authentication-providers
     */
    public function index(): JsonResponse
    {
        $myCapabilities = CapabilityDiscoveryController::getCapabilitiesForFeature('v1', 'authentication-providers');

        $providers = AuthenticationProvider::query()
            ->with(['parameters', 'attachedProviders'])
            ->orderBy('ProviderFriendlyIdentifier')
            ->get();

        return response()->json([
            'feature' => 'authentication-providers',
            'version' => 'v1',
            'capabilities' => $myCapabilities,
            'providers' => $providers,
        ]);
    }

    /**
     * Matches: GET /api/v1/authentication-providers/lookup
     */
    public function lookup(Request $request): JsonResponse
    {
        $providerIdentifier = $request->query('provider_identifier');

        if (! $providerIdentifier) {
            return response()->json(['error' => 'provider_identifier query parameter is required'], 422);
        }

        $provider = AuthenticationProvider::query()
            ->where('AuthenticationProviderIdentifier', $providerIdentifier)
            ->orWhere('ProviderFriendlyIdentifier', $providerIdentifier)
            ->first();

        if (! $provider) {
            return response()->json(['error' => 'Authentication provider not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'provider' => [
                'identifier' => $provider->AuthenticationProviderIdentifier,
                'friendly_identifier' => $provider->ProviderFriendlyIdentifier,
            ],
        ]);
    }

    /**
     * Matches: GET /api/v1/authentication-providers/list
     */
    public function list(Request $request): JsonResponse
    {
        $providers = AuthenticationProvider::query()
            ->with(['parameters'])
            ->orderBy('ProviderFriendlyIdentifier')
            ->get();

        return response()->json([
            'status' => 'success',
            'providers' => $providers,
        ]);
    }

    /**
     * Matches: GET /api/v1/authentication-providers/{id}
     */
    public function show(string $id): JsonResponse
    {
        $provider = AuthenticationProvider::query()
            ->with(['parameters', 'attachedProviders'])
            ->where('AuthenticationProviderIdentifier', $id)
            ->orWhere('ProviderFriendlyIdentifier', $id)
            ->first();

        if (! $provider) {
            return response()->json(['error' => 'Authentication provider not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'provider' => $provider,
        ]);
    }

    /**
     * Matches: POST /api/v1/authentication-providers
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider_friendly_identifier' => 'required|string|max:128|unique:AuthConnection.AuthenticationProviders,ProviderFriendlyIdentifier',
            'provider_description' => 'nullable|string',
            'can_user_login' => 'required|boolean',
        ]);

        $provider = AuthenticationProvider::query()->create([
            'AuthenticationProviderIdentifier' => Str::uuid(),
            'ProviderFriendlyIdentifier' => $validated['provider_friendly_identifier'],
            'ProviderDescription' => $validated['provider_description'] ?? null,
            'bCanUserLoginWithProvider' => $validated['can_user_login'],
        ]);

        return response()->json([
            'status' => 'created',
            'provider' => $provider,
        ], 201);
    }

    /**
     * Matches: PUT /api/v1/authentication-providers/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $provider = AuthenticationProvider::query()
            ->where('AuthenticationProviderIdentifier', $id)
            ->orWhere('ProviderFriendlyIdentifier', $id)
            ->first();

        if (! $provider) {
            return response()->json(['error' => 'Authentication provider not found'], 404);
        }

        $validated = $request->validate([
            'provider_description' => 'nullable|string',
            'can_user_login' => 'nullable|boolean',
        ]);

        if (isset($validated['provider_description'])) {
            $provider->ProviderDescription = $validated['provider_description'];
        }
        if (isset($validated['can_user_login'])) {
            $provider->bCanUserLoginWithProvider = $validated['can_user_login'];
        }

        $provider->save();

        return response()->json([
            'status' => 'updated',
            'provider' => $provider,
        ]);
    }

    /**
     * Matches: DELETE /api/v1/authentication-providers/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $provider = AuthenticationProvider::query()
            ->where('AuthenticationProviderIdentifier', $id)
            ->orWhere('ProviderFriendlyIdentifier', $id)
            ->first();

        if (! $provider) {
            return response()->json(['error' => 'Authentication provider not found'], 404);
        }

        $provider->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Matches: POST /api/v1/authentication-providers/call
     */
    public function call(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider_identifier' => 'required|string',
            'function_name' => 'required|string|max:128',
            'parameters' => 'nullable|array',
        ]);

        // This would be implemented by specific provider handlers
        return response()->json([
            'status' => 'pending',
            'message' => 'Provider call requires specific implementation',
        ]);
    }

    /**
     * Matches: GET /api/v1/authentication-providers/index-attached
     */
    public function indexAttached(Request $request): JsonResponse
    {
        $authenticatableIdentifier = $request->query('authenticatable_identifier');

        if (! $authenticatableIdentifier) {
            return response()->json(['error' => 'authenticatable_identifier query parameter is required'], 422);
        }

        $attachedProviders = AttachedAuthenticationProvider::query()
            ->with(['authenticationProvider'])
            ->where('AuthenticatableIdentifier', $authenticatableIdentifier)
            ->get();

        return response()->json([
            'status' => 'success',
            'attached_providers' => $attachedProviders,
        ]);
    }

    /**
     * Matches: POST /api/v1/authentication-providers/attach
     */
    public function attach(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'authenticatable_identifier' => 'required|string',
            'provider_identifier' => 'required|string',
            'is_primary' => 'nullable|boolean',
            'provider_data' => 'nullable|array',
        ]);

        $attached = AttachedAuthenticationProvider::query()->create([
            'AuthenticatableIdentifier' => $validated['authenticatable_identifier'],
            'AuthenticationProviderIdentifier' => $validated['provider_identifier'],
            'bIsPrimary' => $validated['is_primary'] ?? false,
            'ProviderData' => json_encode($validated['provider_data'] ?? []),
            'ProviderState' => 'Active',
        ]);

        return response()->json([
            'status' => 'created',
            'attached' => $attached,
        ], 201);
    }

    /**
     * Matches: PUT /api/v1/authentication-providers/update-attachment
     */
    public function updateAttachment(Request $request): JsonResponse
    {
        $attachmentId = $request->input('attachment_id');

        if (! $attachmentId) {
            return response()->json(['error' => 'attachment_id is required'], 422);
        }

        $attached = AttachedAuthenticationProvider::query()->find($attachmentId);

        if (! $attached) {
            return response()->json(['error' => 'Attached provider not found'], 404);
        }

        $validated = $request->validate([
            'is_primary' => 'nullable|boolean',
            'provider_data' => 'nullable|array',
            'provider_state' => 'nullable|string',
        ]);

        if (isset($validated['is_primary'])) {
            $attached->bIsPrimary = $validated['is_primary'];
        }
        if (isset($validated['provider_data'])) {
            $attached->ProviderData = json_encode($validated['provider_data']);
        }
        if (isset($validated['provider_state'])) {
            $attached->ProviderState = $validated['provider_state'];
        }

        $attached->save();

        return response()->json([
            'status' => 'updated',
            'attached' => $attached,
        ]);
    }

    /**
     * Matches: DELETE /api/v1/authentication-providers/detach
     */
    public function detach(Request $request): JsonResponse
    {
        $attachmentId = $request->input('attachment_id');

        if (! $attachmentId) {
            return response()->json(['error' => 'attachment_id is required'], 422);
        }

        $attached = AttachedAuthenticationProvider::query()->find($attachmentId);

        if (! $attached) {
            return response()->json(['error' => 'Attached provider not found'], 404);
        }

        $attached->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Matches: GET /api/v1/authentication-providers/index-blacklist
     */
    public function indexBlacklist(Request $request): JsonResponse
    {
        $authenticatableIdentifier = $request->query('authenticatable_identifier');

        if (! $authenticatableIdentifier) {
            return response()->json(['error' => 'authenticatable_identifier query parameter is required'], 422);
        }

        $blacklist = AuthenticationProviderScriptFunctionBlacklist::query()
            ->where('AuthenticatableIdentifier', $authenticatableIdentifier)
            ->get();

        return response()->json([
            'status' => 'success',
            'blacklist' => $blacklist,
        ]);
    }

    /**
     * Matches: POST /api/v1/authentication-providers/blacklist
     */
    public function blacklist(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'authenticatable_identifier' => 'required|string',
            'script_name' => 'required|string|max:255',
        ]);

        $blacklisted = AuthenticationProviderScriptFunctionBlacklist::query()->create([
            'AuthenticatableIdentifier' => $validated['authenticatable_identifier'],
            'ScriptName' => $validated['script_name'],
        ]);

        return response()->json([
            'status' => 'created',
            'blacklisted' => $blacklisted,
        ], 201);
    }

    /**
     * Matches: DELETE /api/v1/authentication-providers/unblacklist
     */
    public function unblacklist(Request $request): JsonResponse
    {
        $blacklistId = $request->input('blacklist_id');

        if (! $blacklistId) {
            return response()->json(['error' => 'blacklist_id is required'], 422);
        }

        $blacklisted = AuthenticationProviderScriptFunctionBlacklist::query()->find($blacklistId);

        if (! $blacklisted) {
            return response()->json(['error' => 'Blacklist entry not found'], 404);
        }

        $blacklisted->delete();

        return response()->json(['status' => 'deleted']);
    }
}

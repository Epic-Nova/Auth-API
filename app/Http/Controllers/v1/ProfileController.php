<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\CapabilityDiscoveryController;
use App\Http\Controllers\Controller;
use App\Models\Authentication\Profile;
use App\Models\Authentication\RejectedRequestForProfileIdentifier;
use App\Models\Authentication\ReservedProfileIdentifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Matches: GET /api/v1/profile
     */
    public function index(): JsonResponse
    {
        $myCapabilities = CapabilityDiscoveryController::getCapabilitiesForFeature('v1', 'profile');

        return response()->json([
            'feature' => 'profile',
            'version' => 'v1',
            'capabilities' => $myCapabilities,
        ]);
    }

    /**
     * Matches: GET /api/v1/profile/lookup
     */
    public function lookup(Request $request): JsonResponse
    {
        $profileIdentifier = $request->query('profile_identifier');

        if (! $profileIdentifier) {
            return response()->json(['error' => 'profile_identifier query parameter is required'], 422);
        }

        $profile = Profile::query()->where('ProfileIdentifier', $profileIdentifier)->first();

        if (! $profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'profile' => [
                'identifier' => $profile->ProfileIdentifier,
                'account_identifier' => $profile->AccountIdentifier,
            ],
        ]);
    }

    /**
     * Matches: GET /api/v1/profile/list
     */
    public function list(Request $request): JsonResponse
    {
        $accountIdentifier = $request->query('account_identifier');

        if (! $accountIdentifier) {
            return response()->json(['error' => 'account_identifier query parameter is required'], 422);
        }

        $profiles = Profile::query()
            ->where('AccountIdentifier', $accountIdentifier)
            ->get();

        return response()->json([
            'status' => 'success',
            'profiles' => $profiles,
        ]);
    }

    /**
     * Matches: GET /api/v1/profile/{id}
     */
    public function show(string $id): JsonResponse
    {
        $profile = Profile::query()->where('ProfileIdentifier', $id)->first();

        if (! $profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'profile' => $profile,
        ]);
    }

    /**
     * Matches: POST /api/v1/profile
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_identifier' => 'required|string',
            'profile_name' => 'required|string|max:255',
            'profile_description' => 'nullable|string|max:1024',
        ]);

        $profile = Profile::query()->create([
            'ProfileIdentifier' => Str::uuid(),
            'AccountIdentifier' => $validated['account_identifier'],
            'ProfileName' => $validated['profile_name'],
            'ProfileDescription' => $validated['profile_description'] ?? null,
            'bIsProfilePublic' => false,
            'bIsProfileSearchable' => false,
            'bIsProfileVerified' => false,
            'bIsProfileDeletable' => true,
        ]);

        return response()->json([
            'status' => 'created',
            'profile' => $profile,
        ], 201);
    }

    /**
     * Matches: PUT /api/v1/profile/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $profile = Profile::query()->where('ProfileIdentifier', $id)->first();

        if (! $profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        $validated = $request->validate([
            'profile_name' => 'nullable|string|max:255',
            'profile_description' => 'nullable|string|max:1024',
            'is_public' => 'nullable|boolean',
            'is_searchable' => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
            'is_deletable' => 'nullable|boolean',
        ]);

        $updateData = [];
        if (isset($validated['profile_name'])) {
            $updateData['ProfileName'] = $validated['profile_name'];
        }
        if (isset($validated['profile_description'])) {
            $updateData['ProfileDescription'] = $validated['profile_description'];
        }
        if (isset($validated['is_public'])) {
            $updateData['bIsProfilePublic'] = $validated['is_public'];
        }
        if (isset($validated['is_searchable'])) {
            $updateData['bIsProfileSearchable'] = $validated['is_searchable'];
        }
        if (isset($validated['is_verified'])) {
            $updateData['bIsProfileVerified'] = $validated['is_verified'];
        }
        if (isset($validated['is_deletable'])) {
            $updateData['bIsProfileDeletable'] = $validated['is_deletable'];
        }

        $profile->update($updateData);

        return response()->json([
            'status' => 'updated',
            'profile' => $profile,
        ]);
    }

    /**
     * Matches: DELETE /api/v1/profile/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $profile = Profile::query()->where('ProfileIdentifier', $id)->first();

        if (! $profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        if (! $profile->bIsProfileDeletable) {
            return response()->json(['error' => 'Profile is not deletable'], 403);
        }

        $profile->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Matches: GET /api/v1/profile/lookup-reserved
     */
    public function lookupReserved(Request $request): JsonResponse
    {
        $profileIdentifier = $request->query('profile_identifier');

        if (! $profileIdentifier) {
            return response()->json(['error' => 'profile_identifier query parameter is required'], 422);
        }

        $reserved = ReservedProfileIdentifier::query()
            ->where('ProfileIdentifier', $profileIdentifier)
            ->first();

        if (! $reserved) {
            return response()->json(['error' => 'Reserved profile identifier not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'reserved' => $reserved,
        ]);
    }

    /**
     * Matches: POST /api/v1/profile/reserve
     */
    public function reserve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'profile_identifier' => 'required|string',
            'reserved_by' => 'nullable|string',
        ]);

        $reserved = ReservedProfileIdentifier::query()->create([
            'ProfileIdentifier' => $validated['profile_identifier'],
            'ReservedBy' => $validated['reserved_by'] ?? null,
        ]);

        return response()->json([
            'status' => 'created',
            'reserved' => $reserved,
        ], 201);
    }

    /**
     * Matches: DELETE /api/v1/profile/unreserve
     */
    public function unreserve(Request $request): JsonResponse
    {
        $profileIdentifier = $request->input('profile_identifier');

        if (! $profileIdentifier) {
            return response()->json(['error' => 'profile_identifier is required'], 422);
        }

        $reserved = ReservedProfileIdentifier::query()
            ->where('ProfileIdentifier', $profileIdentifier)
            ->first();

        if (! $reserved) {
            return response()->json(['error' => 'Reserved profile identifier not found'], 404);
        }

        $reserved->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Matches: GET /api/v1/profile/lookup-rejected
     */
    public function lookupRejected(Request $request): JsonResponse
    {
        $profileIdentifier = $request->query('profile_identifier');

        if (! $profileIdentifier) {
            return response()->json(['error' => 'profile_identifier query parameter is required'], 422);
        }

        $rejected = RejectedRequestForProfileIdentifier::query()
            ->where('ProfileIdentifier', $profileIdentifier)
            ->first();

        if (! $rejected) {
            return response()->json(['error' => 'Rejected profile identifier not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'rejected' => $rejected,
        ]);
    }

    /**
     * Matches: POST /api/v1/profile/reject
     */
    public function reject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'profile_identifier' => 'required|string',
            'rejection_reason' => 'nullable|string',
        ]);

        $rejected = RejectedRequestForProfileIdentifier::query()->create([
            'ProfileIdentifier' => $validated['profile_identifier'],
            'RejectionReason' => $validated['rejection_reason'] ?? null,
        ]);

        return response()->json([
            'status' => 'created',
            'rejected' => $rejected,
        ], 201);
    }

    /**
     * Matches: DELETE /api/v1/profile/unreject
     */
    public function unreject(Request $request): JsonResponse
    {
        $profileIdentifier = $request->input('profile_identifier');

        if (! $profileIdentifier) {
            return response()->json(['error' => 'profile_identifier is required'], 422);
        }

        $rejected = RejectedRequestForProfileIdentifier::query()
            ->where('ProfileIdentifier', $profileIdentifier)
            ->first();

        if (! $rejected) {
            return response()->json(['error' => 'Rejected profile identifier not found'], 404);
        }

        $rejected->delete();

        return response()->json(['status' => 'deleted']);
    }
}

<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\CapabilityDiscoveryController;
use App\Http\Controllers\Controller;
use App\Models\Authorisation\BannedAuthenticatable;
use App\Models\Authorisation\BannedAuthenticatableAppeal;
use App\Models\Authorisation\BannedAuthenticatableEvidence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BanController extends Controller
{
    /**
     * Matches: GET /api/v1/bans
     */
    public function index(): JsonResponse
    {
        $myCapabilities = CapabilityDiscoveryController::getCapabilitiesForFeature('v1', 'bans');

        $bans = BannedAuthenticatable::query()
            ->with(['bannedIn', 'appeals', 'evidences'])
            ->orderBy('BannedAt', 'desc')
            ->get();

        return response()->json([
            'feature' => 'bans',
            'version' => 'v1',
            'capabilities' => $myCapabilities,
            'bans' => $bans,
        ]);
    }

    /**
     * Matches: GET /api/v1/bans/lookup
     */
    public function lookup(Request $request): JsonResponse
    {
        $banIdentifier = $request->query('ban_identifier');

        if (! $banIdentifier) {
            return response()->json(['error' => 'ban_identifier query parameter is required'], 422);
        }

        $ban = BannedAuthenticatable::query()
            ->where('BanIdentifier', $banIdentifier)
            ->first();

        if (! $ban) {
            return response()->json(['error' => 'Ban not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'ban' => [
                'identifier' => $ban->BanIdentifier,
                'authenticatable_identifier' => $ban->AuthenticatableIdentifier,
            ],
        ]);
    }

    /**
     * Matches: GET /api/v1/bans/list
     */
    public function list(Request $request): JsonResponse
    {
        $bans = BannedAuthenticatable::query()
            ->with(['bannedIn', 'appeals', 'evidences'])
            ->orderBy('BannedAt', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'bans' => $bans,
        ]);
    }

    /**
     * Matches: GET /api/v1/bans/{id}
     */
    public function show(string $id): JsonResponse
    {
        $ban = BannedAuthenticatable::query()
            ->with(['bannedIn', 'appeals', 'evidences'])
            ->where('BanIdentifier', $id)
            ->first();

        if (! $ban) {
            return response()->json(['error' => 'Ban not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'ban' => $ban,
        ]);
    }

    /**
     * Matches: POST /api/v1/bans
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'authenticatable_identifier' => 'required|string',
            'ban_reason' => 'nullable|string',
            'ban_expires' => 'nullable|date',
        ]);

        $ban = BannedAuthenticatable::query()->create([
            'BanIdentifier' => Str::uuid(),
            'AuthenticatableIdentifier' => $validated['authenticatable_identifier'],
            'BanReason' => $validated['ban_reason'] ?? null,
            'BannedAt' => now(),
            'BanExpires' => $validated['ban_expires'] ?? null,
        ]);

        return response()->json([
            'status' => 'created',
            'ban' => $ban,
        ], 201);
    }

    /**
     * Matches: PUT /api/v1/bans/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $ban = BannedAuthenticatable::query()->where('BanIdentifier', $id)->first();

        if (! $ban) {
            return response()->json(['error' => 'Ban not found'], 404);
        }

        $validated = $request->validate([
            'ban_reason' => 'nullable|string',
            'ban_expires' => 'nullable|date',
        ]);

        if (isset($validated['ban_reason'])) {
            $ban->BanReason = $validated['ban_reason'];
        }
        if (isset($validated['ban_expires'])) {
            $ban->BanExpires = $validated['ban_expires'];
        }

        $ban->UpdatedAt = now();
        $ban->save();

        return response()->json([
            'status' => 'updated',
            'ban' => $ban,
        ]);
    }

    /**
     * Matches: DELETE /api/v1/bans/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $ban = BannedAuthenticatable::query()->where('BanIdentifier', $id)->first();

        if (! $ban) {
            return response()->json(['error' => 'Ban not found'], 404);
        }

        $ban->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Matches: GET /api/v1/bans/index-appeals
     */
    public function indexAppeals(Request $request): JsonResponse
    {
        $banIdentifier = $request->query('ban_identifier');

        if (! $banIdentifier) {
            return response()->json(['error' => 'ban_identifier query parameter is required'], 422);
        }

        $appeals = BannedAuthenticatableAppeal::query()
            ->where('BanIdentifier', $banIdentifier)
            ->orderBy('CreatedAt', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'appeals' => $appeals,
        ]);
    }

    /**
     * Matches: GET /api/v1/bans/lookup-appeal
     */
    public function lookupAppeal(Request $request): JsonResponse
    {
        $appealIdentifier = $request->query('appeal_identifier');

        if (! $appealIdentifier) {
            return response()->json(['error' => 'appeal_identifier query parameter is required'], 422);
        }

        $appeal = BannedAuthenticatableAppeal::query()
            ->where('AppealIdentifier', $appealIdentifier)
            ->first();

        if (! $appeal) {
            return response()->json(['error' => 'Appeal not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'appeal' => [
                'identifier' => $appeal->AppealIdentifier,
                'ban_identifier' => $appeal->BanIdentifier,
            ],
        ]);
    }

    /**
     * Matches: GET /api/v1/bans/list-appeal
     */
    public function listAppeal(Request $request): JsonResponse
    {
        $banIdentifier = $request->query('ban_identifier');

        if (! $banIdentifier) {
            return response()->json(['error' => 'ban_identifier query parameter is required'], 422);
        }

        $appeals = BannedAuthenticatableAppeal::query()
            ->where('BanIdentifier', $banIdentifier)
            ->get();

        return response()->json([
            'status' => 'success',
            'appeals' => $appeals,
        ]);
    }

    /**
     * Matches: POST /api/v1/bans/create-appeal
     */
    public function createAppeal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ban_identifier' => 'required|string',
            'appeal_reason' => 'required|string',
        ]);

        $appeal = BannedAuthenticatableAppeal::query()->create([
            'AppealIdentifier' => Str::uuid(),
            'BanIdentifier' => $validated['ban_identifier'],
            'AppealReason' => $validated['appeal_reason'],
            'AppealStatus' => 'Pending',
        ]);

        return response()->json([
            'status' => 'created',
            'appeal' => $appeal,
        ], 201);
    }

    /**
     * Matches: PUT /api/v1/bans/update-appeal
     */
    public function updateAppeal(Request $request): JsonResponse
    {
        $appealIdentifier = $request->input('appeal_identifier');

        if (! $appealIdentifier) {
            return response()->json(['error' => 'appeal_identifier is required'], 422);
        }

        $appeal = BannedAuthenticatableAppeal::query()
            ->where('AppealIdentifier', $appealIdentifier)
            ->first();

        if (! $appeal) {
            return response()->json(['error' => 'Appeal not found'], 404);
        }

        $validated = $request->validate([
            'appeal_reason' => 'nullable|string',
            'appeal_status' => 'nullable|string',
        ]);

        if (isset($validated['appeal_reason'])) {
            $appeal->AppealReason = $validated['appeal_reason'];
        }
        if (isset($validated['appeal_status'])) {
            $appeal->AppealStatus = $validated['appeal_status'];
        }

        $appeal->save();

        return response()->json([
            'status' => 'updated',
            'appeal' => $appeal,
        ]);
    }

    /**
     * Matches: DELETE /api/v1/bans/delete-appeal
     */
    public function deleteAppeal(Request $request): JsonResponse
    {
        $appealIdentifier = $request->input('appeal_identifier');

        if (! $appealIdentifier) {
            return response()->json(['error' => 'appeal_identifier is required'], 422);
        }

        $appeal = BannedAuthenticatableAppeal::query()
            ->where('AppealIdentifier', $appealIdentifier)
            ->first();

        if (! $appeal) {
            return response()->json(['error' => 'Appeal not found'], 404);
        }

        $appeal->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Matches: GET /api/v1/bans/index-evidences
     */
    public function indexEvidences(Request $request): JsonResponse
    {
        $banIdentifier = $request->query('ban_identifier');

        if (! $banIdentifier) {
            return response()->json(['error' => 'ban_identifier query parameter is required'], 422);
        }

        $evidences = BannedAuthenticatableEvidence::query()
            ->where('BanIdentifier', $banIdentifier)
            ->orderBy('CreatedAt', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'evidences' => $evidences,
        ]);
    }

    /**
     * Matches: GET /api/v1/bans/lookup-evidence
     */
    public function lookupEvidence(Request $request): JsonResponse
    {
        $evidenceIdentifier = $request->query('evidence_identifier');

        if (! $evidenceIdentifier) {
            return response()->json(['error' => 'evidence_identifier query parameter is required'], 422);
        }

        $evidence = BannedAuthenticatableEvidence::query()
            ->where('EvidenceIdentifier', $evidenceIdentifier)
            ->first();

        if (! $evidence) {
            return response()->json(['error' => 'Evidence not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'evidence' => [
                'identifier' => $evidence->EvidenceIdentifier,
                'ban_identifier' => $evidence->BanIdentifier,
            ],
        ]);
    }

    /**
     * Matches: GET /api/v1/bans/list-evidence
     */
    public function listEvidence(Request $request): JsonResponse
    {
        $banIdentifier = $request->query('ban_identifier');

        if (! $banIdentifier) {
            return response()->json(['error' => 'ban_identifier query parameter is required'], 422);
        }

        $evidences = BannedAuthenticatableEvidence::query()
            ->where('BanIdentifier', $banIdentifier)
            ->get();

        return response()->json([
            'status' => 'success',
            'evidences' => $evidences,
        ]);
    }

    /**
     * Matches: POST /api/v1/bans/create-evidence
     */
    public function createEvidence(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ban_identifier' => 'required|string',
            'evidence_description' => 'required|string',
            'evidence_data' => 'nullable|array',
        ]);

        $evidence = BannedAuthenticatableEvidence::query()->create([
            'EvidenceIdentifier' => Str::uuid(),
            'BanIdentifier' => $validated['ban_identifier'],
            'EvidenceDescription' => $validated['evidence_description'],
            'EvidenceData' => json_encode($validated['evidence_data'] ?? []),
        ]);

        return response()->json([
            'status' => 'created',
            'evidence' => $evidence,
        ], 201);
    }

    /**
     * Matches: PUT /api/v1/bans/update-evidence
     */
    public function updateEvidence(Request $request): JsonResponse
    {
        $evidenceIdentifier = $request->input('evidence_identifier');

        if (! $evidenceIdentifier) {
            return response()->json(['error' => 'evidence_identifier is required'], 422);
        }

        $evidence = BannedAuthenticatableEvidence::query()
            ->where('EvidenceIdentifier', $evidenceIdentifier)
            ->first();

        if (! $evidence) {
            return response()->json(['error' => 'Evidence not found'], 404);
        }

        $validated = $request->validate([
            'evidence_description' => 'nullable|string',
            'evidence_data' => 'nullable|array',
        ]);

        if (isset($validated['evidence_description'])) {
            $evidence->EvidenceDescription = $validated['evidence_description'];
        }
        if (isset($validated['evidence_data'])) {
            $evidence->EvidenceData = json_encode($validated['evidence_data']);
        }

        $evidence->save();

        return response()->json([
            'status' => 'updated',
            'evidence' => $evidence,
        ]);
    }

    /**
     * Matches: DELETE /api/v1/bans/delete-evidence
     */
    public function deleteEvidence(Request $request): JsonResponse
    {
        $evidenceIdentifier = $request->input('evidence_identifier');

        if (! $evidenceIdentifier) {
            return response()->json(['error' => 'evidence_identifier is required'], 422);
        }

        $evidence = BannedAuthenticatableEvidence::query()
            ->where('EvidenceIdentifier', $evidenceIdentifier)
            ->first();

        if (! $evidence) {
            return response()->json(['error' => 'Evidence not found'], 404);
        }

        $evidence->delete();

        return response()->json(['status' => 'deleted']);
    }
}

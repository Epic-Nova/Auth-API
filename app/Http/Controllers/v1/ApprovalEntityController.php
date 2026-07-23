<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\CapabilityDiscoveryController;
use App\Http\Controllers\Controller;
use App\Models\Authentication\ApprovalEntity;
use App\Models\Authentication\ApprovalEntityData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApprovalEntityController extends Controller
{
    /**
     * Matches: GET /api/v1/approval-entities
     */
    public function index(): JsonResponse
    {
        $myCapabilities = CapabilityDiscoveryController::getCapabilitiesForFeature('v1', 'approval-entities');

        $entities = ApprovalEntity::query()
            ->with(['parameters'])
            ->orderBy('ApprovalEntityName')
            ->get();

        return response()->json([
            'feature' => 'approval-entities',
            'version' => 'v1',
            'capabilities' => $myCapabilities,
            'approval_entities' => $entities,
        ]);
    }

    /**
     * Matches: GET /api/v1/approval-entities/lookup
     */
    public function lookup(Request $request): JsonResponse
    {
        $entityIdentifier = $request->query('entity_identifier');

        if (! $entityIdentifier) {
            return response()->json(['error' => 'entity_identifier query parameter is required'], 422);
        }

        $entity = ApprovalEntity::query()
            ->where('ApprovalEntityIdentifier', $entityIdentifier)
            ->first();

        if (! $entity) {
            return response()->json(['error' => 'Approval entity not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'entity' => [
                'identifier' => $entity->ApprovalEntityIdentifier,
                'name' => $entity->ApprovalEntityName,
            ],
        ]);
    }

    /**
     * Matches: GET /api/v1/approval-entities/list
     */
    public function list(Request $request): JsonResponse
    {
        $entities = ApprovalEntity::query()
            ->with(['parameters'])
            ->orderBy('ApprovalEntityName')
            ->get();

        return response()->json([
            'status' => 'success',
            'entities' => $entities,
        ]);
    }

    /**
     * Matches: GET /api/v1/approval-entities/{id}
     */
    public function show(string $id): JsonResponse
    {
        $entity = ApprovalEntity::query()
            ->with(['parameters', 'approvalEntityData'])
            ->where('ApprovalEntityIdentifier', $id)
            ->first();

        if (! $entity) {
            return response()->json(['error' => 'Approval entity not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'entity' => $entity,
        ]);
    }

    /**
     * Matches: POST /api/v1/approval-entities
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entity_name' => 'required|string|max:255',
            'entity_description' => 'nullable|string',
        ]);

        $entity = ApprovalEntity::query()->create([
            'ApprovalEntityIdentifier' => Str::uuid(),
            'ApprovalEntityName' => $validated['entity_name'],
            'ApprovalEntityDescription' => $validated['entity_description'] ?? null,
        ]);

        return response()->json([
            'status' => 'created',
            'entity' => $entity,
        ], 201);
    }

    /**
     * Matches: PUT /api/v1/approval-entities/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $entity = ApprovalEntity::query()->where('ApprovalEntityIdentifier', $id)->first();

        if (! $entity) {
            return response()->json(['error' => 'Approval entity not found'], 404);
        }

        $validated = $request->validate([
            'entity_name' => 'nullable|string|max:255',
            'entity_description' => 'nullable|string',
        ]);

        if (isset($validated['entity_name'])) {
            $entity->ApprovalEntityName = $validated['entity_name'];
        }
        if (isset($validated['entity_description'])) {
            $entity->ApprovalEntityDescription = $validated['entity_description'];
        }

        $entity->save();

        return response()->json([
            'status' => 'updated',
            'entity' => $entity,
        ]);
    }

    /**
     * Matches: DELETE /api/v1/approval-entities/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $entity = ApprovalEntity::query()->where('ApprovalEntityIdentifier', $id)->first();

        if (! $entity) {
            return response()->json(['error' => 'Approval entity not found'], 404);
        }

        $entity->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Matches: GET /api/v1/approval-entities/list-data
     */
    public function listData(Request $request): JsonResponse
    {
        $authenticatableIdentifier = $request->query('authenticatable_identifier');
        $entityIdentifier = $request->query('entity_identifier');

        if (! $authenticatableIdentifier || ! $entityIdentifier) {
            return response()->json(['error' => 'authenticatable_identifier and entity_identifier query parameters are required'], 422);
        }

        $data = ApprovalEntityData::query()
            ->where('AuthenticatableIdentifier', $authenticatableIdentifier)
            ->where('ApprovalEntityIdentifier', $entityIdentifier)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Matches: POST /api/v1/approval-entities/create-data
     */
    public function createData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'authenticatable_identifier' => 'required|string',
            'entity_identifier' => 'required|string',
            'entity_data' => 'required|array',
        ]);

        $entityData = ApprovalEntityData::query()->create([
            'ApprovalEntityDataIdentifier' => Str::uuid(),
            'AuthenticatableIdentifier' => $validated['authenticatable_identifier'],
            'ApprovalEntityIdentifier' => $validated['entity_identifier'],
            'EntityData' => json_encode($validated['entity_data']),
        ]);

        return response()->json([
            'status' => 'created',
            'data' => $entityData,
        ], 201);
    }

    /**
     * Matches: PUT /api/v1/approval-entities/update-data
     */
    public function updateData(Request $request): JsonResponse
    {
        $dataId = $request->input('data_id');

        if (! $dataId) {
            return response()->json(['error' => 'data_id is required'], 422);
        }

        $entityData = ApprovalEntityData::query()->where('ApprovalEntityDataIdentifier', $dataId)->first();

        if (! $entityData) {
            return response()->json(['error' => 'Approval entity data not found'], 404);
        }

        $validated = $request->validate([
            'entity_data' => 'required|array',
        ]);

        $entityData->EntityData = json_encode($validated['entity_data']);
        $entityData->save();

        return response()->json([
            'status' => 'updated',
            'data' => $entityData,
        ]);
    }

    /**
     * Matches: DELETE /api/v1/approval-entities/delete-data
     */
    public function deleteData(Request $request): JsonResponse
    {
        $dataId = $request->input('data_id');

        if (! $dataId) {
            return response()->json(['error' => 'data_id is required'], 422);
        }

        $entityData = ApprovalEntityData::query()->where('ApprovalEntityDataIdentifier', $dataId)->first();

        if (! $entityData) {
            return response()->json(['error' => 'Approval entity data not found'], 404);
        }

        $entityData->delete();

        return response()->json(['status' => 'deleted']);
    }
}

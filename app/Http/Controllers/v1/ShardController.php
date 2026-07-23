<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\CapabilityDiscoveryController;
use App\Http\Controllers\Controller;
use App\Models\Authorisation\Shard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShardController extends Controller
{
    /**
     * Matches: GET /api/v1/shards
     */
    public function index(): JsonResponse
    {
        $myCapabilities = CapabilityDiscoveryController::getCapabilitiesForFeature('v1', 'shards');

        $shards = Shard::query()
            ->orderBy('ShardName')
            ->get();

        return response()->json([
            'feature' => 'shards',
            'version' => 'v1',
            'capabilities' => $myCapabilities,
            'shards' => $shards,
        ]);
    }

    /**
     * Matches: GET /api/v1/shards/lookup
     */
    public function lookup(Request $request): JsonResponse
    {
        $shardIdentifier = $request->query('shard_identifier');

        if (! $shardIdentifier) {
            return response()->json(['error' => 'shard_identifier query parameter is required'], 422);
        }

        $shard = Shard::query()
            ->where('ShardIdentifier', $shardIdentifier)
            ->first();

        if (! $shard) {
            return response()->json(['error' => 'Shard not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'shard' => [
                'identifier' => $shard->ShardIdentifier,
                'name' => $shard->ShardName,
            ],
        ]);
    }

    /**
     * Matches: GET /api/v1/shards/list
     */
    public function list(Request $request): JsonResponse
    {
        $shards = Shard::query()
            ->orderBy('ShardName')
            ->get();

        return response()->json([
            'status' => 'success',
            'shards' => $shards,
        ]);
    }

    /**
     * Matches: GET /api/v1/shards/{id}
     */
    public function show(string $id): JsonResponse
    {
        $shard = Shard::query()
            ->where('ShardIdentifier', $id)
            ->first();

        if (! $shard) {
            return response()->json(['error' => 'Shard not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'shard' => $shard,
        ]);
    }

    /**
     * Matches: POST /api/v1/shards
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shard_name' => 'required|string|max:255',
            'shard_description' => 'nullable|string',
            'enable_parent_shard_inheritance' => 'nullable|boolean',
            'is_application_programming_interface' => 'nullable|boolean',
            'is_active' => 'required|boolean',
        ]);

        $shard = Shard::query()->create([
            'ShardIdentifier' => Str::uuid(),
            'ShardName' => $validated['shard_name'],
            'ShardDescription' => $validated['shard_description'] ?? null,
            'bEnableParentShardInheritance' => $validated['enable_parent_shard_inheritance'] ?? false,
            'bApplicationProgrammingInterface' => $validated['is_application_programming_interface'] ?? false,
            'bIsActive' => $validated['is_active'],
        ]);

        return response()->json([
            'status' => 'created',
            'shard' => $shard,
        ], 201);
    }

    /**
     * Matches: PUT /api/v1/shards/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $shard = Shard::query()->where('ShardIdentifier', $id)->first();

        if (! $shard) {
            return response()->json(['error' => 'Shard not found'], 404);
        }

        $validated = $request->validate([
            'shard_name' => 'nullable|string|max:255',
            'shard_description' => 'nullable|string',
            'enable_parent_shard_inheritance' => 'nullable|boolean',
            'is_application_programming_interface' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['shard_name'])) {
            $shard->ShardName = $validated['shard_name'];
        }
        if (isset($validated['shard_description'])) {
            $shard->ShardDescription = $validated['shard_description'];
        }
        if (isset($validated['enable_parent_shard_inheritance'])) {
            $shard->bEnableParentShardInheritance = $validated['enable_parent_shard_inheritance'];
        }
        if (isset($validated['is_application_programming_interface'])) {
            $shard->bApplicationProgrammingInterface = $validated['is_application_programming_interface'];
        }
        if (isset($validated['is_active'])) {
            $shard->bIsActive = $validated['is_active'];
        }

        $shard->save();

        return response()->json([
            'status' => 'updated',
            'shard' => $shard,
        ]);
    }

    /**
     * Matches: DELETE /api/v1/shards/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $shard = Shard::query()->where('ShardIdentifier', $id)->first();

        if (! $shard) {
            return response()->json(['error' => 'Shard not found'], 404);
        }

        $shard->delete();

        return response()->json(['status' => 'deleted']);
    }
}

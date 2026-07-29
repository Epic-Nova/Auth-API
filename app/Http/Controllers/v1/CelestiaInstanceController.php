<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\CapabilityDiscoveryController;
use App\Http\Controllers\Controller;
use App\Services\BearerTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CelestiaInstanceController extends Controller
{
    private const CACHE_INDEX = 'celestia:mesh:instances';

    /** @required-permission mesh.registry.read */
    public function index(Request $request, BearerTokenService $tokens): JsonResponse
    {
        if (! $this->allows($request, $tokens, 'mesh.registry.read')) return response()->json(['error' => 'forbidden'], 403);
        return response()->json([
            'feature' => 'celestia-instances', 'version' => 'v1',
            'capabilities' => CapabilityDiscoveryController::getCapabilitiesForFeature('v1', 'celestia-instances'),
            'instances' => $this->activeInstances(),
            'election_policy' => config('celestia.mesh.election'),
        ]);
    }

    /** @required-permission mesh.registry.write */
    public function heartbeat(Request $request, BearerTokenService $tokens): JsonResponse
    {
        if (! $this->allows($request, $tokens, 'mesh.registry.write')) return response()->json(['error' => 'forbidden'], 403);
        $data = $request->validate([
            'instance_id' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9._:-]+$/'],
            'mesh_endpoint' => ['required', 'url', 'max:2048'],
            'health' => ['required', 'in:healthy,degraded,unhealthy'],
            'operator_priority' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'uptime_seconds' => ['nullable', 'integer', 'min:0'],
            'capabilities' => ['nullable', 'array', 'max:128'],
            'capabilities.*' => ['string', 'max:128'],
        ]);
        $ttl = max(10, (int) config('celestia.mesh.heartbeat_ttl_seconds', 45));
        $record = array_merge($data, ['last_seen_at' => now()->toIso8601String(), 'expires_at' => now()->addSeconds($ttl)->toIso8601String()]);
        Cache::put('celestia:mesh:instance:'.$data['instance_id'], $record, now()->addSeconds($ttl));
        $ids = Cache::get(self::CACHE_INDEX, []); $ids[$data['instance_id']] = true;
        Cache::put(self::CACHE_INDEX, $ids, now()->addDay());
        return response()->json(['status' => 'accepted', 'heartbeat_ttl_seconds' => $ttl]);
    }

    /** @required-permission mesh.registry.read */
    public function election(Request $request, BearerTokenService $tokens): JsonResponse
    {
        if (! $this->allows($request, $tokens, 'mesh.registry.read')) return response()->json(['error' => 'forbidden'], 403);
        return response()->json(['instances' => $this->activeInstances(), 'policy' => config('celestia.mesh.election')]);
    }

    /** @required-permission celestia.update.read */
    public function updateManifest(Request $request, BearerTokenService $tokens): JsonResponse
    {
        if (! $this->allows($request, $tokens, 'celestia.update.read')) return response()->json(['error' => 'forbidden'], 403);
        return response()->json(['update' => config('celestia.updates')]);
    }

    private function activeInstances(): array
    {
        $out = [];
        foreach (array_keys(Cache::get(self::CACHE_INDEX, [])) as $id) {
            if ($record = Cache::get('celestia:mesh:instance:'.$id)) $out[] = $record;
        }
        usort($out, fn (array $a, array $b) => $a['instance_id'] <=> $b['instance_id']);
        return $out;
    }

    private function allows(Request $request, BearerTokenService $tokens, string $scope): bool
    {
        try { $payload = $tokens->resolvePayloadFromBearer($request->bearerToken()); }
        catch (Throwable) { return false; }
        return in_array($scope, $payload['Scopes'] ?? [], true);
    }
}

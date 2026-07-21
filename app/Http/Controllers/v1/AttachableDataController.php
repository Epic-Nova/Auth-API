<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\CapabilityDiscoveryController;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\AttachableDataStoreRequest;
use App\Http\Requests\v1\AttachableDataUpdateRequest;
use App\Models\Authentication\AttachableData;
use App\Models\Authentication\AttachedData;
use App\Models\Authentication\TemporarilyAttachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttachableDataController extends Controller
{
    public function index(): JsonResponse
    {
        $myCapabilities = CapabilityDiscoveryController::getCapabilitiesForFeature('v1', 'attachable-data');

        return response()->json([
            'feature' => 'attachable-data',
            'version' => 'v1',
            'capabilities' => $myCapabilities,
            'attachable_data' => AttachableData::query()
                ->with(['states', 'authenticationProvider', 'approvalEntity'])
                ->orderBy('AttachableDataName')
                ->get(),
        ]);
    }

    public function store(AttachableDataStoreRequest $request): JsonResponse
    {
        $validated = $this->normalizeAttachableDataPayload($request->validated());

        $attachableData = AttachableData::query()->create($validated);

        return response()->json([
            'status' => 'created',
            'attachable_data' => $attachableData->load(['states', 'authenticationProvider', 'approvalEntity']),
        ], 201);
    }

    public function createType(AttachableDataStoreRequest $request): JsonResponse
    {
        return $this->store($request);
    }

    public function show(string $id): JsonResponse
    {
        $attachableData = AttachableData::query()
            ->with(['states', 'authenticationProvider', 'approvalEntity'])
            ->findOrFail($id);

        return response()->json([
            'attachable_data' => $attachableData,
            'attached_data' => AttachedData::query()->where('AttachedDataType', $attachableData->AttachableDataName)->get(),
            'temporary_attached_data' => TemporarilyAttachedData::query()->where('AttachedDataType', $attachableData->AttachableDataName)->get(),
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $attachableDataName = $request->string('AttachableDataName')->toString();

        if ($attachableDataName === '') {
            return $this->index();
        }

        return $this->show($attachableDataName);
    }

    /** @required-role TrustedApplicationsOnly */
    /** @required-permission AttachableData.Manage */
    public function update(AttachableDataUpdateRequest $request, string $id): JsonResponse
    {
        $attachableData = AttachableData::query()->findOrFail($id);
        $validated = $this->normalizeAttachableDataPayload($request->validated());

        $attachableData->fill($validated)->save();

        return response()->json([
            'status' => 'updated',
            'attachable_data' => $attachableData->refresh()->load(['states', 'authenticationProvider', 'approvalEntity']),
        ]);
    }

    public function updateType(AttachableDataUpdateRequest $request, string $id): JsonResponse
    {
        return $this->update($request, $id);
    }

    /** @required-role TrustedApplicationsOnly */
    /** @required-permission AttachableData.Manage */
    public function destroy(string $id): JsonResponse
    {
        $attachableData = AttachableData::query()->findOrFail($id);
        $attachableData->delete();

        return response()->json([
            'status' => 'deleted',
        ]);
    }

    public function deleteType(string $id): JsonResponse
    {
        return $this->destroy($id);
    }

    /** @required-role TrustedApplicationsOnly */
    /** @required-permission AttachableData.Manage */
    public function attach(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'AttachedOnIdentifier' => ['required', 'string', 'max:36'],
            'AttachedOn' => ['required', 'string', 'in:Authenticatable,Account,Profile'],
            'AttachedDataType' => ['required', 'string', 'exists:AttachableData,AttachableDataName'],
            'AttachedData' => ['required', 'string'],
            'bIsPrimary' => ['sometimes', 'boolean'],
        ]);

        $attachedData = AttachedData::query()->updateOrCreate(
            [
                'AttachedOnIdentifier' => $validated['AttachedOnIdentifier'],
                'AttachedOn' => $validated['AttachedOn'],
                'AttachedDataType' => $validated['AttachedDataType'],
            ],
            [
                'AttachedData' => $validated['AttachedData'],
                'bIsPrimary' => $validated['bIsPrimary'] ?? false,
            ]
        );

        return response()->json([
            'status' => 'attached',
            'attached_data' => $attachedData,
        ], 201);
    }

    public function detach(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'AttachedOnIdentifier' => ['required', 'string', 'max:36'],
            'AttachedOn' => ['required', 'string', 'in:Authenticatable,Account,Profile'],
            'AttachedDataType' => ['required', 'string', 'exists:AttachableData,AttachableDataName'],
        ]);

        $deleted = AttachedData::query()
            ->where($validated)
            ->delete();

        return response()->json([
            'status' => $deleted > 0 ? 'detached' : 'not_found',
        ], $deleted > 0 ? 200 : 404);
    }

    /** @required-role TrustedApplicationsOnly */
    /** @required-permission AttachableData.Manage */
    public function attachTemporary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'AttachedOnIdentifier' => ['required', 'string', 'max:36'],
            'AttachedOn' => ['required', 'string', 'in:Authenticatable,Account,Profile'],
            'AttachedDataType' => ['required', 'string', 'exists:AttachableData,AttachableDataName'],
            'TemporarilyAttachedData' => ['required', 'string'],
            'ExpiresAt' => ['nullable', 'date'],
        ]);

        $temporaryAttachedData = TemporarilyAttachedData::query()->create([
            'AttachedOnIdentifier' => $validated['AttachedOnIdentifier'],
            'AttachedOn' => $validated['AttachedOn'],
            'AttachedDataType' => $validated['AttachedDataType'],
            'TemporarilyAttachedData' => $validated['TemporarilyAttachedData'],
            'ExpiresAt' => $validated['ExpiresAt'] ?? null,
        ]);

        return response()->json([
            'status' => 'temporarily_attached',
            'temporary_attached_data' => $temporaryAttachedData,
        ], 201);
    }

    public function lookupTemporary(Request $request): JsonResponse
    {
        $query = TemporarilyAttachedData::query();

        if ($request->filled('AttachedOnIdentifier')) {
            $query->where('AttachedOnIdentifier', $request->string('AttachedOnIdentifier'));
        }

        if ($request->filled('AttachedOn')) {
            $query->where('AttachedOn', $request->string('AttachedOn'));
        }

        if ($request->filled('AttachedDataType')) {
            $query->where('AttachedDataType', $request->string('AttachedDataType'));
        }

        return response()->json([
            'temporary_attached_data' => $query->get(),
        ]);
    }

    /** @required-role TrustedApplicationsOnly */
    /** @required-permission AttachableData.Manage */
    public function updateTemporary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'TemporarilyAttachedDataIdentifier' => ['required', 'ulid'],
            'TemporarilyAttachedData' => ['sometimes', 'string'],
            'ExpiresAt' => ['nullable', 'date'],
        ]);

        $temporaryAttachedData = TemporarilyAttachedData::query()->findOrFail($validated['TemporarilyAttachedDataIdentifier']);
        $temporaryAttachedData->fill(array_filter([
            'TemporarilyAttachedData' => $validated['TemporarilyAttachedData'] ?? null,
            'ExpiresAt' => $validated['ExpiresAt'] ?? null,
        ], static fn ($value) => $value !== null))->save();

        return response()->json([
            'status' => 'updated',
            'temporary_attached_data' => $temporaryAttachedData->refresh(),
        ]);
    }

    public function detachTemporary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'TemporarilyAttachedDataIdentifier' => ['required', 'ulid'],
        ]);

        $deleted = TemporarilyAttachedData::query()->where('TemporarilyAttachedDataIdentifier', $validated['TemporarilyAttachedDataIdentifier'])->delete();

        return response()->json([
            'status' => $deleted > 0 ? 'detached' : 'not_found',
        ], $deleted > 0 ? 200 : 404);
    }

    public function createData(Request $request): JsonResponse
    {
        return $this->attach($request);
    }

    public function updateData(Request $request): JsonResponse
    {
        return $this->updateTemporary($request);
    }

    public function deleteData(Request $request): JsonResponse
    {
        return $this->detach($request);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeAttachableDataPayload(array $validated): array
    {
        foreach (['AttachableForType', 'AuthenticationAndOrApprovalOn', 'AttachmentPossibility'] as $setField) {
            if (isset($validated[$setField]) && is_array($validated[$setField])) {
                $validated[$setField] = implode(',', $validated[$setField]);
            }
        }

        if (isset($validated['bIsUnique'])) {
            $validated['bIsUnique'] = (bool) $validated['bIsUnique'];
        }

        if (isset($validated['bCanHaveMultiple'])) {
            $validated['bCanHaveMultiple'] = (bool) $validated['bCanHaveMultiple'];
        }

        if (isset($validated['bInvertSafeList'])) {
            $validated['bInvertSafeList'] = (bool) $validated['bInvertSafeList'];
        }

        if (isset($validated['bUseSafeList'])) {
            $validated['bUseSafeList'] = (bool) $validated['bUseSafeList'];
        }

        return $validated;
    }
}

<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\CapabilityDiscoveryController;
use App\Http\Controllers\Controller;
use App\Models\Authentication\Account;
use App\Models\Authentication\AccountNote;
use App\Traits\LoginTokenTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    use LoginTokenTrait;

    /**
     * Matches: GET /api/v1/account
     * Returns its own self-describing capabilities registry matrix dynamically.
     */
    public function index(): JsonResponse
    {
        $myCapabilities = CapabilityDiscoveryController::getCapabilitiesForFeature('v1', 'account');

        return response()->json([
            'feature' => 'account',
            'version' => 'v1',
            'capabilities' => $myCapabilities,
        ]);
    }

    /**
     * Matches: GET /api/v1/account/lookup
     */
    public function lookup(Request $request): JsonResponse
    {
        $accountIdentifier = $request->query('account_identifier');

        if (! $accountIdentifier) {
            return response()->json([
                'error' => 'account_identifier query parameter is required',
            ], 422);
        }

        $account = Account::query()
            ->with(['profile', 'notes', 'attachedData', 'authenticationProviders'])
            ->where('AccountIdentifier', $accountIdentifier)
            ->first();

        if (! $account) {
            return response()->json([
                'error' => 'Account not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'account' => [
                'identifier' => $account->AccountIdentifier,
                'profile_identifier' => $account->profile?->ProfileIdentifier,
            ],
        ]);
    }

    /**
     * Matches: GET /api/v1/account/{id}
     */
    public function show(string $id): JsonResponse
    {
        $account = Account::query()
            ->with(['profile', 'notes', 'attachedData', 'authenticationProviders', 'temporarilyAttachedData'])
            ->where('AccountIdentifier', $id)
            ->first();

        if (! $account) {
            return response()->json([
                'error' => 'Account not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'account' => $account,
        ]);
    }

    /**
     * Matches: POST /api/v1/account
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'authenticatable_identifier' => 'required|string',
        ]);

        $account = Account::query()->create([
            'AccountIdentifier' => Str::uuid(),
            'AccountIdentifier' => $validated['authenticatable_identifier'],
        ]);

        return response()->json([
            'status' => 'created',
            'account' => $account->load(['profile', 'notes']),
        ], 201);
    }

    /**
     * Matches: PUT /api/v1/account/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $account = Account::query()->where('AccountIdentifier', $id)->first();

        if (! $account) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        $validated = $request->validate([
            'ClosedAt' => 'nullable|date',
            'PurgeAfter' => 'nullable|date',
        ]);

        $account->update($validated);

        return response()->json([
            'status' => 'updated',
            'account' => $account,
        ]);
    }

    /**
     * Matches: DELETE /api/v1/account/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $account = Account::query()->where('AccountIdentifier', $id)->first();

        if (! $account) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        $account->delete();

        return response()->json([
            'status' => 'deleted',
        ]);
    }

    /**
     * Matches: GET /api/v1/account/lookup-note
     */
    public function lookupNote(Request $request): JsonResponse
    {
        $noteIdentifier = $request->query('note_identifier');

        if (! $noteIdentifier) {
            return response()->json([
                'error' => 'note_identifier query parameter is required',
            ], 422);
        }

        $note = AccountNote::query()
            ->where('AccountNoteIdentifier', $noteIdentifier)
            ->first();

        if (! $note) {
            return response()->json(['error' => 'Note not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'note' => [
                'identifier' => $note->AccountNoteIdentifier,
                'account_identifier' => $note->AccountIdentifier,
            ],
        ]);
    }

    /**
     * Matches: GET /api/v1/account/list-notes
     */
    public function listNotes(Request $request): JsonResponse
    {
        $accountIdentifier = $request->query('account_identifier');

        if (! $accountIdentifier) {
            return response()->json([
                'error' => 'account_identifier query parameter is required',
            ], 422);
        }

        $notes = AccountNote::query()
            ->where('AccountIdentifier', $accountIdentifier)
            ->get();

        return response()->json([
            'status' => 'success',
            'notes' => $notes,
        ]);
    }

    /**
     * Matches: POST /api/v1/account/create-note
     */
    public function createNote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_identifier' => 'required|string',
            'note' => 'required|string',
        ]);

        $note = AccountNote::query()->create([
            'AccountNoteIdentifier' => Str::uuid(),
            'AccountIdentifier' => $validated['account_identifier'],
            'AccountNote' => $validated['note'],
        ]);

        return response()->json([
            'status' => 'created',
            'note' => $note,
        ], 201);
    }

    /**
     * Matches: PUT /api/v1/account/update-note
     */
    public function updateNote(Request $request): JsonResponse
    {
        $noteIdentifier = $request->input('note_identifier');

        if (! $noteIdentifier) {
            return response()->json(['error' => 'note_identifier is required'], 422);
        }

        $note = AccountNote::query()->where('AccountNoteIdentifier', $noteIdentifier)->first();

        if (! $note) {
            return response()->json(['error' => 'Note not found'], 404);
        }

        $validated = $request->validate([
            'note' => 'required|string',
        ]);

        $note->update(['AccountNote' => $validated['note']]);

        return response()->json([
            'status' => 'updated',
            'note' => $note,
        ]);
    }

    /**
     * Matches: DELETE /api/v1/account/delete-note
     */
    public function deleteNote(Request $request): JsonResponse
    {
        $noteIdentifier = $request->input('note_identifier');

        if (! $noteIdentifier) {
            return response()->json(['error' => 'note_identifier is required'], 422);
        }

        $note = AccountNote::query()->where('AccountNoteIdentifier', $noteIdentifier)->first();

        if (! $note) {
            return response()->json(['error' => 'Note not found'], 404);
        }

        $note->delete();

        return response()->json(['status' => 'deleted']);
    }
}

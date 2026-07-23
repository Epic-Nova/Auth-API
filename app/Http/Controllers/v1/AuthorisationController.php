<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\CapabilityDiscoveryController;
use App\Http\Controllers\Controller;
use App\Models\Authorisation\Permission;
use App\Models\Authorisation\Role;
use App\Models\Authorisation\Scope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthorisationController extends Controller
{
    /**
     * Matches: GET /api/v1/authorisation
     */
    public function index(): JsonResponse
    {
        $myCapabilities = CapabilityDiscoveryController::getCapabilitiesForFeature('v1', 'authorisation');

        $roles = Role::query()->with(['permissions', 'scopes'])->orderBy('RoleName')->get();

        return response()->json([
            'feature' => 'authorisation',
            'version' => 'v1',
            'capabilities' => $myCapabilities,
            'roles' => $roles,
        ]);
    }

    /**
     * Matches: GET /api/v1/authorisation/lookup
     */
    public function lookup(Request $request): JsonResponse
    {
        $roleIdentifier = $request->query('role_identifier');

        if (! $roleIdentifier) {
            return response()->json(['error' => 'role_identifier query parameter is required'], 422);
        }

        $role = Role::query()
            ->where('RoleIdentifier', $roleIdentifier)
            ->first();

        if (! $role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'role' => [
                'identifier' => $role->RoleIdentifier,
                'name' => $role->RoleName,
            ],
        ]);
    }

    /**
     * Matches: GET /api/v1/authorisation/list
     */
    public function list(Request $request): JsonResponse
    {
        $roles = Role::query()
            ->with(['permissions', 'scopes'])
            ->orderBy('RoleName')
            ->get();

        return response()->json([
            'status' => 'success',
            'roles' => $roles,
        ]);
    }

    /**
     * Matches: GET /api/v1/authorisation/{id}
     */
    public function show(string $id): JsonResponse
    {
        $role = Role::query()
            ->with(['permissions', 'scopes', 'shards'])
            ->where('RoleIdentifier', $id)
            ->first();

        if (! $role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'role' => $role,
        ]);
    }

    /**
     * Matches: POST /api/v1/authorisation
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role_name' => 'required|string|max:255',
            'role_description' => 'nullable|string',
        ]);

        $role = Role::query()->create([
            'RoleIdentifier' => Str::uuid(),
            'RoleName' => $validated['role_name'],
            'RoleDescription' => $validated['role_description'] ?? null,
        ]);

        return response()->json([
            'status' => 'created',
            'role' => $role,
        ], 201);
    }

    /**
     * Matches: PUT /api/v1/authorisation/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $role = Role::query()->where('RoleIdentifier', $id)->first();

        if (! $role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        $validated = $request->validate([
            'role_name' => 'nullable|string|max:255',
            'role_description' => 'nullable|string',
        ]);

        if (isset($validated['role_name'])) {
            $role->RoleName = $validated['role_name'];
        }
        if (isset($validated['role_description'])) {
            $role->RoleDescription = $validated['role_description'];
        }

        $role->save();

        return response()->json([
            'status' => 'updated',
            'role' => $role,
        ]);
    }

    /**
     * Matches: DELETE /api/v1/authorisation/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $role = Role::query()->where('RoleIdentifier', $id)->first();

        if (! $role) {
            return response()->json(['error' => 'Role not found'], 404);
        }

        $role->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Matches: GET /api/v1/authorisation/index-scope
     */
    public function indexScope(): JsonResponse
    {
        $scopes = Scope::query()->orderBy('ScopeName')->get();

        return response()->json([
            'status' => 'success',
            'scopes' => $scopes,
        ]);
    }

    /**
     * Matches: GET /api/v1/authorisation/lookup-scope
     */
    public function lookupScope(Request $request): JsonResponse
    {
        $scopeIdentifier = $request->query('scope_identifier');

        if (! $scopeIdentifier) {
            return response()->json(['error' => 'scope_identifier query parameter is required'], 422);
        }

        $scope = Scope::query()
            ->where('ScopeIdentifier', $scopeIdentifier)
            ->first();

        if (! $scope) {
            return response()->json(['error' => 'Scope not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'scope' => [
                'identifier' => $scope->ScopeIdentifier,
                'name' => $scope->ScopeName,
            ],
        ]);
    }

    /**
     * Matches: GET /api/v1/authorisation/list-scope
     */
    public function listScope(): JsonResponse
    {
        $scopes = Scope::query()->orderBy('ScopeName')->get();

        return response()->json([
            'status' => 'success',
            'scopes' => $scopes,
        ]);
    }

    /**
     * Matches: POST /api/v1/authorisation/create-scope
     */
    public function createScope(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'scope_name' => 'required|string|max:255',
            'scope_description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $scope = Scope::query()->create([
            'ScopeIdentifier' => Str::uuid(),
            'ScopeName' => $validated['scope_name'],
            'ScopeDescription' => $validated['scope_description'] ?? null,
            'bIsActive' => $validated['is_active'],
        ]);

        return response()->json([
            'status' => 'created',
            'scope' => $scope,
        ], 201);
    }

    /**
     * Matches: PUT /api/v1/authorisation/update-scope
     */
    public function updateScope(Request $request): JsonResponse
    {
        $scopeIdentifier = $request->input('scope_identifier');

        if (! $scopeIdentifier) {
            return response()->json(['error' => 'scope_identifier is required'], 422);
        }

        $scope = Scope::query()->where('ScopeIdentifier', $scopeIdentifier)->first();

        if (! $scope) {
            return response()->json(['error' => 'Scope not found'], 404);
        }

        $validated = $request->validate([
            'scope_name' => 'nullable|string|max:255',
            'scope_description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['scope_name'])) {
            $scope->ScopeName = $validated['scope_name'];
        }
        if (isset($validated['scope_description'])) {
            $scope->ScopeDescription = $validated['scope_description'];
        }
        if (isset($validated['is_active'])) {
            $scope->bIsActive = $validated['is_active'];
        }

        $scope->save();

        return response()->json([
            'status' => 'updated',
            'scope' => $scope,
        ]);
    }

    /**
     * Matches: DELETE /api/v1/authorisation/delete-scope
     */
    public function deleteScope(Request $request): JsonResponse
    {
        $scopeIdentifier = $request->input('scope_identifier');

        if (! $scopeIdentifier) {
            return response()->json(['error' => 'scope_identifier is required'], 422);
        }

        $scope = Scope::query()->where('ScopeIdentifier', $scopeIdentifier)->first();

        if (! $scope) {
            return response()->json(['error' => 'Scope not found'], 404);
        }

        $scope->delete();

        return response()->json(['status' => 'deleted']);
    }

    /**
     * Matches: GET /api/v1/authorisation/index-permission
     */
    public function indexPermission(): JsonResponse
    {
        $permissions = Permission::query()->orderBy('PermissionName')->get();

        return response()->json([
            'status' => 'success',
            'permissions' => $permissions,
        ]);
    }

    /**
     * Matches: GET /api/v1/authorisation/lookup-permission
     */
    public function lookupPermission(Request $request): JsonResponse
    {
        $permissionIdentifier = $request->query('permission_identifier');

        if (! $permissionIdentifier) {
            return response()->json(['error' => 'permission_identifier query parameter is required'], 422);
        }

        $permission = Permission::query()
            ->where('PermissionIdentifier', $permissionIdentifier)
            ->first();

        if (! $permission) {
            return response()->json(['error' => 'Permission not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'permission' => [
                'identifier' => $permission->PermissionIdentifier,
                'name' => $permission->PermissionName,
            ],
        ]);
    }

    /**
     * Matches: GET /api/v1/authorisation/list-permission
     */
    public function listPermission(): JsonResponse
    {
        $permissions = Permission::query()->orderBy('PermissionName')->get();

        return response()->json([
            'status' => 'success',
            'permissions' => $permissions,
        ]);
    }

    /**
     * Matches: POST /api/v1/authorisation/create-permission
     */
    public function createPermission(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'permission_name' => 'required|string|max:255',
            'permission_description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $permission = Permission::query()->create([
            'PermissionIdentifier' => Str::uuid(),
            'PermissionName' => $validated['permission_name'],
            'PermissionDescription' => $validated['permission_description'] ?? null,
            'bIsActive' => $validated['is_active'],
        ]);

        return response()->json([
            'status' => 'created',
            'permission' => $permission,
        ], 201);
    }

    /**
     * Matches: PUT /api/v1/authorisation/update-permission
     */
    public function updatePermission(Request $request): JsonResponse
    {
        $permissionIdentifier = $request->input('permission_identifier');

        if (! $permissionIdentifier) {
            return response()->json(['error' => 'permission_identifier is required'], 422);
        }

        $permission = Permission::query()->where('PermissionIdentifier', $permissionIdentifier)->first();

        if (! $permission) {
            return response()->json(['error' => 'Permission not found'], 404);
        }

        $validated = $request->validate([
            'permission_name' => 'nullable|string|max:255',
            'permission_description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['permission_name'])) {
            $permission->PermissionName = $validated['permission_name'];
        }
        if (isset($validated['permission_description'])) {
            $permission->PermissionDescription = $validated['permission_description'];
        }
        if (isset($validated['is_active'])) {
            $permission->bIsActive = $validated['is_active'];
        }

        $permission->save();

        return response()->json([
            'status' => 'updated',
            'permission' => $permission,
        ]);
    }

    /**
     * Matches: DELETE /api/v1/authorisation/delete-permission
     */
    public function deletePermission(Request $request): JsonResponse
    {
        $permissionIdentifier = $request->input('permission_identifier');

        if (! $permissionIdentifier) {
            return response()->json(['error' => 'permission_identifier is required'], 422);
        }

        $permission = Permission::query()->where('PermissionIdentifier', $permissionIdentifier)->first();

        if (! $permission) {
            return response()->json(['error' => 'Permission not found'], 404);
        }

        $permission->delete();

        return response()->json(['status' => 'deleted']);
    }
}

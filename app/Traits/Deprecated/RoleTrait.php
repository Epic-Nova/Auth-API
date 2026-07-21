<?php

namespace App\Traits\Deprecated;

use App\Models\Authorisation\Role;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait RoleTrait
{
    use ErrorHandlerTrait;

    /**
     * Checks if a Role Exists
     *
     * @param  string  $Role  The Role Identifier
     * @return bool True if the Role exists, false otherwise.
     */
    public function RoleExists(string $Role): bool
    {
        Log::debug('RoleExists: Checking if Role exists: '.$Role);

        if (Cache::has("Role_$Role")) {
            Log::debug('RoleExists: Role exists in Cache');

            return true;
        }

        $RoleExists = Role::whereRoleidentifier($Role)->exists();
        if (! $RoleExists) {
            Log::error('RoleExists: Role does not exist: '.$Role);
            $this->HandleError('errors.com.epicnova.role.not_found', ['RoleIdentifier' => $Role]);
        }

        Log::debug('RoleExists: Role exists: '.$Role);

        return true;
    }

    /**
     * Returns a Role by its Identifier
     *
     * @param  string  $Role  The Role Identifier
     * @return Role|null The Role
     */
    public function GetRole(string $Role): ?Role
    {
        Log::debug('GetRole: Getting Role with RoleIdentifier: '.$Role);

        $this->RoleExists($Role);

        if (Cache::has("Role_$Role")) {
            Log::debug('GetRole: Role: '.$Role.' is cached');

            return Cache::get("Role_$Role");
        } else {
            Log::debug('GetRole: Role does not exist in Cache, getting from Database and caching it');
            $Role = Role::find($Role);
            Cache::put("Role_$Role->RoleIdentifier", $Role, Carbon::now()->addHours(5));

            return $Role;
        }
    }

    /**
     * Checks if a Role has a Parent Role
     *
     * @param  string|null  $Role  The Role Identifier
     * @return bool True if the Role has a Parent Role, false otherwise.
     */
    public function HasParentRole(?string $Role = ''): bool
    {
        Log::debug('HasParentRole: Checking if Role has a Parent Role');

        $this->CheckRoleVariable($Role);

        return $this->GetRole($Role)->ParentRole != null;
    }

    /**
     * Get all Parent Roles of a Role
     *
     * @param  bool  $IncludeSelf  Include the Role in the list of Parent Roles
     * @param  string|null  $Role  The Role Identifier
     * @return array|null All Parent Roles of a Role
     */
    public function GetParentRoles(bool $IncludeSelf, ?string $Role = ''): ?array
    {
        $this->CheckRoleVariable($Role);

        Log::debug('GetParentRoles: Getting all Parent Roles of a Role');

        $ParentRoles = null;
        $CurrentRole = $this->GetRole($Role);

        if ($IncludeSelf) {
            Log::debug('GetParentRoles: Including self in list of Parent Roles');
            $ParentRoles[] = $CurrentRole;
        }

        while ($this->HasParentRole($CurrentRole->RoleIdentifier)) {
            $ParentRoles[] = $CurrentRole->ParentRole;
            $CurrentRole = $CurrentRole->ParentRole;
        }

        return $ParentRoles;
    }

    /**
     * Gets all Shards assigned to a Role
     */
    public function GetShards(?string $Role): array
    {
        $this->CheckRoleVariable($Role);
        $this->RoleExists($Role);

        Log::debug('GetShards: Getting all Shards assigned to: '.$Role);

        $Shards = [];
        $Role = $this->GetRole($Role);

        foreach ($Role->Shards as $Shard) {
            $Shards[] = $Shard;
        }

        return $Shards;
    }

    /**
     * Returns all Scopes associated with a Role
     *
     * @param  string|null  $Role  The Role Identifier
     * @return array All Scopes associated with the Role
     */
    public function GetScopes(?string $Role = ''): array
    {
        $this->CheckRoleVariable($Role);

        Log::debug('GetScopes: Getting Scopes for Role: '.$Role);

        $Scopes = [];
        $Role = $this->GetRole($Role);

        foreach ($Role->Scopes as $Scope) {
            $Scopes[] = $Scope;
        }

        return $Scopes;
    }

    /**
     * Returns all Permissions associated with a Role
     *
     * @param  string|null  $Role  The Client Identifier
     * @return array All Permissions associated with the Role
     */
    public function GetPermissions(?string $Role = ''): array
    {
        $this->CheckRoleVariable($Role);

        Log::debug('GetPermissions: Getting Permissions for Role: '.$Role);

        $Permissions = [];
        $Role = $this->GetRole($Role);

        foreach ($Role->Permissions as $Permission) {
            $Permissions[] = $Permission;
        }

        return $Permissions;
    }

    /**
     * Attaches an Shard to a Role
     *
     * @param  string  $Shard  The Shard Identifier
     * @param  string|null  $Role  The Role Identifier
     */
    public function AttachShardToRole(string $Shard, ?string $Role = ''): void
    {
        $this->CheckRoleVariable($Role);

        $Role = $this->GetRole($Role);
        $Role->Shards()->attach($Shard);
        $Role->save();

        Log::info('AttachShardToRole: Attached Shard '.$Shard.' to Role '.$Role->RoleIdentifier);
    }

    /**
     * Detaches an Shard from a Role
     *
     * @param  string  $Shard  The Shard Identifier
     * @param  string|null  $Role  The Role Identifier
     */
    public function DetachShardFromRole(string $Shard, ?string $Role = ''): void
    {
        $this->CheckRoleVariable($Role);

        $Role = $this->GetRole($Role);
        $Role->Shards()->detach($Shard);
        $Role->save();

        Log::info('DetachShardFromRole: Detached Shard '.$Shard.' from Role '.$Role->RoleIdentifier);
    }

    /**
     * Attaches a Scope to a Role
     *
     * @param  string  $Scope  The Scope Identifier
     * @param  string|null  $Role  The Role Identifier
     */
    public function AttachScopeToRole(string $Scope, ?string $Role = ''): void
    {
        $this->CheckRoleVariable($Role);

        $Role = $this->GetRole($Role);
        $Role->Scopes()->attach($Scope);
        $Role->save();

        Log::info('AttachScopeToRole: Attached Scope '.$Scope.' to Role '.$Role->RoleIdentifier);
    }

    /**
     * Detaches a Scope from a Role
     *
     * @param  string  $Scope  The Scope Identifier
     * @param  string|null  $Role  The Role Identifier
     */
    public function DetachScopeFromRole(string $Scope, ?string $Role = ''): void
    {
        $this->CheckRoleVariable($Role);

        $Role = $this->GetRole($Role);
        $Role->Scopes()->detach($Scope);
        $Role->save();

        Log::info('DetachScopeFromRole: Detached Scope '.$Scope.' from Role '.$Role->RoleIdentifier);
    }

    /**
     * Attaches a Permission to a Role
     *
     * @param  string  $Permission  The Permission Identifier
     * @param  string|null  $Role  The Role Identifier
     */
    public function AttachPermissionToRole(string $Permission, ?string $Role = ''): void
    {
        $this->CheckRoleVariable($Role);

        $Role = $this->GetRole($Role);
        $Role->Permissions()->attach($Permission);
        $Role->save();

        Log::info('AttachPermissionToRole: Attached Permission '.$Permission.' to Role '.$Role->RoleIdentifier);
    }

    /**
     * Detaches a Permission from a Role
     *
     * @param  string  $Permission  The Permission Identifier
     * @param  string|null  $Role  The Role Identifier
     */
    public function DetachPermissionFromRole(string $Permission, ?string $Role = ''): void
    {
        $this->CheckRoleVariable($Role);

        $Role = $this->GetRole($Role);
        $Role->Permissions()->detach($Permission);
        $Role->save();

        Log::info('DetachPermissionFromRole: Detached Permission '.$Permission.' from Role '.$Role->RoleIdentifier);
    }

    /**
     * Attaches a Permission Collection to a Role
     *
     * @param  string  $PermissionCollection  The Permission Collection Identifier
     * @param  string|null  $Role  The Role Identifier
     */
    public function AttachPermissionCollectionToRole(string $PermissionCollection, ?string $Role = ''): void
    {
        $this->CheckRoleVariable($Role);

        $Role = $this->GetRole($Role);
        $Role->PermissionCollections()->attach($PermissionCollection);
        $Role->save();

        Log::info('AttachPermissionCollectionToRole: Attached Permission Collection '.$PermissionCollection.' to Role '.$Role->RoleIdentifier);
    }

    /**
     * Detaches a Permission Collection from a Role
     *
     * @param  string  $PermissionCollection  The Permission Collection Identifier
     * @param  string|null  $Role  The Role Identifier
     */
    public function DetachPermissionCollectionFromRole(string $PermissionCollection, ?string $Role = ''): void
    {
        $this->CheckRoleVariable($Role);

        $Role = $this->GetRole($Role);
        $Role->PermissionCollections()->detach($PermissionCollection);
        $Role->save();

        Log::info('DetachPermissionCollectionFromRole: Detached Permission Collection '.$PermissionCollection.' from Role '.$Role->RoleIdentifier);
    }

    /**
     * Checks a Role Identifier and modifies it when needed
     *
     * @param  string  $Role  The reference to the Role Identifier to check
     */
    protected function CheckRoleVariable(string &$Role): void
    {
        Log::debug('CheckRoleVariable: Checking if Role is empty');

        if (empty($Role)) {
            Log::debug('CheckRoleVariable: Role Variable not set, trying to set it to the RoleIdentifier of the owning Class');
            $Role = $this->RoleIdentifier;
        } else {
            Log::debug('CheckRoleVariable: Role Variable is set');
        }
    }
}

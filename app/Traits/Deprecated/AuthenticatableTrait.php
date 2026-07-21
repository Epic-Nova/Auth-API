<?php

namespace App\Traits\Deprecated;

use App\Models\Authentication\Authenticatable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Abstract class that provides methods for retrieving, validating a
 */
trait AuthenticatableTrait
{
    use PermissionCollectionTrait, PermissionTrait, ScopeTrait, ShardTrait;

    /**
     * Checks if an Authenticatable Exists
     *
     * @param  string  $Authenticatable  The Authenticatable Identifier
     * @return bool True if the Authenticatable Exists, false if not
     */
    public function AuthenticatableExists(string $Authenticatable): bool
    {
        Log::debug('AuthenticatableExists: Checking if Authenticatable exists: '.$Authenticatable);

        if (Cache::has("Authenticatable_$Authenticatable")) {
            Log::debug('AuthenticatableExists: Authenticatable exists in Cache');

            return true;
        }

        $AuthenticatableExists = Authenticatable::whereAuthenticatableidentifier($Authenticatable)->exists();
        if (! $AuthenticatableExists) {
            Log::error('AuthenticatableExists: Authenticatable does not exist: '.$Authenticatable);
            $this->HandleError('errors.com.epicnova.authenticatable.not_found', ['AuthenticatableIdentifier' => $Authenticatable]);
        }

        Log::debug('AuthenticatableExists: Authenticatable exists: '.$Authenticatable);

        return true;
    }

    /**
     * Returns a Authenticatable by its Identifier
     *
     * @param  string  $Authenticatable  The Authenticatable Identifier
     * @return Authenticatable|null The Authenticatable if it exists, null if not
     */
    public function GetAuthenticatable(string $Authenticatable): ?Authenticatable
    {
        Log::debug('GetAuthenticatable: Getting Authenticatable with AuthenticatableIdentifier: '.$Authenticatable);

        $this->AuthenticatableExists($Authenticatable);

        if (Cache::has("Authenticatable_$Authenticatable")) {
            Log::debug('GetAuthenticatable: Authenticatable is cached: '.$Authenticatable);

            return Cache::get("Authenticatable_$Authenticatable");
        } else {
            Log::debug('GetAuthenticatable: Authenticatable is not cached, getting from Database and caching it: '.$Authenticatable);
            $Authenticatable = Authenticatable::find($Authenticatable);
            Cache::put("Authenticatable_$Authenticatable->AuthenticatableIdentifier", $Authenticatable, Carbon::now()->addHours(5));

            return $Authenticatable;
        }
    }

    /**
     * @deprecated This function will be removed in a future version, due to the changes to the Client Account relationship
     */
    public function IsServerClient(string $Client): bool
    {
        $this->CheckClientVariable($Client);

        Log::debug('IsServerClient: Checking if Client is a ServerClient: '.$Client);

        $Client = $this->GetClient($Client);

        if (Schema::hasColumn($Client->Intractable->getTable(), 'ServerClientIdentifier')) {
            Log::debug('IsServerClient: Client is a ServerClient: '.$Client);

            return true;
        }
        Log::debug('IsServerClient: Client is not a ServerClient: '.$Client);

        return false;
    }

    /**
     * Checks if this Client has the specified Role
     *
     * @param  string  $Role  The Role Identifier
     * @param  string|null  $Client  The Client Identifier
     * @return bool True if the Client has the Role, false if not
     */
    public function HasRole(string $Role, ?string $Client = ''): bool
    {
        $this->CheckClientVariable($Client);

        Log::debug('HasRole: Checking if Client: '.$Client.'has Role: '.$Role);

        return str(json_encode($this->GetRoles($Client)))->contains($Role);
    }

    /**
     * Returns all Roles associated with this Client
     *
     * @param  string|null  $Client  The Client Identifier
     * @return array All Roles associated with the Client
     */
    public function GetRoles(?string $Client = ''): array
    {
        $this->CheckClientVariable($Client);

        Log::debug('GetRoles: Getting Roles for Client: '.$Client);

        $Client = $this->GetClient($Client);

        $Roles = [];
        for ($RolesCountIndex = 0; $RolesCountIndex < count($Client->Roles); $RolesCountIndex++) {
            foreach ($Client->GetParentRoles(true, $Client->Roles[$RolesCountIndex]->RoleIdentifier) as $Role) {
                $Roles[] = $Role;
            }
        }

        return $Roles;
    }

    /**
     * Checks if a Client has access to all Shards
     *
     * @param  string|null  $Client  The Client Identifier
     * @return bool True if the Client has access to all Shards, false if not
     */
    public function HasAccessToAllShards(?string $Client = ''): bool
    {
        $this->CheckClientVariable($Client);

        Log::debug('HasAccessToAllShards: Checking if Client: '.$Client.' has access to all Shards');

        return str(json_encode($this->GetRoles($Client)))->contains('com.epicnova.shards.*');
    }

    /**
     * Checks if a Client has Access to an Shard.
     *
     * @param  string  $Shard  The Shard Identifier.
     * @param  string|null  $Client  The Client Identifier.
     * @return bool True if the Client has Access to the Shard, false otherwise.
     */
    public function HasAccessToShard(string $Shard, ?string $Client = ''): bool
    {
        $this->CheckClientVariable($Client);
        $this->ShardExists($Shard);
        $this->IsAPIShard($Shard);
        $this->IsShardActive($Shard);

        Log::debug('HasAccessToShard: Checking if Client: '.$Client.' has access to Shard: '.$Shard);

        // TODO Check if this works
        if ($this->HasAccessToAllShards($Client)) {
            Log::debug('HasAccessToShard: Client has access to all Shards');

            return true;
        }

        Log::debug('HasAccessToShard: Checking if Client: '.$Client.' has Access to Shard: '.$Shard);

        $Shards = [];

        foreach ($this->GetRoles($Client) as $Role) {
            $Shards[] = $this->GetShards($Role['RoleIdentifier']);
        }

        foreach ($Shards as $ShardElement) {
            for ($ShardIndex = 0; $ShardIndex < count($ShardElement); $ShardIndex++) {
                if ($ShardElement[$ShardIndex]['ShardIdentifier'] == $Shard) {
                    Log::debug('HasAccessToShard: Client has access to Shard: '.$Shard);

                    return true;
                } elseif ($ShardElement[$ShardIndex]['EnableParentShardInheritance']) {
                    Log::debug('HasAccessToShard: Checking if Client: '.$Client.' has Access to Shard: '.$Shard.' through inheritance');

                    $ShardObject = $this->GetShard($Shard);
                    if ($ShardObject->ParentShard && $ShardElement[$ShardIndex]['ShardIdentifier'] == $ShardObject->ParentShard && $this->IsChildOfShard($Shard, $this->GetShard($ShardObject->ParentShard)->ShardIdentifier)) {
                        Log::debug('HasAccessToShard: Client has access to Shard: '.$Shard.' through inheritance of Shard: '.$ShardObject->ParentShard);

                        return true;
                    }
                } else {
                    Log::debug('HasAccessToShard: Client does not have access to Shard: '.$Shard);
                }
            }
        }

        Log::debug('HasAccessToShard: Client does not have access to Shard: '.$Shard);

        $this->HandleError('errors.com.epicnova.shard.no_access', ['ShardIdentifier' => $Shard]);

        // The Syntax Analyzer does not recognize the HandleError functions calls die() so it thinks that this function returns null instead of a bool at the end.
    }

    /**
     * Checks if a Client has access to all Scopes
     *
     * @param  string|null  $Client  The Client Identifier
     * @return bool True if the Client has access to all Scopes, false if not
     */
    public function HasAccessToAllScopes(?string $Client = ''): bool
    {
        $this->CheckClientVariable($Client);

        Log::debug('HasAccessToAllScopes: Checking if Client: '.$Client.' has access to all Scopes');

        return str(json_encode($this->GetRoles($Client)))->contains('com.epicnova.scopes.*');
    }

    /**
     * Checks with the Role if the Client has access to the given Scope
     *
     * @param  string  $Scope  The scope to check if the Client has access to
     * @param  string|null  $Client  The Client Identifier
     * @return bool True if the Client has access to the given Scope
     */
    public function HasAccessToScope(string $Scope, ?string $Client = ''): bool
    {
        $this->CheckClientVariable($Client);
        $this->_ScopeExists($Scope);
        $this->IsScopeActive($Scope);

        Log::debug('HasAccessToScope: Checking if Client: '.$Client.' has access to Scope: '.$Scope);

        if ($this->HasAccessToAllScopes($Client)) {
            Log::debug('HasAccessToScope: Client has access to all Scopes');

            return true;
        }

        $Scopes = [];
        foreach ($this->GetRoles($Client) as $Role) {
            $Scopes[] = $this->GetScopes($Role['RoleIdentifier']);
        }

        if (str(json_encode($Scopes))->contains($Scope)) {
            Log::debug('HasAccessToScope: Client has access to Scope: '.$Scope);

            return true;
        } else {
            Log::debug('HasAccessToScope: Client does not have access to Scope: '.$Scope);
            $this->HandleError('errors.com.epicnova.scope.no_access', ['ScopeIdentifier' => $Scope]);
        }

        // The Syntax Analyzer does not recognize the HandleError functions calls die() so it thinks that this function returns null instead of a bool at the end.
    }

    /**
     * Checks if a Client has access to all Permissions
     *
     * @param  string|null  $Client  The Client Identifier
     * @return bool True if the Client has access to all Permissions, false if not
     */
    public function HasAccessToAllPermissions(?string $Client = ''): bool
    {
        $this->CheckClientVariable($Client);

        Log::debug('HasAccessToAllPermissions: Checking if Client: '.$Client.' has access to all Permissions');

        return str(json_encode($this->GetRoles($Client)))->contains('com.epicnova.permissions.*');
    }

    /**
     * Checks with the Role if the Client has access to the given Permission
     *
     * @param  string  $Permission  The permission to check
     * @param  string|null  $Client  The Client Identifier
     * @param  bool  $StopCodeExecution  If true, the code execution will stop if the Client does not have access to the Permission
     * @return bool True if the Client has access to the given Permission
     */
    public function HasAccessToPermission(string $Permission, ?string $Client = '', bool $StopCodeExecution = true): bool
    {
        $this->CheckClientVariable($Client);
        $this->PermissionExists($Permission);
        $this->IsPermissionActive($Permission);
        $this->GetPermission($Permission);

        Log::debug('HasAccessToPermission: Checking if Client: '.$Client.' has access to Permission: '.$Permission);

        // @TODO Check if this recursive thing actually works
        if ($this->HasHeadPermission($Permission)) {
            Log::debug('HasAccessToPermission: Checking if Client: '.$Client.' has access to Permission: '.$Permission.' through inheritance');

            if ($this->HasAccessToPermission($this->GetHeadPermission($Permission), $Client, false)) {
                Log::debug('HasAccessToPermission: Client has access to Permission: '.$Permission.' through inheritance');

                return true;
            }
        }

        if ($this->HasAccessToAllPermissions($Client)) {
            Log::debug('HasAccessToPermission: Client has access to all Permissions');

            return true;
        }

        $Permissions = [];

        foreach ($this->GetRoles($Client) as $Role) {
            for ($PermissionIndex = 0; $PermissionIndex < count($Role['Permissions']); $PermissionIndex++) {
                $Permissions[] = $Role['Permissions'][$PermissionIndex];
            }

            for ($PermissionIndex = 0; $PermissionIndex < count($Role['PermissionCollections']); $PermissionIndex++) {
                foreach ($this->GetPermissionCollection($Role['PermissionCollections'][$PermissionIndex]['PermissionCollectionIdentifier'])->Permissions as $Permission) {
                    $Permissions[] = $Permission;
                }
            }
            $ClientObject = $this->GetClient($Client);

            for ($PermissionIndex = 0; $PermissionIndex < count($ClientObject->ExtraPermissions); $PermissionIndex++) {
                $Permissions[] = $ClientObject->ExtraPermissions[$PermissionIndex];
            }

            for ($PermissionIndex = 0; $PermissionIndex < count($ClientObject->ExtraPermissionCollections); $PermissionIndex++) {
                foreach ($this->GetPermissionCollection($ClientObject->ExtraPermissionCollections[$PermissionIndex]['PermissionCollectionIdentifier'])->Permissions as $Permission) {
                    $Permissions[] = $Permission;
                }
            }
        }

        if (/* str(json_encode($Permissions))->contains($Permission) */ in_array($Permission, $Permissions)) {
            Log::debug('HasAccessToScope: Client has access to Permission: '.$Permission);

            return true;
        } elseif ($StopCodeExecution) {
            Log::debug('HasAccessToScope: Client does not have access to Permission: '.$Permission);
            $this->HandleError('errors.com.epicnova.permission.no_access', ['PermissionIdentifier' => $Permission]);
        }

        return false;
    }

    /**
     * Basically the same as HasAccessToPermission but this function accepts an array of permissions
     * instead of a single permission $Permissions The permissions to check if the Client has access to
     *
     * @param  array  $Permissions  The permission to check
     * @param  string|null  $Client  The Client Identifier
     * @return bool True if the Client has access to all the permissions in the array
     *
     * @see HasAccessToPermission
     */
    public function HasAccessToPermissions(array $Permissions, ?string $Client = ''): bool
    {
        $this->CheckClientVariable($Client);

        Log::debug('HasAccessToPermissions: Checking if Client: '.$Client.' has access to all given Permissions');

        $ReturnValue = [];

        foreach ($Permissions as $Permission) {
            $ReturnValue[] = $this->HasAccessToPermission($Permission, $Client);
        }

        if (count(array_unique($ReturnValue)) === 1) {
            return current($ReturnValue);
        }

        $this->HandleError('errors.com.epicnova.permission.no_access', ['PermissionIdentifier' => $Permissions]);

        // The Syntax Analyzer does not recognize the HandleError functions calls die() so it thinks that this function returns null instead of a bool at the end.
    }

    /**
     * Attaches a Role to a Client
     *
     * @param  string  $Role  The Role Identifier
     * @param  string|null  $Client  The Client Identifier
     */
    public function AttachRoleToClient(string $Role, ?string $Client = ''): void
    {
        $this->CheckClientVariable($Client);

        $Client = $this->GetClient($Client);
        $Client->Roles()->attach($Role);

        Log::info('AttachRoleToClient: Attached Role to Client: '.$Role.' to Client: '.$Client->ClientIdentifier);
    }

    /**
     * Detaches a Role from a Client
     *
     * @param  string  $Role  The Role Identifier
     * @param  string|null  $Client  The Client Identifier
     */
    public function DetachRoleFromClient(string $Role, ?string $Client = ''): void
    {
        $this->CheckClientVariable($Client);

        $Client = $this->GetClient($Client);
        $Client->Roles()->detach($Role);

        Log::info('DetachRoleFromClient: Detached Role from Client: '.$Role.' from Client: '.$Client->ClientIdentifier);
    }

    /**
     * Attaches a Permission to a Client
     *
     * @param  string  $Permission  The Permission Identifier
     * @param  string|null  $Client  The Client Identifier
     */
    public function AttachExtraPermissionToClient(string $Permission, ?string $Client = ''): void
    {
        $this->CheckClientVariable($Client);

        $Client = $this->GetClient($Client);
        $Client->ExtraPermissions()->attach($Permission);

        Log::debug('AttachExtraPermissionToClient: Attached Extra Permission to Client: '.$Permission.' to Client: '.$Client->ClientIdentifier);
    }

    /**
     * Detaches a Permission from a Client
     *
     * @param  string  $Permission  The Permission Identifier
     * @param  string|null  $Client  The Client Identifier
     */
    public function DetachExtraPermissionFromClient(string $Permission, ?string $Client = ''): void
    {
        $this->CheckClientVariable($Client);

        $Client = $this->GetClient($Client);
        $Client->ExtraPermissions()->detach($Permission);

        Log::debug('DetachExtraPermissionFromClient: Detached Extra Permission from Client: '.$Permission.' from Client: '.$Client->ClientIdentifier);
    }

    /**
     * Attaches a Permission Collection to a Client
     *
     * @param  string  $PermissionCollection  The Permission Collection Identifier
     * @param  string|null  $Client  The Client Identifier
     */
    public function AttachExtraPermissionCollectionToClient(string $PermissionCollection, ?string $Client = ''): void
    {
        $this->CheckClientVariable($Client);

        $Client = $this->GetClient($Client);
        $Client->ExtraPermissionCollections()->attach($PermissionCollection);

        Log::debug('AttachExtraPermissionCollectionToClient: Attached Extra Permission Collection to Client: '.$PermissionCollection.' to Client: '.$Client->ClientIdentifier);
    }

    /**
     * Detaches a Permission Collection from a Client
     *
     * @param  string  $PermissionCollection  The Permission Collection Identifier
     * @param  string|null  $Client  The Client Identifier
     */
    public function DetachExtraPermissionCollectionFromClient(string $PermissionCollection, ?string $Client = ''): void
    {
        $this->CheckClientVariable($Client);

        $Client = $this->GetClient($Client);
        $Client->ExtraPermissionCollections()->detach($PermissionCollection);

        Log::debug('DetachExtraPermissionCollectionFromClient: Detached Extra Permission Collection from Client: '.$PermissionCollection.' from Client: '.$Client->ClientIdentifier);
    }

    /**
     * Checks a Client Identifier and modifies it when needed
     *
     * @param  string  $Client  The reference to the Client Identifier to check
     */
    public function CheckClientVariable(string &$Client): void
    {
        Log::debug('CheckClientVariable: Checking if Client is empty');

        if (empty($Client)) {
            Log::debug('CheckClientVariable: Client Variable is not set, trying to set it to the ClientIdentifier of the owning Class');
            $Client = $this->ClientIdentifier;
        } else {
            Log::debug('CheckClientVariable: Client Variable is set');
        }
    }
}

<?php

namespace App\Traits\Relations;

use App\Models\Authentication\Authenticatable;
use App\Models\Authorisation\Permission;
use App\Models\Authorisation\PermissionCollection;
use App\Models\Authorisation\Role;
use App\Models\Authorisation\Scope;
use App\Models\Authorisation\Shard;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait RoleRelationsTrait
{
    public function parentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'ParentRoleIdentifier', 'RoleIdentifier');
    }

    public function childRoles(): HasMany
    {
        return $this->hasMany(Role::class, 'ParentRoleIdentifier', 'RoleIdentifier');
    }

    public function shards(): BelongsToMany
    {
        return $this->belongsToMany(
            Shard::class,
            'RoleShardReferences',
            'RoleIdentifier',
            'ShardIdentifier',
            'RoleIdentifier',
            'ShardIdentifier'
        );
    }

    public function scopes(): BelongsToMany
    {
        return $this->belongsToMany(
            Scope::class,
            'RoleScopeReferences',
            'RoleIdentifier',
            'ScopeIdentifier',
            'RoleIdentifier',
            'ScopeIdentifier'
        );
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'RolePermissionReferences',
            'RoleIdentifier',
            'PermissionIdentifier',
            'RoleIdentifier',
            'PermissionIdentifier'
        );
    }

    public function permissionCollections(): BelongsToMany
    {
        return $this->belongsToMany(
            PermissionCollection::class,
            'RolePermissionCollectionReferences',
            'RoleIdentifier',
            'PermissionCollectionIdentifier',
            'RoleIdentifier',
            'PermissionCollectionIdentifier'
        );
    }

    public function authenticatables(): BelongsToMany
    {
        return $this->belongsToMany(
            Authenticatable::class,
            'AuthenticatableRoleReferences',
            'RoleIdentifier',
            'AuthenticatableIdentifier',
            'RoleIdentifier',
            'AuthenticatableIdentifier'
        );
    }
}

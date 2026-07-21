<?php

namespace App\Traits\Deprecated\Relations;

use App\Models\Authorisation\Permission;
use App\Models\Authorisation\PermissionCollection;
use App\Models\Authorisation\Role;
use App\Models\Authorisation\RoleIgnoredError;
use App\Models\Authorisation\Scope;
use App\Models\Authorisation\Shard;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait RoleRelationsTrait
{
    /**
     * Role belongs to Parent Role.
     */
    public function ParentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'ParentRoleIdentifier', 'RoleIdentifier');
    }

    /**
     * Returns the ParentRole of this Role for array serialization in the Laravel Framework.
     *
     * @return array|null
     */
    public function getParentRoleAttribute(): ?Role
    {
        return $this->ParentRole()->first();
    }

    /**
     * Role belongs to many Shards.
     */
    public function Shards(): BelongsToMany
    {
        return $this->belongsToMany(Shard::class, 'RoleShardReferences', 'RoleIdentifier', 'ShardIdentifier');
    }

    /**
     * Returns the Shards of this Role for array serialization in the Laravel Framework.
     *
     * @return array|null
     */
    public function getShardsAttribute()
    {
        return $this->Shards()->get();
    }

    /**
     * Role belongs to many Scopes.
     */
    public function Scopes(): BelongsToMany
    {
        return $this->belongsToMany(Scope::class, 'RoleScopeReferences', 'RoleIdentifier', 'ScopeIdentifier');
    }

    /**
     * Returns the Scopes of this Role for array serialization in the Laravel Framework.
     *
     * @return array|null
     */
    public function getScopesAttribute()
    {
        return $this->Scopes()->get();
    }

    /**
     * Role belongs to many Permissions.
     */
    public function Permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'RolePermissionReferences', 'RoleIdentifier', 'PermissionIdentifier');
    }

    /**
     * Returns the Permissions of this Role for array serialization in the Laravel Framework.
     *
     * @return array|null
     */
    public function getPermissionsAttribute()
    {
        return $this->Permissions()->get();
    }

    /**
     * Role has many Permission Collections.
     */
    public function PermissionCollections(): BelongsToMany
    {
        return $this->belongsToMany(PermissionCollection::class, 'RolePermissionCollectionReferences', 'RoleIdentifier', 'PermissionCollectionIdentifier');
    }

    /**
     * Returns the Permission Collections of this Role for array serialization in the Laravel Framework.
     *
     * @return array|null
     */
    public function getPermissionCollectionsAttribute()
    {
        return $this->PermissionCollections()->get();
    }

    /**
     * Role has many Ignored Errors.
     */
    public function RoleIgnoredErrors(): HasMany
    {
        return $this->hasMany(RoleIgnoredError::class, 'RoleIdentifier', 'RoleIdentifier');
    }

    /**
     * Returns the Ignored Errors of this Role for array serialization in the Laravel Framework.
     *
     * @return array|null
     */
    public function getRoleIgnoredErrorsAttribute()
    {
        return $this->RoleIgnoredErrors()->get();
    }
}

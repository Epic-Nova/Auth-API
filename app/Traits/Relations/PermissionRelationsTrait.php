<?php

namespace App\Traits\Relations;

use App\Models\Authorisation\Permission;
use App\Models\Authorisation\PermissionCollection;
use App\Models\Authorisation\Role;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait PermissionRelationsTrait
{
    public function parentPermission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'ParentPermission', 'PermissionIdentifier');
    }

    public function childPermissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'ParentPermission', 'PermissionIdentifier');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'RolePermissionReferences',
            'PermissionIdentifier',
            'RoleIdentifier',
            'PermissionIdentifier',
            'RoleIdentifier'
        );
    }

    public function permissionCollections(): BelongsToMany
    {
        return $this->belongsToMany(
            PermissionCollection::class,
            'PermissionCollectionPermissionReferences',
            'PermissionIdentifier',
            'PermissionCollectionIdentifier',
            'PermissionIdentifier',
            'PermissionCollectionIdentifier'
        );
    }
}

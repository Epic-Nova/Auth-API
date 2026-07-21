<?php

namespace App\Traits\Relations;

use App\Models\Authentication\Authenticatable;
use App\Models\Authorisation\Permission;
use App\Models\Authorisation\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait PermissionCollectionRelationsTrait
{
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'PermissionCollectionPermissionReferences',
            'PermissionCollectionIdentifier',
            'PermissionIdentifier',
            'PermissionCollectionIdentifier',
            'PermissionIdentifier'
        );
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'RolePermissionCollectionReferences',
            'PermissionCollectionIdentifier',
            'RoleIdentifier',
            'PermissionCollectionIdentifier',
            'RoleIdentifier'
        );
    }

    public function authenticatables(): BelongsToMany
    {
        return $this->belongsToMany(
            Authenticatable::class,
            'AuthenticatablePermissionCollectionReferences',
            'PermissionCollectionIdentifier',
            'AuthenticatableIdentifier',
            'PermissionCollectionIdentifier',
            'AuthenticatableIdentifier'
        );
    }
}

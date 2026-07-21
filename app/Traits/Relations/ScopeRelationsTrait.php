<?php

namespace App\Traits\Relations;

use App\Models\Authorisation\Permission;
use App\Models\Authorisation\Role;
use App\Models\Authorisation\Shard;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait ScopeRelationsTrait
{
    public function shard(): BelongsTo
    {
        return $this->belongsTo(Shard::class, 'ScopeShard', 'ShardIdentifier');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'PermissionScope', 'ScopeIdentifier');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'RoleScopeReferences',
            'ScopeIdentifier',
            'RoleIdentifier',
            'ScopeIdentifier',
            'RoleIdentifier'
        );
    }
}

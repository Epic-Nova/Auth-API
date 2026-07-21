<?php

namespace App\Traits\Relations;

use App\Models\Authorisation\Role;
use App\Models\Authorisation\Scope;
use App\Models\Authorisation\Shard;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait ShardRelationsTrait
{
    public function parentShard(): BelongsTo
    {
        return $this->belongsTo(Shard::class, 'ParentShardIdentifier', 'ShardIdentifier');
    }

    public function childShards(): HasMany
    {
        return $this->hasMany(Shard::class, 'ParentShardIdentifier', 'ShardIdentifier');
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(Scope::class, 'ScopeShard', 'ShardIdentifier');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'RoleShardReferences',
            'ShardIdentifier',
            'RoleIdentifier',
            'ShardIdentifier',
            'RoleIdentifier'
        );
    }
}

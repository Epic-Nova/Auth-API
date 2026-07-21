<?php

namespace App\Traits\Deprecated\Relations;

use App\Models\Authorisation\Shard;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait ShardRelationsTrait
{
    /**
     * Shard has Many Child Shards
     */
    public function ChildShards(): HasMany
    {
        return $this->hasMany(Shard::class, 'ParentShardIdentifier', 'ShardIdentifier');
    }

    /**
     * Returns the Child Shards of this Shard for array serialization in the Laravel Framework.
     */
    public function getChildShardsAttribute(): ?array
    {
        return $this->ChildShards()->get();
    }

    /**
     * Shard belongs to one Parent Shard
     */
    public function ParentShard(): BelongsTo
    {
        return $this->belongsTo(Shard::class, 'ParentShardIdentifier', 'ShardIdentifier');
    }

    /**
     * Returns the Parent Shard of this Shard for array serialization in the Laravel Framework.
     *
     * @return Shard|null
     */
    public function getParentShardAttribute(): ?string
    {
        return $this->ParentShard()->exists() ? $this->ParentShard()->first()->ShardIdentifier : null;
    }
}

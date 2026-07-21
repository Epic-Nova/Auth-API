<?php

namespace App\Traits\Deprecated\Relations;

use App\Models\Authorisation\Shard;
use App\Models\Client;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait ServerClientRelationsTrait
{
    /**
     * ServerClient belongs to Client.
     */
    public function Client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'ServerClientIdentifier', 'ServerClientIdentifier');
    }

    /**
     * Returns the Client of this ServerClient for array serialization in the Laravel Framework.
     */
    public function getClientAttribute(): ?Client
    {
        return $this->Client()->first()->ServerClientIdentifier;
    }

    /**
     * ServerClient Has One Shard.
     */
    public function Shard(): HasOne
    {
        return $this->hasOne(Shard::class, 'ShardIdentifier', 'AssignedShard');
    }

    /**
     * Returns the Shard of this ServerClient for array serialization in the Laravel Framework.
     */
    public function getShardAttribute(): ?Shard
    {
        return $this->Shard()->first();
    }
}

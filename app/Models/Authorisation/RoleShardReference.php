<?php

namespace App\Models\Authorisation;

use Database\Factories\RoleShardReferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleShardReference extends Model
{
    /** @use HasFactory<RoleShardReferenceFactory> */
    use HasFactory;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'RoleShardReferences';

    protected $primaryKey = 'RoleShardReferenceIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'CreatedAt' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'RoleIdentifier', 'RoleIdentifier');
    }

    public function shard(): BelongsTo
    {
        return $this->belongsTo(Shard::class, 'ShardIdentifier', 'ShardIdentifier');
    }
}

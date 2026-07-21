<?php

namespace App\Models\Authorisation;

use App\Traits\Relations\ShardRelationsTrait;
use Database\Factories\ShardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shard extends Model
{
    /** @use HasFactory<ShardFactory> */
    use HasFactory;

    use ShardRelationsTrait;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'Shards';

    protected $primaryKey = 'ShardIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bEnableParentShardInheritance' => 'boolean',
            'bApplicationProgrammingInterface' => 'boolean',
            'bIsActive' => 'boolean',
            'CreatedAt' => 'datetime',
        ];
    }
}

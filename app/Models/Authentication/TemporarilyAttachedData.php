<?php

namespace App\Models\Authentication;

use App\Traits\Relations\TemporarilyAttachedDataRelationsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporarilyAttachedData extends Model
{
    use HasFactory;
    use TemporarilyAttachedDataRelationsTrait;

    protected $connection = 'AuthConnection';

    protected $table = 'TemporarilyAttachedData';

    protected $primaryKey = 'TemporarilyAttachedDataIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'ExpiresAt' => 'datetime',
            'CreatedAt' => 'datetime',
        ];
    }
}

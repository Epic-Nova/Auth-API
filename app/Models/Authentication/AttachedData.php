<?php

namespace App\Models\Authentication;

use App\Traits\Relations\AttachedDataRelationsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttachedData extends Model
{
    use AttachedDataRelationsTrait;
    use HasFactory;

    protected $connection = 'AuthConnection';

    protected $table = 'AttachedData';

    protected $primaryKey = 'AttachedDataIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public const string CREATED_AT = 'CreatedAt';

    public const string UPDATED_AT = 'UpdatedAt';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bIsPrimary' => 'boolean',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
        ];
    }
}

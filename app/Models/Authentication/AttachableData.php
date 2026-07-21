<?php

namespace App\Models\Authentication;

use App\Traits\Relations\AttachableDataRelationsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttachableData extends Model
{
    use AttachableDataRelationsTrait;
    use HasFactory;

    protected $connection = 'AuthConnection';

    protected $table = 'AttachableData';

    protected $primaryKey = 'AttachableDataName';

    protected $keyType = 'string';

    public $incrementing = false;

    public const string CREATED_AT = 'CreatedAt';

    public const string UPDATED_AT = 'UpdatedAt';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bIsUnique' => 'boolean',
            'bCanHaveMultiple' => 'boolean',
            'bInvertSafeList' => 'boolean',
            'bUseSafeList' => 'boolean',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
        ];
    }
}

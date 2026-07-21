<?php

namespace App\Models\Authentication;

use App\Traits\Relations\AuthenticatableRelationsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Authenticatable extends Model
{
    use AuthenticatableRelationsTrait;
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'AuthConnection';

    protected $table = 'Authenticatable';

    protected $primaryKey = 'AuthenticatableIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public const string CREATED_AT = 'CreatedAt';

    public const string UPDATED_AT = 'UpdatedAt';

    public const DELETED_AT = 'DeletedAt';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
            'DeletedAt' => 'datetime',
        ];
    }
}

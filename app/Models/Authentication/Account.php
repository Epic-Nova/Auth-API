<?php

namespace App\Models\Authentication;

use App\Traits\Relations\AccountRelationsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use AccountRelationsTrait;
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'AuthConnection';

    protected $table = 'Accounts';

    protected $primaryKey = 'AccountIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public const string CREATED_AT = 'CreatedAt';

    public const string UPDATED_AT = 'UpdatedAt';

    public const DELETED_AT = 'DeletedAt';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'ClosedAt' => 'datetime',
            'PurgeAfter' => 'datetime',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
            'DeletedAt' => 'datetime',
        ];
    }
}

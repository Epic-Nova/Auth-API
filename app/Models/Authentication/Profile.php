<?php

namespace App\Models\Authentication;

use App\Traits\Relations\ProfileRelationsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory;
    use ProfileRelationsTrait;
    use SoftDeletes;

    protected $connection = 'AuthConnection';

    protected $table = 'Profiles';

    protected $primaryKey = 'ProfileIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public const string CREATED_AT = 'CreatedAt';

    public const string UPDATED_AT = 'UpdatedAt';

    public const DELETED_AT = 'DeletedAt';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bIsProfilePublic' => 'boolean',
            'bIsProfileSearchable' => 'boolean',
            'bIsProfileVerified' => 'boolean',
            'bIsProfileDeletable' => 'boolean',
            'LastStateChange' => 'datetime',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
            'DeletedAt' => 'datetime',
        ];
    }
}

<?php

namespace App\Models\Authentication;

use App\Traits\Relations\AuthenticationProviderRelationsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthenticationProvider extends Model
{
    use AuthenticationProviderRelationsTrait;
    use HasFactory;

    protected $connection = 'AuthConnection';

    protected $table = 'AuthenticationProviders';

    protected $primaryKey = 'AuthenticationProviderIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public const string CREATED_AT = 'CreatedAt';

    public const string UPDATED_AT = 'UpdatedAt';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bCanUserLoginWithProvider' => 'boolean',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
        ];
    }
}

<?php

namespace App\Models\Authentication;

use Illuminate\Database\Eloquent\Model;

class OAuthClient extends Model
{
    protected $connection = 'AuthConnection';

    protected $table = 'OAuthClients';

    protected $primaryKey = 'OAuthClientIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public const string CREATED_AT = 'CreatedAt';

    public const string UPDATED_AT = 'UpdatedAt';

    protected $guarded = [];

    protected $hidden = [
        'ClientSecretHash',
    ];

    protected function casts(): array
    {
        return [
            'AllowedScopes' => 'array',
            'AllowedAudiences' => 'array',
            'IsActive' => 'boolean',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
        ];
    }
}

<?php

namespace App\Models\Authentication;

use Illuminate\Database\Eloquent\Model;

class OAuthDeviceAuthorization extends Model
{
    protected $connection = 'AuthConnection';

    protected $table = 'OAuthDeviceAuthorizations';

    protected $primaryKey = 'OAuthDeviceAuthorizationIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public const string CREATED_AT = 'CreatedAt';

    public const string UPDATED_AT = 'UpdatedAt';

    protected $guarded = [];

    protected $hidden = [
        'DeviceCodeHash',
        'UserCodeHash',
    ];

    protected function casts(): array
    {
        return [
            'RequestedScopes' => 'array',
            'RequestedAudiences' => 'array',
            'ExpiresAt' => 'datetime',
            'ApprovedAt' => 'datetime',
            'ConsumedAt' => 'datetime',
            'CreatedAt' => 'datetime',
            'UpdatedAt' => 'datetime',
        ];
    }
}

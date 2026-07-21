<?php

namespace App\Models\Authorisation;

use App\Traits\Relations\PermissionRelationsTrait;
use Database\Factories\PermissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /** @use HasFactory<PermissionFactory> */
    use HasFactory;

    use PermissionRelationsTrait;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'Permissions';

    protected $primaryKey = 'PermissionIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bIsActive' => 'boolean',
            'CreatedAt' => 'datetime',
        ];
    }
}

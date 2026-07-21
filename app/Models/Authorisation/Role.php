<?php

namespace App\Models\Authorisation;

use App\Traits\Relations\RoleRelationsTrait;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    use RoleRelationsTrait;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'Roles';

    protected $primaryKey = 'RoleIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'CreatedAt' => 'datetime',
        ];
    }
}

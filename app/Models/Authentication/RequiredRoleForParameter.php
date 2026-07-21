<?php

namespace App\Models\Authentication;

use App\Models\Authorisation\Role;
use Database\Factories\RequiredRoleForParameterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequiredRoleForParameter extends Model
{
    /** @use HasFactory<RequiredRoleForParameterFactory> */
    use HasFactory;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'RequiredRoleForParameters';

    protected $primaryKey = 'ParameterReference';

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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'RoleIdentifier', 'RoleIdentifier');
    }
}

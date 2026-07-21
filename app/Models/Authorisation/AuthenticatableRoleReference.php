<?php

namespace App\Models\Authorisation;

use Database\Factories\AuthenticatableRoleReferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthenticatableRoleReference extends Model
{
    /** @use HasFactory<AuthenticatableRoleReferenceFactory> */
    use HasFactory;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'AuthenticatableRoleReferences';

    protected $primaryKey = 'AuthenticatableRoleReferenceIdentifier';

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

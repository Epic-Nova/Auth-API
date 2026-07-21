<?php

namespace App\Models\Authorisation;

use Database\Factories\RoleScopeReferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleScopeReference extends Model
{
    /** @use HasFactory<RoleScopeReferenceFactory> */
    use HasFactory;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'RoleScopeReferences';

    protected $primaryKey = 'RoleScopeReferenceIdentifier';

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

    public function scope(): BelongsTo
    {
        return $this->belongsTo(Scope::class, 'ScopeIdentifier', 'ScopeIdentifier');
    }
}

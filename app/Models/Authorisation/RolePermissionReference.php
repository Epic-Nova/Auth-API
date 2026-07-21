<?php

namespace App\Models\Authorisation;

use Database\Factories\RolePermissionReferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermissionReference extends Model
{
    /** @use HasFactory<RolePermissionReferenceFactory> */
    use HasFactory;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'RolePermissionReferences';

    protected $primaryKey = 'RolePermissionReferenceIdentifier';

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

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'PermissionIdentifier', 'PermissionIdentifier');
    }
}

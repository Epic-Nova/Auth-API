<?php

namespace App\Models\Authorisation;

use Database\Factories\RolePermissionCollectionReferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermissionCollectionReference extends Model
{
    /** @use HasFactory<RolePermissionCollectionReferenceFactory> */
    use HasFactory;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'RolePermissionCollectionReferences';

    protected $primaryKey = 'RolePermissionCollectionReferenceIdentifier';

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

    public function permissionCollection(): BelongsTo
    {
        return $this->belongsTo(
            PermissionCollection::class,
            'PermissionCollectionIdentifier',
            'PermissionCollectionIdentifier'
        );
    }
}

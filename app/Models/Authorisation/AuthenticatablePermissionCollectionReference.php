<?php

namespace App\Models\Authorisation;

use Database\Factories\AuthenticatablePermissionCollectionReferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthenticatablePermissionCollectionReference extends Model
{
    /** @use HasFactory<AuthenticatablePermissionCollectionReferenceFactory> */
    use HasFactory;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'AuthenticatablePermissionCollectionReferences';

    protected $primaryKey = 'AuthenticatablePermissionCollectionReferenceIdentifier';

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

    public function permissionCollection(): BelongsTo
    {
        return $this->belongsTo(
            PermissionCollection::class,
            'PermissionCollectionIdentifier',
            'PermissionCollectionIdentifier'
        );
    }
}

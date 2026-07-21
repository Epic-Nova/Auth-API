<?php

namespace App\Models\Authorisation;

use Database\Factories\PermissionCollectionPermissionReferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionCollectionPermissionReference extends Model
{
    /** @use HasFactory<PermissionCollectionPermissionReferenceFactory> */
    use HasFactory;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'PermissionCollectionPermissionReferences';

    protected $primaryKey = 'PermissionCollectionPermissionReferenceIdentifier';

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

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'PermissionIdentifier', 'PermissionIdentifier');
    }
}

<?php

namespace App\Models\Authorisation;

use Database\Factories\AuthenticatablePermissionReferenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthenticatablePermissionReference extends Model
{
    /** @use HasFactory<AuthenticatablePermissionReferenceFactory> */
    use HasFactory;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'AuthenticatablePermissionReferences';

    protected $primaryKey = 'AuthenticatablePermissionReferenceIdentifier';

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

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'PermissionIdentifier', 'PermissionIdentifier');
    }
}

<?php

namespace App\Models\Authorisation;

use App\Traits\Relations\PermissionCollectionRelationsTrait;
use Database\Factories\PermissionCollectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionCollection extends Model
{
    /** @use HasFactory<PermissionCollectionFactory> */
    use HasFactory;

    use PermissionCollectionRelationsTrait;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'PermissionCollections';

    protected $primaryKey = 'PermissionCollectionIdentifier';

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

<?php

namespace App\Models\Authorisation;

use Database\Factories\RoleIgnoredErrorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleIgnoredError extends Model
{
    /** @use HasFactory<RoleIgnoredErrorFactory> */
    use HasFactory;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'RoleIgnoredErrors';

    protected $primaryKey = 'RoleIgnoredErrorIdentifier';

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

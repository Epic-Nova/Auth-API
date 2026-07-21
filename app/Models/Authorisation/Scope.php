<?php

namespace App\Models\Authorisation;

use App\Traits\Relations\ScopeRelationsTrait;
use Database\Factories\ScopeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scope extends Model
{
    /** @use HasFactory<ScopeFactory> */
    use HasFactory;

    use ScopeRelationsTrait;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'Scopes';

    protected $primaryKey = 'ScopeIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bIsActive' => 'boolean',
            'CreatedAt' => 'datetime',
        ];
    }
}

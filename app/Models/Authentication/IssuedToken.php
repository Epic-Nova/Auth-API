<?php

namespace App\Models\Authentication;

use App\Traits\Relations\IssuedTokenRelationsTrait;
use Database\Factories\IssuedTokenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssuedToken extends Model
{
    /** @use HasFactory<IssuedTokenFactory> */
    use HasFactory;

    use IssuedTokenRelationsTrait;

    protected $connection = 'AuthConnection';

    protected $table = 'IssuedTokens';

    protected $primaryKey = 'IssuedTokenJTI';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'TokenExpiry' => 'datetime',
            'IssuedAt' => 'datetime',
        ];
    }
}

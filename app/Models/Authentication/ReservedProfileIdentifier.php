<?php

namespace App\Models\Authentication;

use Database\Factories\ReservedProfileIdentifierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservedProfileIdentifier extends Model
{
    /** @use HasFactory<ReservedProfileIdentifierFactory> */
    use HasFactory;

    protected $connection = 'AuthConnection';

    protected $table = 'ReservedProfileIdentifiers';

    protected $primaryKey = 'ReservedProfileIdentifierIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bCanReservedAccountShareProfileIdentifier' => 'boolean',
            'ReservedUntil' => 'date',
            'CreatedAt' => 'datetime',
        ];
    }
}

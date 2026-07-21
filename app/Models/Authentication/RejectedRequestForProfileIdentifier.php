<?php

namespace App\Models\Authentication;

use Database\Factories\RejectedRequestForProfileIdentifierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RejectedRequestForProfileIdentifier extends Model
{
    /** @use HasFactory<RejectedRequestForProfileIdentifierFactory> */
    use HasFactory;

    protected $connection = 'AuthConnection';

    protected $table = 'RejectedRequestForProfileIdentifiers';

    protected $primaryKey = 'RejectedRequestIdentifier';

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

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'RejectedAccountIdentifier', 'AccountIdentifier');
    }

    public function reservedProfileIdentifier(): BelongsTo
    {
        return $this->belongsTo(
            ReservedProfileIdentifier::class,
            'RejectedOnProfileIdentifier',
            'ReservedProfileIdentifierIdentifier'
        );
    }
}

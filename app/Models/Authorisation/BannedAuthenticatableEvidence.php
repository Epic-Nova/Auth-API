<?php

namespace App\Models\Authorisation;

use Database\Factories\BannedAuthenticatableEvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannedAuthenticatableEvidence extends Model
{
    /** @use HasFactory<BannedAuthenticatableEvidenceFactory> */
    use HasFactory;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'BannedAuthenticatableEvidences';

    protected $primaryKey = 'BannedAuthenticatableEvidenceIdentifier';

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

    public function ban(): BelongsTo
    {
        return $this->belongsTo(BannedAuthenticatable::class, 'BanIdentifier', 'BanIdentifier');
    }
}

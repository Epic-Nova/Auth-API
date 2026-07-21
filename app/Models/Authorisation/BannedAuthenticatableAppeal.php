<?php

namespace App\Models\Authorisation;

use Database\Factories\BannedAuthenticatableAppealFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannedAuthenticatableAppeal extends Model
{
    /** @use HasFactory<BannedAuthenticatableAppealFactory> */
    use HasFactory;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'BannedAuthenticatableAppeals';

    protected $primaryKey = 'BannedAuthenticatableAppealIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'BanAppealDate' => 'datetime',
            'BanAppealDecisionDate' => 'datetime',
            'CreatedAt' => 'datetime',
        ];
    }

    public function ban(): BelongsTo
    {
        return $this->belongsTo(BannedAuthenticatable::class, 'BanIdentifier', 'BanIdentifier');
    }
}

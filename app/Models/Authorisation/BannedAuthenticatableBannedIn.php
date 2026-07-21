<?php

namespace App\Models\Authorisation;

use Database\Factories\BannedAuthenticatableBannedInFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BannedAuthenticatableBannedIn extends Model
{
    /** @use HasFactory<BannedAuthenticatableBannedInFactory> */
    use HasFactory;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'BannedAuthenticatableBannedIn';

    protected $primaryKey = 'BannedAuthenticatableBannedInIdentifier';

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

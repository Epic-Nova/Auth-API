<?php

namespace App\Models\Authorisation;

use Database\Factories\BannedAuthenticatableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BannedAuthenticatable extends Model
{
    /** @use HasFactory<BannedAuthenticatableFactory> */
    use HasFactory;

    protected $connection = 'RestrictionsConnection';

    protected $table = 'BannedAuthenticatable';

    protected $primaryKey = 'BanIdentifier';

    protected $keyType = 'string';

    public $incrementing = false;

    public const string UPDATED_AT = 'UpdatedAt';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'BannedAt' => 'datetime',
            'BanExpires' => 'datetime',
            'UpdatedAt' => 'datetime',
        ];
    }

    public function bannedIn(): HasMany
    {
        return $this->hasMany(BannedAuthenticatableBannedIn::class, 'BanIdentifier', 'BanIdentifier');
    }

    public function appeals(): HasMany
    {
        return $this->hasMany(BannedAuthenticatableAppeal::class, 'BanIdentifier', 'BanIdentifier');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(BannedAuthenticatableEvidence::class, 'BanIdentifier', 'BanIdentifier');
    }
}

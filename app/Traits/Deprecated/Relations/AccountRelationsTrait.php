<?php

namespace App\Traits\Deprecated\Relations;

use App\Models\AccountPasswordHistory;
use App\Models\Authentication\Authenticatable;
use App\Models\Authentication\Profile;
use App\Models\Client;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait AccountRelationsTrait
{
    /**
     * Account belongs to one Client
     */
    public function Client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'AccountIdentifier', 'ClientIdentifier');
    }

    /**
     * Returns the Client of this Account for array serialization in the Laravel Framework.
     */
    public function getClientAttribute(): ?string
    {
        return $this->Client()->first()->ClientIdentifier;
    }

    /**
     * Account has one Profile
     */
    public function Profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'ProfileIdentifier', 'ProfileIdentifier');
    }

    /**
     * Returns the Profile of this Account for array serialization in the Laravel Framework.
     *
     * @return Profile|null
     */
    public function getProfileAttribute(): ?string
    {
        return $this->Profile()->exists() ? $this->Profile()->first()->ProfileIdentifier : null;
    }

    /**
     * Account has one Password
     */
    public function CurrentPassword(): BelongsTo
    {
        return $this->belongsTo(AccountPasswordHistory::class, 'CurrentPassword', 'Password');
    }

    /**
     * Returns the Password of this account for array serialization in the Laravel Framework.
     *
     * @return AccountPasswordHistory|null
     */
    public function getCurrentPasswordAttribute(): ?string
    {
        return $this->CurrentPassword()->exists() ? $this->CurrentPassword()->first()->Password : null;
    }

    public function Authenticatable(): Authenticatable
    {
        return $this->belongsTo(Authenticatable::class, 'AccountIdentifier', 'AuthenticatableIdentifier')->first();
    }

    public function getAuthenticatableAttribute(): ?string
    {
        return $this->Authenticatable()->exists() ? $this->Authenticatable()->first()->AuthenticatableIdentifier : null;
    }
}

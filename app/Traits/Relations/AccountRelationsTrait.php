<?php

namespace App\Traits\Relations;

use App\Models\Authentication\AccountNote;
use App\Models\Authentication\AttachedAuthenticationProvider;
use App\Models\Authentication\AttachedData;
use App\Models\Authentication\Authenticatable;
use App\Models\Authentication\AuthenticationProvider;
use App\Models\Authentication\Profile;
use App\Models\Authentication\TemporarilyAttachedData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait AccountRelationsTrait
{
    /** --- CORE RELATIONSHIPS --- */
    public function authenticatable(): BelongsTo
    {
        return $this->belongsTo(Authenticatable::class, 'AccountIdentifier', 'AuthenticatableIdentifier');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'AccountIdentifier', 'AccountIdentifier');
    }

    /**
     * Provider attachment rows for this Account.
     */
    public function attachedAuthenticationProviders(): HasMany
    {
        return $this->hasMany(
            AttachedAuthenticationProvider::class,
            'AuthenticatableIdentifier',
            'AccountIdentifier'
        );
    }

    /**
     * Authentication providers available to this Account.
     */
    public function authenticationProviders(): BelongsToMany
    {
        return $this->belongsToMany(
            AuthenticationProvider::class,
            'AttachedAuthenticationProviders',
            'AuthenticatableIdentifier',
            'AuthenticationProviderIdentifier',
            'AccountIdentifier',
            'AuthenticationProviderIdentifier'
        )->withPivot(['bIsPrimary', 'ProviderData', 'ProviderState', 'CreatedAt']);
    }

    /**
     * Metadata attached to the Account.
     */
    public function attachedData(): MorphMany
    {
        return $this->morphMany(AttachedData::class, 'attachedOn', 'AttachedOn', 'AttachedOnIdentifier');
    }

    /**
     * Staged metadata attached to the Account.
     */
    public function temporarilyAttachedData(): MorphMany
    {
        return $this->morphMany(
            TemporarilyAttachedData::class,
            'attachedOn',
            'AttachedOn',
            'AttachedOnIdentifier'
        );
    }

    public function notes(): HasMany
    {
        return $this->hasMany(AccountNote::class, 'AccountIdentifier', 'AccountIdentifier');
    }

    /** --- HELPERS & ACCESSORS --- */
    public function getPrimaryAuthenticationProviderAttribute(): ?AuthenticationProvider
    {
        return $this->authenticationProviders
            ->firstWhere('pivot.bIsPrimary', true);
    }

    public function getProfileIdentifierAttribute(): ?string
    {
        return $this->profile?->ProfileIdentifier;
    }
}

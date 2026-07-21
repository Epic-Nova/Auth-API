<?php

namespace App\Traits\Relations;

use App\Models\Authentication\AttachedAuthenticationProvider;
use App\Models\Authentication\AuthenticationProviderParameter;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait AuthenticationProviderRelationsTrait
{
    public function parameters(): HasMany
    {
        return $this->hasMany(
            AuthenticationProviderParameter::class,
            'AuthenticationProviderIdentifier',
            'AuthenticationProviderIdentifier'
        );
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(
            AttachedAuthenticationProvider::class,
            'AuthenticationProviderIdentifier',
            'AuthenticationProviderIdentifier'
        );
    }
}

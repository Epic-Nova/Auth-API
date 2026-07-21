<?php

namespace App\Traits\Relations;

use App\Models\Authentication\Account;
use App\Models\Authentication\ApprovalEntityData;
use App\Models\Authentication\AttachedAuthenticationProvider;
use App\Models\Authentication\AttachedData;
use App\Models\Authentication\AuthenticatableUpdateLog;
use App\Models\Authentication\AuthenticationProvider;
use App\Models\Authentication\IssuedToken;
use App\Models\Authentication\TemporarilyAttachedData;
use App\Models\Authorisation\Permission;
use App\Models\Authorisation\PermissionCollection;
use App\Models\Authorisation\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait AuthenticatableRelationsTrait
{
    /**
     * Link to the Account details.
     */
    public function account(): HasOne
    {
        return $this->hasOne(Account::class, 'AccountIdentifier', 'AuthenticatableIdentifier');
    }

    /**
     * Provider attachment rows for this Authenticatable.
     */
    public function attachedAuthenticationProviders(): HasMany
    {
        return $this->hasMany(
            AttachedAuthenticationProvider::class,
            'AuthenticatableIdentifier',
            'AuthenticatableIdentifier'
        );
    }

    /**
     * Authentication providers available to this Authenticatable.
     */
    public function authenticationProviders(): BelongsToMany
    {
        return $this->belongsToMany(
            AuthenticationProvider::class,
            'AttachedAuthenticationProviders',
            'AuthenticatableIdentifier',
            'AuthenticationProviderIdentifier',
            'AuthenticatableIdentifier',
            'AuthenticationProviderIdentifier'
        )->withPivot(['bIsPrimary', 'ProviderData', 'ProviderState', 'CreatedAt']);
    }

    /**
     * Metadata attached to the Authenticatable.
     */
    public function attachedData(): MorphMany
    {
        return $this->morphMany(AttachedData::class, 'attachedOn', 'AttachedOn', 'AttachedOnIdentifier');
    }

    /**
     * Staged metadata attached to the Authenticatable.
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

    /**
     * Approval entity data for this Authenticatable.
     */
    public function approvalEntityData(): HasMany
    {
        return $this->hasMany(ApprovalEntityData::class, 'AuthenticatableIdentifier', 'AuthenticatableIdentifier');
    }

    /**
     * Issued tokens for this Authenticatable.
     */
    public function issuedTokens(): HasMany
    {
        return $this->hasMany(IssuedToken::class, 'IssuedToAuthenticatable', 'AuthenticatableIdentifier');
    }

    /**
     * Audit logs for this Authenticatable.
     */
    public function updateLogs(): HasMany
    {
        return $this->hasMany(AuthenticatableUpdateLog::class, 'AuthenticatableIdentifier', 'AuthenticatableIdentifier');
    }

    /**
     * Roles assigned to this Authenticatable via the restrictions database.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'AuthenticatableRoleReferences',
            'AuthenticatableIdentifier',
            'RoleIdentifier',
            'AuthenticatableIdentifier',
            'RoleIdentifier'
        );
    }

    /**
     * Permissions assigned to this Authenticatable via the restrictions database.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'AuthenticatablePermissionReferences',
            'AuthenticatableIdentifier',
            'PermissionIdentifier',
            'AuthenticatableIdentifier',
            'PermissionIdentifier'
        );
    }

    /**
     * Permission collections assigned to this Authenticatable via the restrictions database.
     */
    public function permissionCollections(): BelongsToMany
    {
        return $this->belongsToMany(
            PermissionCollection::class,
            'AuthenticatablePermissionCollectionReferences',
            'AuthenticatableIdentifier',
            'PermissionCollectionIdentifier',
            'AuthenticatableIdentifier',
            'PermissionCollectionIdentifier'
        );
    }

    /**
     * Performance Helper: Get all providers for this entity AND its sub-account.
     * Useful for login logic where you check all possible auth methods.
     */
    public function getAllAvailableProviders()
    {
        $providers = $this->authenticationProviders;

        if ($this->account) {
            $providers = $providers->merge($this->account->authenticationProviders);
        }

        return $providers;
    }
}

<?php

namespace App\Traits\Relations;

use App\Models\Authentication\ApprovalEntity;
use App\Models\Authentication\AttachableDataState;
use App\Models\Authentication\AttachedData;
use App\Models\Authentication\AuthenticationProvider;
use App\Models\Authentication\TemporarilyAttachedData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait AttachableDataRelationsTrait
{
    public function attachedData(): HasMany
    {
        return $this->hasMany(AttachedData::class, 'AttachedDataType', 'AttachableDataName');
    }

    public function temporarilyAttachedData(): HasMany
    {
        return $this->hasMany(TemporarilyAttachedData::class, 'AttachedDataType', 'AttachableDataName');
    }

    public function states(): HasMany
    {
        return $this->hasMany(AttachableDataState::class, 'AttachableDataParameterName', 'AttachableDataName');
    }

    public function authenticationProvider(): BelongsTo
    {
        return $this->belongsTo(
            AuthenticationProvider::class,
            'AuthenticationProviderIdentifier',
            'AuthenticationProviderIdentifier'
        );
    }

    public function approvalEntity(): BelongsTo
    {
        return $this->belongsTo(ApprovalEntity::class, 'ApprovalEntityIdentifier', 'ApprovalEntityIdentifier');
    }
}

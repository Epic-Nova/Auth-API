<?php

namespace App\Traits\Relations;

use App\Models\Authentication\ApprovalEntityData;
use App\Models\Authentication\ApprovalEntityParameter;
use App\Models\Authentication\AttachableData;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait ApprovalEntityRelationsTrait
{
    public function parameters(): HasMany
    {
        return $this->hasMany(ApprovalEntityParameter::class, 'ApprovalEntityIdentifier', 'ApprovalEntityIdentifier');
    }

    public function dataEntries(): HasMany
    {
        return $this->hasMany(ApprovalEntityData::class, 'ApprovalEntityIdentifier', 'ApprovalEntityIdentifier');
    }

    public function attachableData(): HasMany
    {
        return $this->hasMany(AttachableData::class, 'ApprovalEntityIdentifier', 'ApprovalEntityIdentifier');
    }
}

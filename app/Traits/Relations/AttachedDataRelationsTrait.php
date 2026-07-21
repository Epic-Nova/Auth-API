<?php

namespace App\Traits\Relations;

use App\Models\Authentication\AttachableData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

trait AttachedDataRelationsTrait
{
    public function attachableData(): BelongsTo
    {
        return $this->belongsTo(AttachableData::class, 'AttachedDataType', 'AttachableDataName');
    }

    public function attachedOn(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'AttachedOn', 'AttachedOnIdentifier');
    }
}

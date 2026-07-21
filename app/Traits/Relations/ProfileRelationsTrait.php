<?php

namespace App\Traits\Relations;

use App\Models\Authentication\Account;
use App\Models\Authentication\AttachedData;
use App\Models\Authentication\TemporarilyAttachedData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait ProfileRelationsTrait
{
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'AccountIdentifier', 'AccountIdentifier');
    }

    public function attachedData(): MorphMany
    {
        return $this->morphMany(AttachedData::class, 'attachedOn', 'AttachedOn', 'AttachedOnIdentifier');
    }

    public function temporarilyAttachedData(): MorphMany
    {
        return $this->morphMany(
            TemporarilyAttachedData::class,
            'attachedOn',
            'AttachedOn',
            'AttachedOnIdentifier'
        );
    }
}

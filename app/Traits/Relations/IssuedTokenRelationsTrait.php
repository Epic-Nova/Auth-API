<?php

namespace App\Traits\Relations;

use App\Models\Authentication\Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait IssuedTokenRelationsTrait
{
    public function authenticatable(): BelongsTo
    {
        return $this->belongsTo(Authenticatable::class, 'IssuedToAuthenticatable', 'AuthenticatableIdentifier');
    }
}

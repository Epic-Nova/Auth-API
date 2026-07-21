<?php

namespace App\Traits\Deprecated\Relations;

use App\Models\Authentication\Account;
use App\Models\Client;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

trait ProfileRelationsTrait
{
    /**
     * Profile belongs to Account.
     */
    public function Account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'AccountIdentifier', 'AccountIdentifier');
    }

    /**
     * Profile has Client through Account.
     */
    public function Client(): HasOneThrough
    {
        return $this->hasOneThrough(Client::class, Account::class, 'AccountIdentifier', 'ClientIdentifier', 'AccountIdentifier', 'AccountIdentifier');
    }

    /**
     * Returns the Client of this Profile for array serialization in the Laravel Framework.
     *
     * @return Client|null
     */
    public function getClientAttribute(): ?string
    {
        return $this->Client()->first()->ClientIdentifier;
    }
}

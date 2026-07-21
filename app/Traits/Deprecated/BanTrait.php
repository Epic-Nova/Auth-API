<?php

namespace App\Traits\Deprecated;

use App\Models\BannedClient;
use Illuminate\Support\Facades\Log;

/**
 * This Trait contains functions for validating bans inside the Epic Nova infrastructure,
 * these validation steps only exist inside this api, and the validation is called by
 * the LoginTokenTrait on token validation, therefore, every api that can validate tokens must use
 * the token validation endpoint for one of their validation steps.
 * Also, this Trait does not use Caching at all to ensure the validity of the ban.
 */
trait BanTrait
{
    /**
     * Checks if an BannedClient entry exists for a Client Identifier.
     *
     * @param  string  $Client  The client to search for in the BannedClients table
     * @return bool Returns true if a Banned Client entry exists for the given Client
     */
    public function BannedClientExistsByClientIdentifier(string $Client): bool
    {
        Log::debug('BannedClientExistsByClientIdentifier: Checking if BannedClient entry exists by Client Identifier: '.$Client);

        $BannedClientExists = BannedClient::whereBannedclient($Client)->exists();
    }
}

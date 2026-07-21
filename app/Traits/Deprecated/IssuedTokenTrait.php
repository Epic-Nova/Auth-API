<?php

namespace App\Traits\Deprecated;

use App\Models\Authentication\IssuedToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait IssuedTokenTrait
{
    use ErrorHandlerTrait;

    /**
     * Checks if an Issued Token Exists
     *
     * @param  string  $TokenJTI  The Token JTI
     * @return bool True if the Token exists, false otherwise
     */
    public function IssuedTokenExists(string $TokenJTI): bool
    {
        if (Cache::has("IssuedToken_$TokenJTI")) {
            Log::debug('IssuedTokenExists: Token exists in Cache');

            return true;
        }

        $IssuedTokenExists = IssuedToken::whereIssuedtoken($TokenJTI)->exists();
        if (! $IssuedTokenExists) {
            Log::error('IssuedTokenExists: Token does not exist: '.$TokenJTI);
            $this->HandleError('errors.com.epicnova.token.not_found', ['TokenJTI' => $TokenJTI]);
        }
        Log::debug('IssuedTokenExists: Token exists: '.$TokenJTI);

        return true;
    }

    /**
     * Gets an Issued Token from Token JTI
     *
     * @param  string  $TokenJTI  The Token JTI
     * @return IssuedToken The Issued Token
     */
    public function GetIssuedToken(string $TokenJTI): IssuedToken
    {
        Log::debug('GetIssuedToken: Getting Token with TokenJTI: '.$TokenJTI);

        $this->IssuedTokenExists($TokenJTI);

        if (Cache::has("IssuedToken_$TokenJTI")) {
            Log::debug('GetIssuedToken: Token: '.$TokenJTI.' is cached');

            return Cache::get("IssuedToken_$TokenJTI");
        } else {
            Log::debug('GetIssuedToken: Token: '.$TokenJTI.' is not cached, getting from Database and caching it');
            $IssuedToken = IssuedToken::find($TokenJTI);
            Cache::put("IssuedToken_$TokenJTI", $IssuedToken, Carbon::now()->addHours(5));

            return $IssuedToken;
        }
    }

    public function IssuedTokenExistsByRefreshJTI(string $RefreshJTI): bool
    {
        if (Cache::has("IssuedToken_$RefreshJTI")) {
            Log::debug('IssuedTokenExists: Token exists in Cache');

            return true;
        }

        $IssuedTokenExists = IssuedToken::whereIssuedrefreshtoken($RefreshJTI)->exists();
        if (! $IssuedTokenExists) {
            Log::error('IssuedTokenExists: Token does not exist: '.$RefreshJTI);
            $this->HandleError('errors.com.epicnova.token.not_found', ['TokenJTI' => $RefreshJTI]);
        }
        Log::debug('IssuedTokenExists: Token exists: '.$RefreshJTI);

        return true;
    }

    public function GetIssuedTokenByRefreshJTI(string $RefreshJTI): IssuedToken
    {
        Log::debug('GetIssuedTokenByRefreshJTI: Getting Token with TokenJTI: '.$RefreshJTI);

        $this->IssuedTokenExistsByRefreshJTI($RefreshJTI);

        if (Cache::has("IssuedToken_$RefreshJTI")) {
            Log::debug('GetIssuedTokenByRefreshJTI: Token: '.$RefreshJTI.' is cached');

            return Cache::get("IssuedToken_$RefreshJTI");
        } else {
            Log::debug('GetIssuedTokenByRefreshJTI: Token: '.$RefreshJTI.' is not cached, getting from Database and caching it');
            $IssuedToken = IssuedToken::whereIssuedrefreshtoken($RefreshJTI)->first();
            Cache::put("IssuedToken_$RefreshJTI", $IssuedToken, Carbon::now()->addHours(5));

            return $IssuedToken;
        }
    }

    /**
     * Constructs an Issued Token with the given parameters
     *
     * @param  string  $IssuedTokenJTI  The Token JTI
     * @param  string  $IssuedRefreshTokenJTI  The JTI of the Refresh Token
     * @param  string  $TokenPayload  The Token Payload
     * @return IssuedToken The Issued Token
     */
    public function ConstructIssuedToken(string $IssuedTokenJTI, string $IssuedRefreshTokenJTI, string $TokenPayload): IssuedToken
    {
        Log::debug('ConstructIssuedToken: Constructing Issued Token with TokenJTI: '.$IssuedTokenJTI);

        $IssuedToken = new IssuedToken;
        $IssuedToken->IssuedToken = $IssuedTokenJTI;
        $IssuedToken->IssuedRefreshToken = $IssuedRefreshTokenJTI;
        json_decode($TokenPayload);
        $IssuedToken->TokenPayload = json_last_error() == JSON_ERROR_NONE ? $TokenPayload : null;
        $IssuedToken->save();

        return $IssuedToken;
    }
}

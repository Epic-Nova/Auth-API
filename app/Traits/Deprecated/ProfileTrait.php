<?php

namespace App\Traits\Deprecated;

use App\Models\Authentication\Profile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait ProfileTrait
{
    use ErrorHandlerTrait;

    /**
     * Checks if a Profile Exists
     *
     * @param  string  $Profile  The Profile Identifier of a Profile
     * @return bool True if the Profile exists, false otherwise.
     */
    public function ProfileExists(string $Profile): bool
    {
        Log::debug('ProfileExists: Checking if Profile exists: '.$Profile);

        if (Cache::has("Profile_$Profile")) {
            Log::debug('ProfileExists: Profile exists in Cache');

            return true;
        }

        $ProfileExists = Profile::whereProfileidentifier($Profile)->exists();
        if (! $ProfileExists) {
            Log::error('ProfileExists: Profile does not exist: '.$Profile);
            $this->HandleError('errors.com.epicnova.profile.not_found', ['ProfileIdentifier' => $Profile]);
        }

        Log::debug('ProfileExists: Profile exists: '.$Profile);

        return true;
    }

    /**
     * Gets a Profile from Profile Identifier
     *
     * @param  string  $Profile  The Profile Identifier of a Profile
     * @return Profile The Profile
     */
    public function GetProfile(string $Profile): Profile
    {
        Log::debug('GetProfile: Getting Profile with ProfileIdentifier: '.$Profile);

        $this->ProfileExists($Profile);

        if (Cache::has("Profile_$Profile")) {
            Log::debug('GetProfile: Profile: '.$Profile.' is cached');

            return Cache::get("Profile_$Profile");
        } else {
            Log::debug('GetProfile: Profile: '.$Profile.' is not cached, getting from Database and caching it');
            $Profile = Profile::find($Profile);
            Cache::put("Profile_$Profile->ProfileIdentifier", $Profile, Carbon::now()->addHours(5));

            return $Profile;
        }
    }

    /**
     * Checks if a Profile belongs to the entered Account
     *
     * @param  string  $Account  The Account Identifier of the Account
     * @param  string|null  $Profile  The Profile Identifier of the Profile
     * @return bool True if the Profile belongs to the Account, false otherwise.
     */
    public function ProfileBelongsToAccount(string $Account, ?string $Profile = ''): bool
    {
        Log::debug('ProfileBelongsToAccount: Checking if Profile belongs to Account: '.$Account);

        $this->CheckProfileVariable($Profile);
        $this->ProfileExists($Profile);

        if ($this->GetProfile($Profile)->AccountIdentifier == $Account) {
            Log::debug('ProfileBelongsToAccount: Profile belongs to Account: '.$Account);

            return true;
        } else {
            Log::error('ProfileBelongsToAccount: Profile does not belong to Account: '.$Account);
            $this->HandleError('errors.com.epicnova.profile.not_found', ['ProfileIdentifier' => $Profile]);
        }
    }

    public function CheckProfileVariable(string &$Profile): void
    {
        Log::debug('CheckProfileVariable: Checking Profile is Empty');

        if (empty($Profile)) {
            Log::debug('CheckProfileVariable: Profile Variable is not set, trying to set it to the ProfileIdentifier of the owning Class');
            $Profile = $this->ProfileIdentifier;
        } else {
            Log::debug('CheckProfileVariable: Profile Variable is set');
        }
    }
}

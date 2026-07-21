<?php

namespace App\Traits\Deprecated;

use App\Models\Authentication\Account;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait AccountTrait
{
    use ErrorHandlerTrait;

    /**
     * Checks if an Account Exists
     *
     * @param  string  $Account  The Account Identifier of an Account
     * @return bool True if the Account exists, false otherwise.
     */
    public function AccountExists(string $Account): bool
    {
        Log::debug('AccountExists: Checking if Account exists: '.$Account);

        if (Cache::has("Account_$Account")) {
            Log::debug('AccountExists: Account exists in Cache');

            return true;
        }

        $AccountExists = Account::whereAccountidentifier($Account)->exists();
        if (! $AccountExists) {
            Log::error('AccountExists: Account does not exist: '.$Account);
            $this->HandleError('errors.com.epicnova.account.not_found', ['AccountIdentifier' => $Account]);
        }

        Log::debug('AccountExists: Account exists: '.$Account);

        return true;
    }

    /**
     * Gets an Account from Account Identifier
     *
     * @param  string  $Account  The Account Identifier of an Account
     * @return Account The Account
     */
    public function GetAccount(string $Account): Account
    {
        Log::debug('GetAccount: Getting Account with AccountIdentifier: '.$Account);

        $this->AccountExists($Account);

        if (Cache::has("Account_$Account")) {
            Log::debug('GetAccount: Account: '.$Account.' is cached');

            return Cache::get("Account_$Account");
        } else {
            Log::debug('GetAccount: Account: '.$Account.' is not cached, getting from Database and caching it');
            $Account = Account::find($Account);
            Cache::put("Account_$Account->AccountIdentifier", $Account, Carbon::now()->addHours(5));

            return $Account;
        }
    }

    /**
     * Gets the count of Accounts on a single EMail Address
     *
     * @param  string  $Email  The Account EMail of an Account
     * @return int The Account Count on one EMail
     */
    public function GetAccountCountOnEMail(string $Email): int
    {
        Log::debug('GetAccountCountOnEMail: Getting Account Count with EMail: '.$Email);

        if (Cache::has("AccountCount_$Email")) {
            Log::debug('GetAccountCountOnEMail: Account Count with EMail: '.$Email.' is cached');

            return Cache::get("AccountCount_$Email");
        } else {
            Log::debug('GetAccountCountOnEMail: Account Count with EMail: '.$Email.' is not cached, getting from Database and caching it');
            $AccountCount = Account::whereEmailaddress($Email)->count();
            Cache::put("AccountCount_$Email", $AccountCount, Carbon::now()->addHours(5));

            return $AccountCount;
        }
    }

    /**
     * Checks if an Account has multiple Accounts
     *
     * @param  string  $Email  The Account EMail of an Account
     * @return bool True if the email has multiple Accounts, false otherwise.
     */
    public function HasMultipleAccountsOnEMail(string $Email): bool
    {
        Log::debug('HasMultipleAccountsOnEMail: Checking if Account with EMail: '.$Email.' has multiple Accounts');

        if (Cache::has("EmailHasMultipleAccounts_$Email")) {
            Log::debug('HasMultipleAccountsOnEMail: Boolean if Account with EMail: '.$Email.' has multiple Accounts is cached');

            return Cache::get("EmailHasMultipleAccounts_$Email");
        } else {
            Log::debug('HasMultipleAccountsOnEMail: Boolean if Account with EMail: '.$Email.' has multiple Accounts is not cached, getting from Database and caching it');
            $bHasMultipleAccounts = $this->GetAccountCountOnEMail($Email) > 1;
            Cache::put("EmailHasMultipleAccounts_$Email", $bHasMultipleAccounts, Carbon::now()->addHours(5));

            return $bHasMultipleAccounts;
        }
    }

    /**
     * Gets all Accounts on a single EMail Address
     *
     * @param  string  $Email  The Account EMail of an Account
     * @param  bool  $bReturnAlwaysArray  True if the function should always return an array, false otherwise.
     * @return mixed Account when there is just a single account registered on the EMail, array of Accounts when there are multiple Accounts registered on the EMail
     */
    public function GetAccountWithEMail(string $Email, bool $bReturnAlwaysArray = false): mixed
    {
        Log::debug('GetAccountWithEMail: Getting Account(s) with EMail: '.$Email);

        if (! $this->GetAccountCountOnEMail($Email) > 0) {
            Log::error('GetAccountWithEMail: No Account found with EMail: '.$Email);
            $this->HandleError('errors.com.epicnova.account.not_found', ['AccountIdentifier' => $Email]);
        }

        if ($this->HasMultipleAccountsOnEMail($Email)) {
            Log::debug('GetAccountWithEMail: Multiple Accounts found with EMail: '.$Email);

            if (Cache::has("Account_$Email")) {
                Log::debug('GetAccountWithEMail: Accounts with EMail: '.$Email.' are cached');

                return Cache::get("Account_$Email");
            } else {
                Log::debug('GetAccountWithEMail: Accounts with EMail: '.$Email.' are not cached, getting from Database and caching it');
                $Accounts = Account::whereEmailaddress($Email)->getModels();
                Cache::put("Account_$Email", $Accounts, Carbon::now()->addHours(5));

                return $Accounts;
            }
        } else {
            Log::debug('GetAccountWithEMail: Single Account found with EMail: '.$Email);
            if (Cache::has("Account_$Email")) {
                Log::debug('GetAccountWithEMail: Account with EMail: '.$Email.' is cached'); //
                $Account = Cache::get("Account_$Email");
            } else {
                Log::debug('GetAccountWithEMail: Account with EMail: '.$Email.' is not cached, getting from Database and caching it');
                $Account = Account::whereEmailaddress($Email)->first();
                Cache::put("Account_$Email", $Account, Carbon::now()->addHours(5));
            }

            if ($bReturnAlwaysArray) {
                return [$Account];
            }

            return $Account;
        }
    }
}

<?php

namespace App\Traits\Deprecated;

use App\Models\Authorisation\Scope;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait ScopeTrait
{
    use ErrorHandlerTrait;

    /**
     * Checks if a Scope Exists
     * When using the Name ScopeExists for the function,
     * the Laravel Framework thinks this function is Part of the Eloquent Model Builder, so don't use it.
     *
     * @param  string  $Scope  The Scope Identifier
     */
    public function _ScopeExists(string $Scope): bool
    {
        Log::debug('ScopeExists: Checking if Scope exists: '.$Scope);

        if (Cache::has("Scope_$Scope")) {
            Log::debug('ScopeExists: Scope exists in Cache');

            return true;
        }

        $ScopeExists = Scope::whereScopeidentifier($Scope)->exists();
        if (! $ScopeExists) {
            Log::error('ScopeExists: Scope does not exist: '.$Scope);
            $this->HandleError('errors.com.epicnova.scope.not_found', ['ScopeIdentifier' => $Scope]);
        }

        Log::debug('ScopeExists: Scope exists: '.$Scope);

        return true;
    }

    /**
     * Returns a Scope by its Identifier
     */
    public function GetScope(string $Scope): ?Scope
    {
        Log::debug('GetScope: Getting Scope with ScopeIdentifier: '.$Scope);

        $this->_ScopeExists($Scope);

        if (Cache::has("Scope_$Scope")) {
            Log::debug('GetScope: Scope: '.$Scope.' is cached');

            return Cache::get("Scope_$Scope");
        } else {
            Log::debug('GetScope: Scope: '.$Scope.' is not cached, getting from Database and caching it');
            $Scope = Scope::find($Scope);
            Cache::put("Scope_$Scope->ScopeIdentifier", $Scope, Carbon::now()->addHours(5));

            return $Scope;
        }
    }

    public function GetScopeName(?string $Scope = ''): string // @TODO do this for every table that has an epic nova identifier
    {
        // TODO we need to get the scope identifier get the string after the last dot, replace the _ with a space and capitalize the first letter of each word

        return '';
    }

    /**
     * Checks if a Scope is Active
     *
     * @param  string|null  $Scope  Identifier of the Scope
     * @return bool True if the Scope is Active, otherwise false
     */
    public function IsScopeActive(?string $Scope = ''): bool
    {
        $this->CheckScopeVariable($Scope);

        Log::debug('IsScopeActive: Checking if Scope is Active: '.$Scope);

        $IsScopeActive = $this->GetScope($Scope)->IsActive;
        if (! $IsScopeActive) {
            Log::error('IsScopeActive: Scope is not Active: '.$Scope);
            $this->HandleError('errors.com.epicnova.scope.not_active', ['ScopeIdentifier' => $Scope]);
        }

        return $IsScopeActive;
    }

    /**
     * Attaches a Permission to this Scope.
     *
     * @param  string  $Scope  The Scope Identifier
     * @param  string  $Permission  The Permission to attach.
     */
    public function AttachPermissionToScope(string $Scope, string $Permission): void
    {
        $this->CheckScopeVariable($Scope);

        $Scope = $this->GetScope($Scope);
        $Scope->Permissions()->attach($Permission);

        Log::debug('AttachPermissionToScope: Attached Permission: '.$Permission.' to Scope: '.$Scope->ScopeIdentifier);
    }

    /**
     * Detaches a Permission from this Scope.
     *
     * @param  string  $Permission  The Permission to detach.
     * @param  string|null  $Scope  The Scope Identifier
     */
    public function DetachPermissionFromScope(string $Permission, ?string $Scope = ''): void
    {
        $this->CheckScopeVariable($Scope);

        $Scope = $this->GetScope($Scope);
        $Scope->Permissions()->detach($Permission);

        Log::info('DetachPermissionFromScope: Detached Permission: '.$Permission.' from Scope: '.$Scope->ScopeIdentifier);
    }

    /**
     * Checks a Scope Identifier and modifies it when needed
     *
     * @param  string  $Scope  The reference to the Scope Identifier to check
     */
    private function CheckScopeVariable(string &$Scope): void
    {
        Log::debug('CheckScopeVariable: Checking if Scope is empty');

        if (empty($Scope)) {
            Log::warning('CheckScopeVariable: Scope Variable not set, trying to set it to the ScopeIdentifier of the owning Class');
            $Scope = $this->ScopeIdentifier;
        } else {
            Log::debug('CheckScopeVariable: Scope Variable is set');
        }
    }
}

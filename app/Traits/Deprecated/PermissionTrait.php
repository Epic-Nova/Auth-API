<?php

namespace App\Traits\Deprecated;

use App\Models\Authorisation\Permission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait PermissionTrait
{
    use ErrorHandlerTrait;

    /**
     * Checks if a Permission Exists
     */
    public function PermissionExists(string $Permission): bool
    {
        Log::debug('PermissionExists: Checking if Permission exists: '.$Permission);

        if (Cache::has("Permission_$Permission")) {
            Log::debug('PermissionExists: Permission exists in Cache');

            return true;
        }

        $PermissionExists = Permission::wherePermissionidentifier($Permission)->exists();
        if (! $PermissionExists) {
            Log::error('PermissionExists: Permission does not exist: '.$Permission);
            $this->HandleError('errors.com.epicnova.permission.not_found', ['PermissionIdentifier' => $Permission]);
        }

        Log::debug('PermissionExists: Permission exists: '.$Permission);

        return true;
    }

    /**
     * Returns a Permission by its Identifier
     */
    public function GetPermission(string $Permission): ?Permission
    {
        Log::debug('GetPermission: Getting Permission with PermissionIdentifier: '.$Permission);

        $this->PermissionExists($Permission);

        if (Cache::has("Permission_$Permission")) {
            Log::debug('GetPermission: Permission: '.$Permission.' is cached');

            return Cache::get("Permission_$Permission");
        } else {
            Log::debug('GetPermission: Permission: '.$Permission.' is not cached, getting from Database and caching it');
            $Permission = Permission::find($Permission);
            Cache::put("Permission_$Permission->PermissionIdentifier", $Permission, Carbon::now()->addHours(5));

            return $Permission;
        }
    }

    /**
     * Checks if a Permission is Active
     *
     * @param  string|null  $Permission  Identifier of the Permission
     * @return bool True if the Permission is Active, otherwise false
     */
    public function IsPermissionActive(?string $Permission = ''): bool
    {
        $this->CheckPermissionVariable($Permission);

        Log::debug('IsPermissionActive: Checking if Permission is Active: '.$Permission);

        $IsPermissionActive = $this->GetPermission($Permission)->IsActive;
        if (! $IsPermissionActive) {
            Log::error('IsPermissionActive: Permission is not active: '.$Permission);
            $this->HandleError('errors.com.epicnova.permission.not_active', ['PermissionIdentifier' => $Permission]);
        }

        return $IsPermissionActive;
    }

    /**
     * Checks if a Permission has a Head Permission
     *
     * @param  string|null  $Permission  Identifier of the Permission
     * @return bool True if the Permission has a Head Permission, otherwise false
     */
    public function HasHeadPermission(?string $Permission = ''): bool
    {
        $this->CheckPermissionVariable($Permission);

        Log::debug('HasHeadPermission: Checking if Permission has a Head Permission: '.$Permission);

        return $this->GetPermission($Permission)->HeadPermission != '';
    }

    /**
     * Returns the Head Permission of a Permission
     *
     * @param  string|null  $Permission  Identifier of the Permission
     * @return string|null The Identifier of the Head Permission
     */
    public function GetHeadPermission(?string $Permission = ''): ?string
    {
        $this->CheckPermissionVariable($Permission);

        Log::debug('GetHeadPermission: Getting Head Permission of Permission: '.$Permission);

        return $this->GetPermission($Permission)->HeadPermission;
    }

    /**
     * Checks a Permission Identifier and modifies it when needed
     *
     * @param  string  $Permission  The reference to the Permission Identifier to check
     */
    protected function CheckPermissionVariable(string &$Permission): void
    {
        Log::debug('CheckPermissionVariable: Checking if Permission is empty');

        if (empty($Permission)) {
            Log::debug('CheckPermissionVariable: Permission Variable not set, trying to set it to the PermissionIdentifier of the owning Class');
            $Permission = $this->PermissionIdentifier;
        } else {
            Log::debug('CheckPermissionVariable: Permission Variable is set');
        }
    }
}

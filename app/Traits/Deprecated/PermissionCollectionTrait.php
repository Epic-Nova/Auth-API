<?php

namespace App\Traits\Deprecated;

use App\Models\Authorisation\PermissionCollection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait PermissionCollectionTrait
{
    use ErrorHandlerTrait;

    /**
     * Checks if a Permission Collection Exists
     *
     * @param  string  $PermissionCollection  The Permission Collection Identifier of an Shard
     * @return bool True if the Shard exists, false otherwise.
     */
    public function PermissionCollectionExists(string $PermissionCollection): bool
    {
        Log::debug('PermissionCollectionExists: Checking if PermissionCollection exists: '.$PermissionCollection);

        if (Cache::has("PermissionCollection_$PermissionCollection")) {
            Log::debug('PermissionCollectionExists: PermissionCollection exists in Cache');

            return true;
        }

        $PermissionCollectionExists = PermissionCollection::wherePermissioncollectionidentifier($PermissionCollection)->exists();
        if (! $PermissionCollectionExists) {
            Log::error('PermissionCollectionExists: PermissionCollection does not exist: '.$PermissionCollection);
            $this->HandleError('errors.com.epicnova.permission_collection.not_found', ['PermissionCollectionIdentifier' => $PermissionCollection]);
        }

        Log::debug('PermissionCollectionExists: PermissionCollection exists: '.$PermissionCollection);

        return true;
    }

    /**
     * Gets a Permission Collection from Permission Collection Identifier
     *
     * @param  string  $PermissionCollection  The Permission Collection Identifier of an Shard
     * @return PermissionCollection The Permission Collection
     */
    public function GetPermissionCollection(string $PermissionCollection): PermissionCollection
    {
        Log::debug('GetPermissionCollection: Getting PermissionCollection with PermissionCollectionIdentifier: '.$PermissionCollection);

        $this->PermissionCollectionExists($PermissionCollection);

        if (Cache::has("PermissionCollection_$PermissionCollection")) {
            Log::debug('GetPermissionCollection: PermissionCollection: '.$PermissionCollection.' is cached');

            return Cache::get("PermissionCollection_$PermissionCollection");
        } else {
            Log::debug('GetPermissionCollection: PermissionCollection is not cached, getting from Database and caching it');
            $PermissionCollection = PermissionCollection::find($PermissionCollection);
            Cache::put("PermissionCollection_$PermissionCollection->PermissionCollectionIdentifier", $PermissionCollection, Carbon::now()->addHours(5));

            return $PermissionCollection;
        }
    }

    /**
     * Attaches a Permission to the Permission Collection.
     *
     * @param  string  $Permission  The Permission to attach.
     */
    public function AttachPermissionToPermissionCollection(string $Permission): void
    {
        $this->Permissions()->attach($Permission);
        $this->save();

        Log::debug('AttachPermissionToPermissionCollection: Attached Permission: '.$Permission);
    }

    /**
     * Detaches a Permission from the Permission Collection.
     *
     * @param  string  $Permission  The Permission to detach.
     */
    public function DetachPermissionFromPermissionCollection(string $Permission): void
    {
        $this->Permissions()->detach($Permission);
        $this->save();

        Log::debug('DetachPermissionFromPermissionCollection: Detached Permission: '.$Permission);
    }
}

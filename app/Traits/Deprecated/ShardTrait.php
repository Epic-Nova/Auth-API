<?php

namespace App\Traits\Deprecated;

use App\Models\Authorisation\Shard;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait ShardTrait
{
    /**
     * Checks if an Shard Exists
     *
     * @param  string  $Shard  The Shard Identifier of an Shard
     * @return bool True if the Shard exists, false otherwise.
     */
    public function ShardExists(string $Shard): bool
    {
        Log::debug('ShardExists: Checking if Shard exists: '.$Shard);

        if (Cache::has("Shard_$Shard")) {
            Log::debug('ShardExists: Shard exists in Cache');

            return true;
        }

        $ShardExists = Shard::whereShardidentifier($Shard)->exists();
        if (! $ShardExists) {
            Log::error('ShardExists: Shard does not exist: '.$Shard);
            $this->HandleError('errors.com.epicnova.shard.not_found', ['ShardIdentifier' => $Shard]);
        }

        Log::debug('ShardExists: Shard exists: '.$Shard);

        return true;
    }

    /**
     * Gets an Shard from Shard Identifier
     *
     * @param  string  $Shard  The Shard Identifier of an Shard
     * @return Shard The Shard
     */
    public function GetShard(string $Shard): Shard
    {
        Log::debug('GetShard: Getting Shard with ShardIdentifier: '.$Shard);

        $this->ShardExists($Shard);

        if (Cache::has("Shard_$Shard")) {
            Log::debug('GetShard: Shard: '.$Shard.' is cached');

            return Cache::get("Shard_$Shard");
        } else {
            Log::debug('GetShard: Shard: '.$Shard.' is not cached, getting from Database and caching it');
            $Shard = Shard::find($Shard);
            Cache::put("Shard_$Shard->ShardIdentifier", $Shard, Carbon::now()->addHours(5));

            return $Shard;
        }
    }

    /**
     * Checks if a Shard is Active
     *
     * @param  string|null  $Shard  Identifier of the Shard
     * @return bool True if the Shard is Active, otherwise false
     */
    public function IsShardActive(?string $Shard = ''): bool
    {
        $this->CheckShardVariable($Shard);

        Log::debug('IsShardActive: Checking if Shard is Active: '.$Shard);

        $IsShardActive = $this->GetShard($Shard)->IsActive;
        if (! $IsShardActive) {
            Log::error('IsShardActive: Shard is not Active: '.$Shard);
            $this->HandleError('errors.com.epicnova.shard.not_active', ['ShardIdentifier' => $Shard]);
        }

        return $IsShardActive;
    }

    /**
     * Checks if a Shard has Child Shards
     *
     * @param  string|null  $Shard  Identifier of the Shard
     * @return bool True if an Shard has Children, false otherwise.
     */
    public function HasChildShards(?string $Shard = ''): bool
    {
        $this->CheckShardVariable($Shard);

        return $this->GetShard($Shard)->ChildShards()->exists();
    }

    /**
     * Gets the Child Shards of an Shard
     *
     * @param  string|null  $Shard  Identifier of the Shard
     * @return array The Child Shards of an Shard
     */
    public function GetChildShards(?string $Shard = ''): mixed
    {
        $this->CheckShardVariable($Shard);

        Log::debug('GetChildShards: Getting Child Shards of Shard: '.$Shard);

        return $this->GetShard($Shard)->ChildShards;
    }

    /**
     * Checks if an Shard is the Child of another Shard
     *
     * @param  string  $PossibleChild  The Possible Child of an Shard
     * @param  string|null  $Shard  The Shard Identifier of an Shard
     * @return bool True if the PossibleChild is a child of the Shard, false otherwise.
     */
    public function IsChildOfShard(string $PossibleChild, ?string $Shard = ''): bool
    {
        $this->CheckShardVariable($Shard);

        Log::debug('IsChildOfShard: Checking if Shard: '.$PossibleChild.' is a child of Shard: '.$Shard);

        $this->ShardExists($Shard);
        $this->ShardExists($PossibleChild);

        if (! $this->HasChildShards($Shard)) {
            return false;
        }

        foreach ($this->GetChildShards($Shard) as $ChildShard) {
            Log::debug("Try to find $PossibleChild in $ChildShard->ShardIdentifier");

            if ($ChildShard->ShardIdentifier == $PossibleChild) {
                Log::debug("Shard $PossibleChild is a child of $Shard");

                return true;
            }
        }

        return false;
    }

    /**
     * Checks if an Shard can be used as an API Shard
     *
     * @param  string|null  $Shard  The Shard Identifier of an Shard
     * @return bool True if the Shard can be used as an API Shard, false otherwise.
     */
    public function IsAPIShard(?string $Shard = ''): bool
    {
        $this->CheckShardVariable($Shard);

        Log::debug("IsAPIShard: Checking if Shard $Shard is an API Shard");

        if (! $this->GetShard($Shard)->IsAPI) {
            Log::error('IsAPIShard: Shard '.$Shard.' is not an API Shard');
            $this->HandleError('errors.com.epicnova.shard.is_not_an_api_shard', ['ShardIdentifier' => $Shard]);
        }

        return true;
    }

    /**
     * Set a Parent Shard for an Shard
     *
     * @param  string  $ParentShard  The Shard Identifier of the Parent Shard
     * @param  string|null  $Shard  The Shard Identifier of an Shard
     */
    public function SetParentShard(string $ParentShard, ?string $Shard = ''): void
    {
        $this->CheckShardVariable($Shard);

        $Shard = $this->GetShard($Shard);
        $Shard->ParentShard()->associate($ParentShard);
        $Shard->save();

        Log::info('SetParentShard: Set Parent Shard for Shard: '.$Shard->ShardIdentifier.' to '.$ParentShard);
    }

    /**
     * DeSet a Parent Shard for an Shard
     *
     * @param  string|null  $Shard  The Shard Identifier of an Shard
     */
    public function DeSetParentShard(?string $Shard = ''): void
    {
        $this->CheckShardVariable($Shard);

        $Shard = $this->GetShard($Shard);
        $Shard->ParentShard()->dissociate();
        $Shard->save();

        Log::info('DeSetParentShard: DeSet Parent Shard for Shard: '.$Shard->ShardIdentifier);
    }

    /**
     * Checks an Shard Identifier and modifies it when needed
     *
     * @param  string  $Shard  The reference to the Shard Identifier to check
     */
    private function CheckShardVariable(string &$Shard): void
    {
        Log::debug('CheckShardVariable: Checking if Shard is empty');

        if (empty($Shard)) {
            Log::warning('CheckShardVariable: Shard Variable not set, trying to set it to the ShardIdentifier of the owning Class');
            $Shard = $this->ShardIdentifier;
        } else {
            Log::debug('CheckShardVariable: Shard Variable is set');
        }
    }
}

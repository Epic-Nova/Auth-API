<?php

namespace App\Traits\Deprecated\Relations;

use App\Models\Authentication\Account;
use App\Models\Authorisation\Permission;
use App\Models\Authorisation\PermissionCollection;
use App\Models\Authorisation\Role;
use App\Models\ServerClient;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait ClientRelationsTrait
{
    /**
     * Client belongs to many Roles
     */
    public function Roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'ClientRoleReferences', 'ClientIdentifier', 'RoleIdentifier');
    }

    /**
     * Returns the Roles of this Client for array serialization in the Laravel Framework.
     *
     * @return array|null
     */
    public function getRolesAttribute()
    {
        return $this->Roles()->get();
    }

    /**
     * Client belongs to many ExtraPermissions
     */
    public function ExtraPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'ClientPermissionReferences', 'ClientIdentifier', 'PermissionIdentifier');
    }

    /**
     * Returns the ExtraPermissions of this Client for array serialization in the Laravel Framework.
     *
     * @return array|null
     */
    public function getExtraPermissionsAttribute()
    {
        return $this->ExtraPermissions()->get();
    }

    /**
     * Client belongs to many ExtraPermissionCollections
     */
    public function ExtraPermissionCollections(): BelongsToMany
    {
        return $this->belongsToMany(PermissionCollection::class, 'ClientPermissionCollectionReferences', 'ClientIdentifier', 'PermissionCollectionIdentifier');
    }

    /**
     * Returns the ExtraPermissionCollections of this Client for array serialization in the Laravel Framework.
     *
     * @return array|null
     */
    public function getExtraPermissionCollectionsAttribute()
    {
        return $this->ExtraPermissionCollections()->get();
    }

    /**
     * Client has one Account or ServerClient
     */
    public function Intractable(): HasOne
    {
        $ServerClientRelation = $this->hasOne(ServerClient::class, 'ServerClientIdentifier', 'ServerClientIdentifier');
        $AccountRelation = $this->hasOne(Account::class, 'AccountIdentifier', 'AccountIdentifier');

        return $ServerClientRelation->exists() ? $ServerClientRelation : $AccountRelation;
    }

    /**
     * Returns the Intractable of this Client for array serialization in the Laravel Framework.
     *
     * @return Account|ServerClient|null
     */
    public function getIntractableAttribute()
    {
        return $this->Intractable()->first();
    }
}

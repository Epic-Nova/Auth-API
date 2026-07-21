<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('AuthenticatablePermissionCollectionReferences', function (Blueprint $table) {

            $table->string('AuthenticatableIdentifier')
                ->comment('The AuthenticatableIdentifier of the Authenticatable that this data belongs to.');

            $table->string('PermissionCollectionIdentifier')
                ->comment('The PermissionCollectionIdentifier of the PermissionCollection that this data belongs to.');

            $table->foreign('AuthenticatableIdentifier', 'AuthenticatableCollection_AuthIdentifier_Foreign')
                ->references('AuthenticatableIdentifier')
                ->on('Auth.Authenticatable')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('PermissionCollectionIdentifier', 'AuthenticatableCollection_PermissionCollectionIdentifier_Foreign')
                ->references('PermissionCollectionIdentifier')
                ->on('PermissionCollections')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

        });
    }

    public function down()
    {
        Schema::dropIfExists('AuthenticatablePermissionCollectionReferences');
    }
};

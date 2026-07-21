<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('RolePermissionCollectionReferences', function (Blueprint $table) {

            $table->string('RoleIdentifier')
                ->comment('The RoleIdentifier of the Role that this data belongs to.');

            $table->string('PermissionCollectionIdentifier')
                ->comment('The PermissionCollectionIdentifier of the PermissionCollection that this data belongs to.');

            $table->foreign('RoleIdentifier', 'RolePermissionCollection_RoleIdentifier_Foreign')
                ->references('RoleIdentifier')
                ->on('Roles')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('PermissionCollectionIdentifier', 'RolePermissionCollection_PermissionCollectionIdentifier_Foreign')
                ->references('PermissionCollectionIdentifier')
                ->on('PermissionCollections')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

        });
    }

    public function down()
    {
        Schema::dropIfExists('RolePermissionCollectionReferences');
    }
};

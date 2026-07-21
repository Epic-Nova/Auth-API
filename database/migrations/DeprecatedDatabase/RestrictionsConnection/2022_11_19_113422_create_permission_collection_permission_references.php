<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('PermissionCollectionPermissionReferences', function (Blueprint $table) {

            $table->string('PermissionCollectionIdentifier')
                ->comment('The PermissionCollectionIdentifier of the PermissionCollection that this data belongs to.');

            $table->string('PermissionIdentifier')
                ->comment('The PermissionIdentifier of the Permission that this data belongs to.');

            $table->foreign('PermissionCollectionIdentifier', 'PermissionCollection_PermissionCollectionIdentifier_Foreign')
                ->references('PermissionCollectionIdentifier')
                ->on('PermissionCollections')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('PermissionIdentifier', 'PermissionCollection_PermissionIdentifier_Foreign')
                ->references('PermissionIdentifier')
                ->on('Permissions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

        });
    }

    public function down()
    {
        Schema::dropIfExists('PermissionCollectionPermissionReferences');
    }
};

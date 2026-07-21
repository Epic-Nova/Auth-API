<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('RolePermissionReferences', function (Blueprint $table) {

            $table->string('RoleIdentifier')
                ->comment('The RoleIdentifier of the Role that this data belongs to.');

            $table->string('PermissionIdentifier')
                ->comment('The PermissionIdentifier of the Permission that this data belongs to.');

            $table->foreign('RoleIdentifier', 'RolePermission_RoleIdentifier_Foreign')
                ->references('RoleIdentifier')
                ->on('Roles')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('PermissionIdentifier', 'RolePermission_PermissionIdentifier_Foreign')
                ->references('PermissionIdentifier')
                ->on('Permissions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

        });
    }

    public function down()
    {
        Schema::dropIfExists('RolePermissionReferences');
    }
};

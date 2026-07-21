<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('AuthenticatablePermissionReferences', function (Blueprint $table) {

            $table->string('AuthenticatableIdentifier')
                ->comment('The AuthenticatableIdentifier of the Authenticatable that this data belongs to.');

            $table->string('PermissionIdentifier')
                ->comment('The PermissionIdentifier of the Permission that this data belongs to.');

            $table->foreign('AuthenticatableIdentifier', 'AuthenticatablePermission_AuthIdentifier_Foreign')
                ->references('AuthenticatableIdentifier')
                ->on('Auth.Authenticatable')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('PermissionIdentifier', 'AuthenticatablePermission_PermissionIdentifier_Foreign')
                ->references('PermissionIdentifier')
                ->on('Permissions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

        });
    }

    public function down()
    {
        Schema::dropIfExists('AuthenticatablePermissionReferences');
    }
};

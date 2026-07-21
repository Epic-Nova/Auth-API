<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('AuthenticatableRoleReferences', function (Blueprint $table) {

            $table->foreignUuid('AuthenticatableIdentifier')
                ->comment('The AuthenticatableIdentifier of the Authenticatable that this data belongs to.')
                ->constrained('Auth.Authenticatable', 'AuthenticatableIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('RoleIdentifier')
                ->comment('The RoleIdentifier of the Role that this data belongs to.');

            $table->foreign('RoleIdentifier', 'AuthenticatableRole_RoleIdentifier_Foreign')
                ->references('RoleIdentifier')
                ->on('Roles')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

        });
    }

    public function down()
    {
        Schema::dropIfExists('AuthenticatableRoleReferences');
    }
};

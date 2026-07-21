<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('RoleScopeReferences', function (Blueprint $table) {

            $table->string('RoleIdentifier')
                ->comment('The RoleIdentifier of the Role that this data belongs to.');

            $table->string('ScopeIdentifier')
                ->comment('The ScopeIdentifier of the Scope that this data belongs to.');

            $table->foreign('RoleIdentifier', 'RoleScope_RoleIdentifier_Foreign')
                ->references('RoleIdentifier')
                ->on('Roles')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('ScopeIdentifier', 'RoleScope_ScopeIdentifier_Foreign')
                ->references('ScopeIdentifier')
                ->on('Scopes')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

        });
    }

    public function down()
    {
        Schema::dropIfExists('RoleScopeReferences');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('Roles', function (Blueprint $table) {

            $table->foreign('ParentRoleIdentifier', 'Roles_ParentRoleIdentifier_Foreign')
                ->references('RoleIdentifier')
                ->on('Roles')
                ->nullOnDelete()
                ->cascadeOnUpdate();

        });
    }
};

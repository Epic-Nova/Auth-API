<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('RequiredRoleForParameters', function (Blueprint $table) {
            $table->string('ParameterReference', 512)
                ->comment('The parameter reference requiring a role.')
                ->primary();

            $table->string('RoleIdentifier')
                ->default('com.epicnova.roles.')
                ->comment('The role identifier required to access the parameter.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the requirement was created.')
                ->index();

            $table->foreign('RoleIdentifier', 'RequiredRoleForParam_RoleID_FK')
                ->references('RoleIdentifier')
                ->on('Roles')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('RequiredRoleForParameters');
    }
};

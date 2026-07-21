<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('RequiredRoleForParameters', function (Blueprint $table) {

            $table->string('ParameterReference', 512)
                ->comment('The name of the parameter that requires a role to be present in the authenticatable that is requesting the parameter.
                    The format of the reference should be: [DATABASE_CONNECTION].[DATABASE_TABLE].[COLUMN_NAME]');

            $table->string('RoleIdentifier')
                ->comment('The identifier of the role that is required to be present.')
                ->default('com.epicnova.roles.');

            $table->foreign('RoleIdentifier', 'RequiredRoleForParameters_RoleIdentifier_ForeignKey')
                ->references('RoleIdentifier')
                ->on('Roles')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->primary('ParameterReference', 'RequiredRoleForParameters_PrimaryKey');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('RequiredRoleForParameters');
    }
};

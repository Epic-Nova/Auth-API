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
        Schema::create('AuthenticatableRoleReferences', function (Blueprint $table) {
            $table->ulid('AuthenticatableRoleReferenceIdentifier')
                ->comment('The identifier for the authenticatable-role reference.')
                ->primary();

            $table->uuid('AuthenticatableIdentifier')
                ->comment('The Authenticatable identifier.');

            $table->string('RoleIdentifier')
                ->comment('The role identifier.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the reference was created.')
                ->index();

            $table->unique(['AuthenticatableIdentifier', 'RoleIdentifier'], 'AuthRoleRef_UI');
            $table->index('AuthenticatableIdentifier', 'AuthRoleRef_AuthIndex');

            $table->foreign('RoleIdentifier', 'AuthRole_RoleID_FK')
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
        Schema::dropIfExists('AuthenticatableRoleReferences');
    }
};

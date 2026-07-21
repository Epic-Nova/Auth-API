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
        Schema::create('RolePermissionReferences', function (Blueprint $table) {
            $table->ulid('RolePermissionReferenceIdentifier')
                ->comment('The identifier for the role-permission reference.')
                ->primary();

            $table->string('RoleIdentifier')
                ->comment('The role identifier.');

            $table->string('PermissionIdentifier')
                ->comment('The permission identifier.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the reference was created.')
                ->index();

            $table->unique(['RoleIdentifier', 'PermissionIdentifier'], 'RolePermRef_UI');

            $table->foreign('RoleIdentifier', 'RolePerm_RoleID_FK')
                ->references('RoleIdentifier')
                ->on('Roles')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('PermissionIdentifier', 'RolePerm_PermID_FK')
                ->references('PermissionIdentifier')
                ->on('Permissions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('RolePermissionReferences');
    }
};

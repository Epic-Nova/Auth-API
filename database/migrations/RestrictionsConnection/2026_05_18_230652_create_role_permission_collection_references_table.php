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
        Schema::create('RolePermissionCollectionReferences', function (Blueprint $table) {
            $table->ulid('RolePermissionCollectionReferenceIdentifier')
                ->comment('The identifier for the role-permission-collection reference.')
                ->primary();

            $table->string('RoleIdentifier')
                ->comment('The role identifier.');

            $table->string('PermissionCollectionIdentifier')
                ->comment('The permission collection identifier.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the reference was created.')
                ->index();

            $table->unique(
                ['RoleIdentifier', 'PermissionCollectionIdentifier'],
                'RolePermCollRef_UI'
            );

            $table->foreign('RoleIdentifier', 'RolePermColl_RoleID_FK')
                ->references('RoleIdentifier')
                ->on('Roles')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign(
                'PermissionCollectionIdentifier',
                'RolePermColl_PermCollID_FK'
            )
                ->references('PermissionCollectionIdentifier')
                ->on('PermissionCollections')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('RolePermissionCollectionReferences');
    }
};

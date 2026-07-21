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
        Schema::create('AuthenticatablePermissionCollectionReferences', function (Blueprint $table) {
            $table->ulid('AuthenticatablePermissionCollectionReferenceIdentifier')
                ->comment('The identifier for the authenticatable-collection reference.')
                ->primary();

            $table->uuid('AuthenticatableIdentifier')
                ->comment('The Authenticatable identifier.');

            $table->string('PermissionCollectionIdentifier')
                ->comment('The permission collection identifier.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the reference was created.')
                ->index();

            $table->unique(
                ['AuthenticatableIdentifier', 'PermissionCollectionIdentifier'],
                'AuthPermCollRef_UI'
            );

            $table->index(
                'AuthenticatableIdentifier',
                'AuthPermCollRef_AuthIndex'
            );

            $table->foreign(
                'PermissionCollectionIdentifier',
                'AuthColl_PermCollID_FK'
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
        Schema::dropIfExists('AuthenticatablePermissionCollectionReferences');
    }
};

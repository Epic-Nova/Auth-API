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
        Schema::create('PermissionCollectionPermissionReferences', function (Blueprint $table) {
            $table->ulid('PermissionCollectionPermissionReferenceIdentifier')
                ->comment('The identifier for the collection-permission reference.')
                ->primary();

            $table->string('PermissionCollectionIdentifier')
                ->comment('The permission collection identifier.');

            $table->string('PermissionIdentifier')
                ->comment('The permission identifier.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the reference was created.')
                ->index();

            $table->unique(
                ['PermissionCollectionIdentifier', 'PermissionIdentifier'],
                'PermCollPermRef_UI'
            );

            $table->foreign('PermissionCollectionIdentifier', 'PermColl_PermCollID_FK')
                ->references('PermissionCollectionIdentifier')
                ->on('PermissionCollections')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('PermissionIdentifier', 'PermColl_PermID_FK')
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
        Schema::dropIfExists('PermissionCollectionPermissionReferences');
    }
};

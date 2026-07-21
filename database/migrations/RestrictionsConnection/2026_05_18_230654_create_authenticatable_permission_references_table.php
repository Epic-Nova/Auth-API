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
        Schema::create('AuthenticatablePermissionReferences', function (Blueprint $table) {
            $table->ulid('AuthenticatablePermissionReferenceIdentifier')
                ->comment('The identifier for the authenticatable-permission reference.')
                ->primary();

            $table->uuid('AuthenticatableIdentifier')
                ->comment('The Authenticatable identifier.');

            $table->string('PermissionIdentifier')
                ->comment('The permission identifier.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the reference was created.')
                ->index();

            $table->unique(
                ['AuthenticatableIdentifier', 'PermissionIdentifier'],
                'AuthPermRef_UI'
            );

            $table->index('AuthenticatableIdentifier', 'AuthPermRef_AuthIndex');

            $table->foreign('PermissionIdentifier', 'AuthPerm_PermID_FK')
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
        Schema::dropIfExists('AuthenticatablePermissionReferences');
    }
};

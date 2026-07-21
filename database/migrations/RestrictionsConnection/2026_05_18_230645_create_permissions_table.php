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
        Schema::create('Permissions', function (Blueprint $table) {
            $table->string('PermissionIdentifier')
                ->comment('The com identifier of the permission.')
                ->primary();

            $table->string('PermissionDescription')
                ->nullable()
                ->comment('The description of the permission.');

            $table->string('ParentPermission')
                ->nullable()
                ->comment('The com identifier of the parent permission.');

            $table->string('PermissionScope')
                ->nullable()
                ->comment('The scope identifier this permission belongs to.');

            $table->boolean('bIsActive')
                ->default(true)
                ->comment('If true, the permission is active.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the permission was created.')
                ->index();

            $table->index('ParentPermission', 'Permissions_ParentIndex');
            $table->index('PermissionScope', 'Permissions_ScopeIndex');

            $table->foreign('ParentPermission', 'Permissions_ParentPerm_FK')
                ->references('PermissionIdentifier')
                ->on('Permissions')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('PermissionScope', 'Perm_PermScope_FK')
                ->references('ScopeIdentifier')
                ->on('Scopes')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Permissions');
    }
};

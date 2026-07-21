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
        Schema::create('RoleScopeReferences', function (Blueprint $table) {
            $table->ulid('RoleScopeReferenceIdentifier')
                ->comment('The identifier for the role-scope reference.')
                ->primary();

            $table->string('RoleIdentifier')
                ->comment('The role identifier.');

            $table->string('ScopeIdentifier')
                ->comment('The scope identifier.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the reference was created.')
                ->index();

            $table->unique(['RoleIdentifier', 'ScopeIdentifier'], 'RoleScopeRef_UI');

            $table->foreign('RoleIdentifier', 'RoleScope_RoleID_FK')
                ->references('RoleIdentifier')
                ->on('Roles')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('ScopeIdentifier', 'RoleScope_ScopeID_FK')
                ->references('ScopeIdentifier')
                ->on('Scopes')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('RoleScopeReferences');
    }
};

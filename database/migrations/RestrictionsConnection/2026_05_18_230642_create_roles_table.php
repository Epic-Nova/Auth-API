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
        Schema::create('Roles', function (Blueprint $table) {
            $table->string('RoleIdentifier')
                ->comment('The com identifier of the role.')
                ->primary();

            $table->string('ParentRoleIdentifier')
                ->nullable()
                ->comment('The com identifier of the parent role.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the role was created.')
                ->index();

            $table->index('ParentRoleIdentifier', 'Roles_ParentIndex');

            $table->foreign('ParentRoleIdentifier', 'Roles_ParentRoleID_FK')
                ->references('RoleIdentifier')
                ->on('Roles')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Roles');
    }
};

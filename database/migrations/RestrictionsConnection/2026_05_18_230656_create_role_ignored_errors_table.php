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
        Schema::create('RoleIgnoredErrors', function (Blueprint $table) {
            $table->ulid('RoleIgnoredErrorIdentifier')
                ->comment('The identifier for the role ignored error entry.')
                ->primary();

            $table->string('RoleIdentifier')
                ->comment('The role identifier.');

            $table->string('ErrorIdentifier')
                ->comment('The error identifier to ignore.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time the ignore entry was created.')
                ->index();

            $table->unique(['RoleIdentifier', 'ErrorIdentifier'], 'RoleIgnoredErrors_UI');

            $table->foreign('RoleIdentifier', 'RoleIgnoredErrors_RoleID_FK')
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
        Schema::dropIfExists('RoleIgnoredErrors');
    }
};

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
        Schema::create('RoleShardReferences', function (Blueprint $table) {
            $table->ulid('RoleShardReferenceIdentifier')
                ->comment('The identifier for the role-shard reference.')
                ->primary();

            $table->string('RoleIdentifier')
                ->comment('The role identifier.');

            $table->string('ShardIdentifier')
                ->comment('The shard identifier.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the reference was created.')
                ->index();

            $table->unique(['RoleIdentifier', 'ShardIdentifier'], 'RoleShardRef_UI');

            $table->foreign('RoleIdentifier', 'RoleShard_RoleID_FK')
                ->references('RoleIdentifier')
                ->on('Roles')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('ShardIdentifier', 'RoleShard_ShardID_FK')
                ->references('ShardIdentifier')
                ->on('Shards')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('RoleShardReferences');
    }
};

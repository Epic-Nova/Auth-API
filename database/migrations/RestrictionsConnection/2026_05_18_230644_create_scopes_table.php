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
        Schema::create('Scopes', function (Blueprint $table) {
            $table->string('ScopeIdentifier')
                ->comment('The com identifier of the scope.')
                ->primary();

            $table->string('ScopeDescription')
                ->nullable()
                ->comment('The description of the scope.');

            $table->string('ScopeShard')
                ->comment('The shard identifier this scope belongs to.');

            $table->boolean('bIsActive')
                ->default(true)
                ->comment('If true, the scope is active.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the scope was created.')
                ->index();

            $table->index('ScopeShard', 'Scopes_ShardIndex');

            $table->foreign('ScopeShard', 'Scopes_Shard_FK')
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
        Schema::dropIfExists('Scopes');
    }
};

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
        Schema::create('Shards', function (Blueprint $table) {
            $table->string('ShardIdentifier')
                ->comment('The com identifier of the shard.')
                ->primary();

            $table->string('ParentShardIdentifier')
                ->nullable()
                ->comment('The com identifier of the parent shard.');

            $table->longText('ShardDescription')
                ->nullable()
                ->comment('The description of the shard.');

            $table->longText('ShardPublicKey')
                ->comment('The public key of the shard.');

            $table->boolean('bEnableParentShardInheritance')
                ->default(true)
                ->comment('If true, this shard inherits access from its parent.');

            $table->boolean('bApplicationProgrammingInterface')
                ->default(true)
                ->comment('If true, this shard is an API in the network.');

            $table->boolean('bIsActive')
                ->default(true)
                ->comment('If true, the shard is active.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the shard was created.')
                ->index();

            $table->index('ParentShardIdentifier', 'Shards_ParentIndex');

            $table->foreign('ParentShardIdentifier', 'Shards_ParentShardID_FK')
                ->references('ShardIdentifier')
                ->on('Shards')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Shards');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Shards', function (Blueprint $table) {

            $table->string('ShardIdentifier')
                ->comment('The com identifier of the shard, containing the name of the shard in the last part of the identifier.');

            $table->string('ParentShardIdentifier')
                ->nullable()
                ->comment('The com identifier of the parent shard, containing the name of the shard in the last part of the identifier.');

            $table->longText('ShardDescription')
                ->nullable()
                ->comment('The description of the shard.');

            $table->longText('ShardPublicKey')
                ->comment('The public key of the shard.');

            $table->boolean('bEnableParentShardInheritance')
                ->default(true)
                ->comment('If true, then the shard is active for inheritance from the parent shard.
                    This means that when the parent shard of this shard is defined in the bearer token, then the user has also access to this shard.');

            $table->boolean('bApplicationProgrammingInterface')
                ->default(true)
                ->comment('If true, then the shard is an API in the network.');

            $table->boolean('bIsActive')
                ->default(true)
                ->comment('If true, then the shard is active and can be used.');

            $table->primary('ShardIdentifier', 'Shards_PrimaryIndex');
            $table->index('ParentShardIdentifier', 'Shards_Index');

        });
    }

    public function down()
    {
        Schema::dropIfExists('Shards');
    }
};

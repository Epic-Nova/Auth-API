<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('RoleShardReferences', function (Blueprint $table) {
            $table->string('RoleIdentifier')
                ->comment('The RoleIdentifier of the Role that this data belongs to.');

            $table->string('ShardIdentifier')
                ->comment('The ShardIdentifier of the Shard that this data belongs to.');

            $table->foreign('RoleIdentifier', 'RoleShard_RoleIdentifier_Foreign')
                ->references('RoleIdentifier')
                ->on('Roles')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('ShardIdentifier', 'RoleShard_ShardIdentifier_Foreign')
                ->references('ShardIdentifier')
                ->on('Shards')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

        });
    }

    public function down()
    {
        Schema::dropIfExists('RoleShardReferences');
    }
};

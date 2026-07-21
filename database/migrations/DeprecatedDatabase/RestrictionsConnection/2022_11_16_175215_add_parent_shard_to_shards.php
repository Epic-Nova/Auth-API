<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('Shards', function (Blueprint $table) {

            $table->foreign('ParentShardIdentifier', 'Shards_ParentShardIdentifier_Foreign')
                ->references('ShardIdentifier')
                ->on('Shards')
                ->nullOnDelete()
                ->cascadeOnUpdate();

        });
    }
};

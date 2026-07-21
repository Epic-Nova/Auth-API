<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('Scopes', function (Blueprint $table) {

            $table->foreign('ScopeShard', 'Scopes_ScopeShard_Foreign')
                ->references('ShardIdentifier')
                ->on('Shards')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Scopes', function (Blueprint $table) {

            $table->string('ScopeIdentifier')
                ->comment('The com identifier of the scope, containing the name of the scope in the last part of the identifier.');

            $table->string('ScopeDescription')
                ->nullable()
                ->comment('The description of the scope.');

            $table->string('ScopeShard')
                ->comment('The com identifier of the shard witch this scope is attached to, containing the name of the shard in the last part of the identifier.');

            $table->boolean('bIsActive')
                ->default(true)
                ->comment('If true, then the scope is active and can be used.');

            $table->primary('ScopeIdentifier', 'Scopes_PrimaryIndex');
            $table->index('ScopeShard', 'Scopes_Index');

        });
    }

    public function down()
    {
        Schema::dropIfExists('Scopes');
    }
};

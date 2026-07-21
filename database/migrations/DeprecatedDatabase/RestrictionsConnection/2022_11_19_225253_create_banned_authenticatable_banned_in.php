<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('BannedAuthenticatableBannedIn', function (Blueprint $table) {

            $table->foreignUuid('BanIdentifier')
                ->comment('The BanIdentifier of the Ban that this data belongs to.')
                ->constrained('BannedAuthenticatable', 'BanIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->enum('BannedInType', ['Application', 'Shard'])
                ->comment('Where this Authenticatable is banned in.
                    Application: The Authenticatable is banned in the one Application.
                    Shard: The Authenticatable is banned in a specific Shard.');

            $table->uuid('BannedIn')
                ->comment('The uuid of the Application or Shard that this Authenticatable is banned in.');

        });
    }

    public function down()
    {
        Schema::dropIfExists('BannedAuthenticatableBannedIn');
    }
};

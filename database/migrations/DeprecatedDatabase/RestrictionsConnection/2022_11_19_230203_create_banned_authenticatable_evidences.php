<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('BannedAuthenticatableEvidences', function (Blueprint $table) {

            $table->foreignUuid('BanIdentifier')
                ->comment('The BanIdentifier of the Ban that this data belongs to.')
                ->constrained('BannedAuthenticatable', 'BanIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('EvidenceURI', 1024)
                ->comment('The URI of the evidence that was used to ban the Authenticatable.');

        });
    }

    public function down()
    {
        Schema::dropIfExists('BannedAuthenticatableEvidences');
    }
};

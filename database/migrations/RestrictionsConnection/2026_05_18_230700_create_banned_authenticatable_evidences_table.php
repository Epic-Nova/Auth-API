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
        Schema::create('BannedAuthenticatableEvidences', function (Blueprint $table) {
            $table->ulid('BannedAuthenticatableEvidenceIdentifier')
                ->comment('The identifier for the ban evidence.')
                ->primary();

            $table->uuid('BanIdentifier')
                ->comment('The BanIdentifier that this evidence belongs to.');

            $table->string('EvidenceURI', 1024)
                ->comment('The URI of the evidence used to ban the Authenticatable.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time the evidence was recorded.')
                ->index();

            $table->index('BanIdentifier', 'BannedAuthEvidences_BanIndex');

            $table->foreign('BanIdentifier', 'BannedAuthEvidences_BanID_FK')
                ->references('BanIdentifier')
                ->on('BannedAuthenticatable')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('BannedAuthenticatableEvidences');
    }
};

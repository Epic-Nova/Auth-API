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
        Schema::create('BannedAuthenticatable', function (Blueprint $table) {
            $table->uuid('BanIdentifier')
                ->comment('A unique identifier for the ban.')
                ->primary();

            $table->uuid('BannedAuthenticatable')
                ->comment('The AuthenticatableIdentifier that is banned.');

            $table->uuid('BannedBy')
                ->comment('The AuthenticatableIdentifier that issued the ban.');

            $table->longText('BanReason')
                ->comment('The reason for the ban.');

            $table->enum('BanType', ['Permanent', 'Temporary', 'PermanentWithAppeal', 'TemporaryWithAppeal', 'Shadow'])
                ->comment('The type of ban.');

            $table->enum('BanStatus', ['Active', 'Appealed', 'Expired'])
                ->default('Active')
                ->comment('The status of the ban.');

            $table->timestamp('BannedAt')
                ->useCurrent()
                ->comment('When the ban was created.')
                ->index();

            $table->timestamp('BanExpires')
                ->nullable()
                ->comment('When the ban expires, if applicable.')
                ->index();

            $table->timestamp('UpdatedAt')
                ->useCurrent()
                ->useCurrentOnUpdate()
                ->comment('The date and time when the ban was last updated.');

            $table->index(['BannedAuthenticatable', 'BannedBy'], 'BannedAuthenticatable_Index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('BannedAuthenticatable');
    }
};

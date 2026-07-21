<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('BannedAuthenticatable', function (Blueprint $table) {

            $table->uuid('BanIdentifier')
                ->comment('A unique identifier for the ban.');

            $table->foreignUuid('BannedAuthenticatable')
                ->comment('The AuthenticatableIdentifier of the Authenticatable that is banned.')
                ->constrained('Auth.Authenticatable', 'AuthenticatableIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignUuid('BannedBy')
                ->comment('The AuthenticatableIdentifier of the Authenticatable that banned the Authenticatable.')
                ->constrained('Auth.Authenticatable', 'AuthenticatableIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->longText('BanReason')
                ->comment('The reason for the ban.');

            $table->enum('BanType', ['Permanent', 'Temporary', 'PermanentWithAppeal', 'TemporaryWithAppeal', 'Shadow'])
                ->comment('The type of ban.
                    Permanent: The ban is permanent and cannot be appealed.
                    Temporary: The ban is temporary and will expire after a set amount of time.
                    PermanentWithAppeal: The ban is permanent, but can be appealed.
                    TemporaryWithAppeal: The ban is temporary and will expire after a set amount of time, but can be appealed.
                    Shadow: The ban restricts the account in activity, visibility and interactivity but can still be used.');

            $table->enum('BanStatus', ['Active', 'Appealed', 'Expired'])
                ->default('Active')
                ->comment('The status of the ban.
                    Active: The ban is active and the Authenticatable is banned.
                    Appealed: The ban has been appealed and is pending review.
                    Expired: The ban has expired and the Authenticatable is no longer banned.');

            $table->timestamp('BannedAt')
                ->useCurrent()
                ->comment('The timestamp of when the Authenticatable was banned.');

            $table->timestamp('BanExpires')
                ->nullable()
                ->comment('The timestamp of when the ban expires. If the ban is permanent, this will be null.');

            $table->timestamp('UpdatedAt')
                ->useCurrent()
                ->comment('The timestamp of when the Ban was last updated.');

            $table->primary('BanIdentifier', 'BannedAuthenticatable_PrimaryIndex');
            $table->index(['BannedAuthenticatable', 'BannedBy'], 'BannedAuthenticatable_Index');

        });
    }

    public function down()
    {
        Schema::dropIfExists('BannedAuthenticatable');
    }
};

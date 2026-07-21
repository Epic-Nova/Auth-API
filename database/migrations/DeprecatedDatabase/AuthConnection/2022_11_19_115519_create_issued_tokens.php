<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('IssuedTokens', function (Blueprint $table) {

            $table->string('IssuedTokenJTI', 128)
                ->comment('The JTI of the issued Bearer Token.');

            $table->string('IssuedRefreshTokenJTI', 128)
                ->nullable(false)
                ->comment('The JTI of the issued Refresh Token.');

            $table->longText('TokenPayload')
                ->nullable(false)
                ->comment('The payload of the issued Bearer Token.');

            $table->enum('TokenState', ['Active', 'Expired', 'Revoked'])
                ->default('Active')
                ->comment('The state of the issued Bearer Token. The token state changed to Expired when this token is tried to be used after the expiry date.
                    Active: The token is active and can be used.
                    Expired: The token is expired and cannot be used.
                    Revoked: The token is revoked and cannot be used.');

            $table->timestamp('TokenExpiry')
                ->nullable(false)
                ->comment('The expiry of the issued Bearer Token.');

            $table->foreignUuid('IssuedToAuthenticatable')
                ->comment('The Authenticatable that the Bearer Token was issued to.')
                ->constrained('Authenticatable', 'AuthenticatableIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->primary('IssuedTokenJTI', 'IssuedTokens_PrimaryIndex');
            $table->unique('IssuedRefreshTokenJTI', 'IssuedTokens_UniqueIndex');

        });
    }

    public function down()
    {
        Schema::dropIfExists('IssuedTokens');
    }
};

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
        Schema::create('IssuedTokens', function (Blueprint $table) {
            $table->string('IssuedTokenJTI', 128)
                ->comment('The JTI of the issued bearer token.')
                ->primary();

            $table->string('IssuedRefreshTokenJTI', 128)
                ->comment('The JTI of the issued refresh token.')
                ->unique('IssuedTokens_UniqueIndex');

            $table->longText('TokenPayload')
                ->comment('The payload of the issued bearer token.');

            $table->enum('TokenState', ['Active', 'Expired', 'Revoked'])
                ->default('Active')
                ->comment('The current state of the token.')
                ->index();

            $table->timestamp('TokenExpiry')
                ->comment('The expiry of the issued bearer token.')
                ->index();

            $table->foreignUuid('IssuedToAuthenticatable')
                ->comment('The Authenticatable that the token was issued to.')
                ->constrained('Authenticatable', 'AuthenticatableIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->timestamp('IssuedAt')
                ->useCurrent()
                ->comment('The date and time the token was issued.')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('IssuedTokens');
    }
};

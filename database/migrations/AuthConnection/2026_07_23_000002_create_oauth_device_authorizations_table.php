<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'AuthConnection';

    public function up(): void
    {
        Schema::connection($this->connection)->create('OAuthDeviceAuthorizations', function (Blueprint $table): void {
            $table->uuid('OAuthDeviceAuthorizationIdentifier')->primary();
            $table->uuid('OAuthClientIdentifier')->index();
            $table->string('DeviceCodeHash', 64)->unique();
            $table->string('UserCodeHash', 64)->unique();
            $table->json('RequestedScopes');
            $table->json('RequestedAudiences');
            $table->enum('AuthorizationState', ['Pending', 'Approved', 'Denied', 'Consumed', 'Expired'])->default('Pending')->index();
            $table->uuid('ApprovedByAuthenticatable')->nullable()->index();
            $table->timestamp('ExpiresAt')->index();
            $table->timestamp('ApprovedAt')->nullable();
            $table->timestamp('ConsumedAt')->nullable();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('OAuthDeviceAuthorizations');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'AuthConnection';

    public function up(): void
    {
        Schema::connection($this->connection)->create('OAuthClients', function (Blueprint $table): void {
            $table->uuid('OAuthClientIdentifier')->primary();
            $table->string('ClientName', 128)->unique();
            $table->string('ClientSecretHash');
            $table->json('AllowedScopes');
            $table->json('AllowedAudiences');
            $table->unsignedInteger('AccessTokenTtlSeconds')->default(600);
            $table->boolean('IsActive')->default(true)->index();
            $table->timestamp('CreatedAt')->useCurrent();
            $table->timestamp('UpdatedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('OAuthClients');
    }
};

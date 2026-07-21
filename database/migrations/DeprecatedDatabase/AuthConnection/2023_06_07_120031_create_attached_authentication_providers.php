<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AttachedAuthenticationProviders', function (Blueprint $table) {

            $table->uuid('AuthenticatableIdentifier')
                ->comment('The reference to the AuthenticatableResource.');

            $table->foreign('AuthenticatableIdentifier', 'AttachedProvider_AuthenticatableIdentifier_Foreign')
                ->references('AuthenticatableIdentifier')
                ->on('Authenticatable')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->ulid('AuthenticationProviderIdentifier')
                ->comment('The reference to the AuthenticationProvider.');

            $table->foreign('AuthenticationProviderIdentifier', 'AttachedProvider_AuthenticationProviderIdentifier_Foreign')
                ->references('AuthenticationProviderIdentifier')
                ->on('AuthenticationProviders')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->boolean('bIsPrimary')
                ->default(false)
                ->comment('Defines if this attached authentication provider is the primary one for the account.
                    When this flag is false, every authentication provider is considered a multi-factor authentication provider.');

            $table->longText('ProviderData')
                ->nullable()
                ->comment('The data that is used to authenticate the account.');

            $table->enum('ProviderState', ['Unverified', 'Verified', 'Disabled'])
                ->default('Unverified')
                ->comment('The state of the authentication provider.
                    Unverified: The authentication provider is not verified yet.
                    Verified: The authentication provider is verified.
                    Disabled: The authentication provider is disabled and will not be considered for authentication.');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('AttachedAuthenticationProviders');
    }
};

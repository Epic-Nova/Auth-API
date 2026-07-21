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
        Schema::create('AttachedAuthenticationProviders', function (Blueprint $table) {
            $table->ulid('AttachedAuthenticationProviderIdentifier')
                ->comment('The identifier of the attached provider.')
                ->primary();

            $table->foreignUuid('AuthenticatableIdentifier')
                ->comment('The reference to the Authenticatable resource.')
                ->constrained('Authenticatable', 'AuthenticatableIdentifier', 'AttAP_AuthID_FK')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignUlid('AuthenticationProviderIdentifier')
                ->comment('The reference to the AuthenticationProvider.')
                ->constrained('AuthenticationProviders', 'AuthenticationProviderIdentifier', 'AttAP_Provider_FK')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->boolean('bIsPrimary')
                ->default(false)
                ->comment('If true, this is the primary provider.');

            $table->longText('ProviderData')
                ->nullable()
                ->comment('The data used to authenticate the account.');

            $table->enum('ProviderState', ['Unverified', 'Verified', 'Disabled'])
                ->default('Unverified')
                ->comment('The state of the attached provider.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time the provider was attached.')
                ->index();

            $table->unique(
                ['AuthenticatableIdentifier', 'AuthenticationProviderIdentifier'],
                'AttachedAuthenticationProviders_UniqueIndex'
            );

            $table->index(['AuthenticatableIdentifier', 'bIsPrimary'], 'AttAP_Index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('AttachedAuthenticationProviders');
    }
};

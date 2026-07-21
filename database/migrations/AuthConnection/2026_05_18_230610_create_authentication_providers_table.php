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
        Schema::create('AuthenticationProviders', function (Blueprint $table) {
            $table->ulid('AuthenticationProviderIdentifier')
                ->comment('The identifier of the authentication provider.')
                ->primary();

            $table->string('ProviderFriendlyIdentifier', 128)
                ->default('com.epicnova.authentication_provider.')
                ->comment('The provider reference identifier.')
                ->unique('AuthenticationProviders_UniqueIndex');

            $table->longText('ProviderDescription')
                ->comment('The description of the authentication provider.');

            $table->uuid('ProviderCreator')
                ->nullable()
                ->comment('The reference to the owner of this provider.');

            $table->enum('ProviderLocation', ['CDN', 'Remote'])
                ->default('CDN')
                ->comment('The location where the provider is hosted.');

            $table->string('ProviderGitRepository', 512)
                ->nullable()
                ->comment('The git repository of the authentication provider.');

            $table->string('ProviderGitVersion', 128)
                ->nullable()
                ->comment('The version tag of the provider.');

            $table->enum('ProviderState', ['CreatorOnly', 'UnderReview', 'PublicAvailable'])
                ->default('CreatorOnly')
                ->comment('The provider availability state.');

            $table->boolean('bCanUserLoginWithProvider')
                ->default(false)
                ->comment('If true, this provider can be used for login.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time the provider was created.')
                ->index();

            $table->timestamp('UpdatedAt')
                ->useCurrent()
                ->useCurrentOnUpdate()
                ->comment('The date and time the provider was last updated.');

            $table->foreign('ProviderCreator', 'AuthProv_ProviderCreator_FK')
                ->references('AuthenticatableIdentifier')
                ->on('Authenticatable')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('AuthenticationProviders');
    }
};

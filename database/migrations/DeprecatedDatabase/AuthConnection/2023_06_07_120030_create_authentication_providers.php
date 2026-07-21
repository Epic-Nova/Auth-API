<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AuthenticationProviders', function (Blueprint $table) {

            $table->ulid('AuthenticationProviderIdentifier')
                ->comment('The identifier of the authentication provider.');

            $table->string('ProviderFriendlyIdentifier', 128)
                ->nullable(false)
                ->default('com.epicnova.authentication_provider.')
                ->comment('Represents the BaseProviderReference that includes various implementations:
                    LoginScreenConfiguration defines the login screen appearance
                    AuthenticatorAPIProvider creates API endpoints
                    RequirementsValidator checks user requirements for the login type
                    StateValidator initiates authentication for a specific type (e.g. sending a verification code)
                    Authenticator validates user credentials.

                    When an account has multiple associated values (e.g. phone numbers, email addresses), the Authenticator uses the primary one and offers a dropdown for user selection.

                    Providers can be used not only for login but also for additional authentication, such as identity verification when changing passwords.
                    In this case the AttachableData table defines if there is a need for multi factor authentication.');

            $table->longText('ProviderDescription')
                ->nullable(false)
                ->comment('The description of the authentication provider.');

            $table->enum('ProviderLocation', ['CDN', 'Remote'])
                ->default('CDN')
                ->comment('The location where to expect the authentication provider.
                    CDN: The authentication provider is stored in the CDN.
                    Remote: The authentication provider is stored on a remote server.');

            $table->string('ProviderGitRepository', 512)
                ->nullable(true)
                ->comment('The git repository of the authentication provider.');

            $table->string('ProviderGitVersion', 128)
                ->nullable(true)
                ->comment('The version tag in the git repository of the authentication provider to use.');

            $table->enum('ProviderState', ['CreatorOnly', 'UnderReview', 'PublicAvailable'])
                ->default('CreatorOnly')
                ->comment('The state of the authentication provider.
                    CreatorOnly: The authentication provider is only available to the creator.
                    UnderReview: The authentication provider is under review for public availability.
                    PublicAvailable: The authentication provider is available to the public.');

            $table->boolean('bCanUserLoginWithProvider')
                ->default(false)
                ->comment('Defines if the user can login with this authentication provider.
                    When this flag is false, the authentication provider is only used for additional authentication.');

            $table->primary('AuthenticationProviderIdentifier', 'AuthenticationProviders_PrimaryIndex');
            $table->unique('ProviderFriendlyIdentifier', 'AuthenticationProviders_UniqueIndex');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('AuthenticationProviders');
    }
};

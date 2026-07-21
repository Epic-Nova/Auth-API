<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AuthenticationProvidersParameters', function (Blueprint $table) {

            $table->ulid('AuthenticationProviderIdentifier')
                ->comment('The reference to the AuthenticationProvider that this parameter is for.');

            $table->foreign('AuthenticationProviderIdentifier', 'ProvidersParameters_AuthenticationProviderIdentifier_Foreign')
                ->references('AuthenticationProviderIdentifier')
                ->on('AuthenticationProviders')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('ParameterName', 128)
                ->comment('The name of the parameter.');

            $table->string('AttachableDataName')
                ->nullable()
                ->comment('The AttachableDataName of the AttachableData that this references the value of.');

            $table->foreign('AttachableDataName', 'ProvidersParameters_AttachableDataName_Foreign')
                ->references('AttachableDataName')
                ->on('AttachableData')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->boolean('bIsParameterRequired')
                ->default(true)
                ->comment('If true, then this parameter is required for the authentication provider to work.');

            $table->set('ParameterForType', ['RequirementsValidator', 'StateValidator', 'Authenticator'])
                ->default('Authenticator')
                ->comment('The type of Authenticator that this parameter is for.
                    RequirementsValidator: The parameter is for the RequirementsValidator.
                    StateValidator: The parameter is for the StateValidator.
                    Authenticator: The parameter is for the Authenticator.');

            $table->enum('ParameterStoreIn', ['AuthorisationHeader', 'Body',  'Params', 'Header'])
                ->default('Body')
                ->comment('Where the parameter in the request should be stored.
                    AuthorisationHeader: The parameter should be stored in the Authorisation header.
                    Body: The parameter should be stored in the body of the request.
                    Params: The parameter should be stored in the params of the request.
                    Header: The parameter should be stored in the header of the request.');

            $table->index('ParameterName', 'ProvidersParameters_Index');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('AuthenticationProvidersParameters');
    }
};

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
        Schema::create('AuthenticationProvidersParameters', function (Blueprint $table) {
            $table->ulid('AuthenticationProviderParameterIdentifier')
                ->comment('The identifier of the provider parameter.')
                ->primary();

            $table->foreignUlid('AuthenticationProviderIdentifier')
                ->comment('The reference to the AuthenticationProvider.')
                ->constrained('AuthenticationProviders', 'AuthenticationProviderIdentifier', 'AuthProvParams_ProvID_FK')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('ParameterName', 128)
                ->comment('The name of the parameter.')
                ->index('AuthenticationProviderParameters_NameIndex');

            $table->string('AttachableDataName', 64)
                ->nullable()
                ->comment('The AttachableDataName that this parameter references.');

            $table->foreign('AttachableDataName', 'AuthProvParams_AttachData_FK')
                ->references('AttachableDataName')
                ->on('AttachableData')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->boolean('bIsParameterRequired')
                ->default(true)
                ->comment('If true, the parameter is required.');

            $table->set('ParameterForType', ['RequirementsValidator', 'StateValidator', 'Authenticator'])
                ->default('Authenticator')
                ->comment('The provider component that consumes this parameter.');

            $table->enum('ParameterStoreIn', ['AuthorisationHeader', 'Body', 'Params', 'Header'])
                ->default('Body')
                ->comment('Where the parameter should be stored in requests.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time the parameter was created.')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('AuthenticationProvidersParameters');
    }
};

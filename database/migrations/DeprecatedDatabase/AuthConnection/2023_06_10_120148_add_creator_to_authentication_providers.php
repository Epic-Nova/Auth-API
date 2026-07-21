<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('AuthenticationProviders', function (Blueprint $table) {

            $table->uuid('ProviderCreator')
                ->nullable()
                ->after('ProviderDescription')
                ->comment('The reference to owner of this AuthenticationProvider.');

            $table->foreign('ProviderCreator', 'AuthenticationProviders_ProviderCreator_Foreign')
                ->references('AuthenticatableIdentifier')
                ->on('Authenticatable')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

        });
    }

    public function down(): void
    {
        Schema::table('AuthenticationProviders', function (Blueprint $table) {
            //
        });
    }
};

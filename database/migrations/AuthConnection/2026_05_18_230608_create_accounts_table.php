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
        Schema::create('Accounts', function (Blueprint $table) {
            $table->foreignUuid('AccountIdentifier')
                ->comment('The reference to the Authenticatable resource.')
                ->constrained('Authenticatable', 'AuthenticatableIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->primary();

            $table->enum('Region', ['Europe', 'NorthAmerica', 'SouthAmerica', 'Asia', 'Africa', 'Australia'])
                ->comment('The region where this account was created. Used to determine the primary location of the account. (CDN, Edge Servers)')
                ->index();

            $table->timestamp('ClosedAt')
                ->nullable()
                ->comment('The date and time the account was closed.')
                ->index();

            $table->timestamp('PurgeAfter')
                ->nullable()
                ->comment('The date and time when account data may be purged.')
                ->index();

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time the account was created.')
                ->index();

            $table->timestamp('UpdatedAt')
                ->useCurrent()
                ->useCurrentOnUpdate()
                ->comment('The date and time the account was last updated.');

            $table->timestamp('DeletedAt')
                ->nullable()
                ->comment('Soft-delete marker for retention workflows.')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Accounts');
    }
};

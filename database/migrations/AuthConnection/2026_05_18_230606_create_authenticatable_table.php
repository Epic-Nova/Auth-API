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
        Schema::create('Authenticatable', function (Blueprint $table) {
            $table->uuid('AuthenticatableIdentifier')
                ->comment('The identifier of the authenticatable resource.')
                ->primary();

            $table->enum('AuthenticatableType', ['Account', 'Application'])
                ->default('Account')
                ->comment('The type of the authenticatable resource.')
                ->index();

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time the authenticatable was created.')
                ->index();

            $table->timestamp('UpdatedAt')
                ->useCurrent()
                ->useCurrentOnUpdate()
                ->comment('The date and time the authenticatable was last updated.');

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
        Schema::dropIfExists('Authenticatable');
    }
};

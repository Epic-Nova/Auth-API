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
        Schema::create('AuthenticatableUpdateLogs', function (Blueprint $table) {
            $table->ulid('AuthenticatableUpdateLogIdentifier')
                ->comment('The identifier of this update log entry.')
                ->primary();

            $table->foreignUuid('AuthenticatableIdentifier')
                ->comment('The Authenticatable that was updated.')
                ->constrained('Authenticatable', 'AuthenticatableIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignUuid('UpdateMadeBy')
                ->comment('The Authenticatable that made the update.')
                ->constrained('Authenticatable', 'AuthenticatableIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('UpdatedTable')
                ->comment('The table that was updated.');

            $table->string('UpdatedField')
                ->comment('The field that was updated.');

            $table->longText('OldValue')
                ->nullable()
                ->comment('The old value of the field.');

            $table->longText('NewValue')
                ->comment('The new value of the field.');

            $table->boolean('bAreValuesSecret')
                ->default(false)
                ->comment('If true, values are secret and should be protected.');

            $table->timestamp('UpdatedAt')
                ->useCurrent()
                ->comment('The date and time when this change was performed.')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('AuthenticatableUpdateLogs');
    }
};

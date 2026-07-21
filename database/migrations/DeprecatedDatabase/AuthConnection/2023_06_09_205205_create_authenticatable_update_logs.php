<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AuthenticatableUpdateLogs', function (Blueprint $table) {

            $table->foreignUuid('AuthenticatableIdentifier')
                ->comment('The reference to the AuthenticatableResource.')
                ->constrained('Authenticatable', 'AuthenticatableIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignUuid('UpdateMadeBy')
                ->comment('The reference to the AuthenticatableResource that made the update.')
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
                ->comment('If the values are secret, they will be hashed.');

            $table->timestamp('UpdatedAt')
                ->useCurrent()
                ->comment('The date and time when this change was performed.');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('AuthenticatableUpdateLogs');
    }
};

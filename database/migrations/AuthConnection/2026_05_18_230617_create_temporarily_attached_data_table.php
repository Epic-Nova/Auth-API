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
        Schema::create('TemporarilyAttachedData', function (Blueprint $table) {
            $table->ulid('TemporarilyAttachedDataIdentifier')
                ->comment('The identifier of the temporary attachment.')
                ->primary();

            $table->string('AttachedOnIdentifier', 36)
                ->comment('The identifier of the entity this data is attached to.');

            $table->enum('AttachedOn', ['Authenticatable', 'Account', 'Profile'])
                ->default('Authenticatable')
                ->comment('The entity type this data is attached to.');

            $table->string('AttachedDataType', 64)
                ->comment('The AttachableDataName this row references.');

            $table->longText('TemporarilyAttachedData')
                ->comment('The staged value pending approval or MFA.');

            $table->timestamp('ExpiresAt')
                ->nullable()
                ->comment('When this staged data should expire.')
                ->index();

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time this staged data was created.')
                ->index();

            $table->foreign('AttachedDataType', 'TempAttData_AttDataType_FK')
                ->references('AttachableDataName')
                ->on('AttachableData')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->index(['AttachedOnIdentifier', 'AttachedOn'], 'TemporarilyAttachedData_LookupIndex');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('TemporarilyAttachedData');
    }
};

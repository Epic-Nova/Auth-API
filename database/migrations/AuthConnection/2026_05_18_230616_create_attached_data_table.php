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
        Schema::create('AttachedData', function (Blueprint $table) {
            $table->ulid('AttachedDataIdentifier')
                ->comment('The identifier of the attached data row.')
                ->primary();

            $table->string('AttachedOnIdentifier', 36)
                ->comment('The identifier of the entity this data is attached to.');

            $table->enum('AttachedOn', ['Authenticatable', 'Account', 'Profile', 'AuthenticationProvider'])
                ->default('Authenticatable')
                ->comment('The entity type this data is attached to.');

            $table->string('AttachedDataSharedType', 32)
                ->nullable()
                ->comment('The entity shared type this data is attached to.');

            $table->string('AttachedDataType', 64)
                ->comment('The AttachableDataName this row references.');

            $table->boolean('bIsPrimary')
                ->default(false)
                ->comment('If true, this is the primary value for its type.');

            $table->longText('AttachedData')
                ->comment('The stored value for this attached data.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time this data was attached.')
                ->index();

            $table->timestamp('UpdatedAt')
                ->useCurrent()
                ->useCurrentOnUpdate()
                ->comment('The date and time this data was last updated.');

            $table->foreign('AttachedDataType', 'AttData_AttDataType_FK')
                ->references('AttachableDataName')
                ->on('AttachableData')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->unique(
                ['AttachedOnIdentifier', 'AttachedOn', 'AttachedDataType'],
                'AttachedData_UI'
            );

            $table->index(
                [
                    'AttachedOnIdentifier',
                    'AttachedOn',
                    'bIsPrimary',
                    'AttachedDataType',
                    'AttachedDataSharedType'], 'AttachedData_Index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('AttachedData');
    }
};

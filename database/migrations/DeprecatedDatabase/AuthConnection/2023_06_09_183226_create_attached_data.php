<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AttachedData', function (Blueprint $table) {

            $table->uuid('AttachedOnIdentifier')
                ->comment('The identifier of the entity that this AttachedData is attached to.');

            $table->enum('AttachedOn', ['Authenticatable', 'Account', 'Profile'])
                ->default('Authenticatable')
                ->comment('The type of entity that this AttachedData is attached to.
                    Authenticatable: The AttachedData is attached to an Authenticatable entity.
                    Account: The AttachedData is attached to an Account entity.
                    Profile: The AttachedData is attached to a Profile entity.');

            $table->string('AttachedDataType')
                ->comment('The AttachableDataName of the AttachableData that this references the value of.');

            $table->foreign('AttachedDataType', 'AttachedData_AttachedDataType_Foreign')
                ->references('AttachableDataName')
                ->on('AttachableData')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->boolean('bIsPrimary')
                ->default(false)
                ->comment('If true, then this AttachedData is the primary AttachedData for the AttachableData.');

            $table->longText('AttachedData')
                ->nullable(false)
                ->comment('The value of the AttachedData.');

            $table->index('AttachedOn', 'AttachedData_Index');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('AttachedData');
    }
};

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
        Schema::create('AttachableDataStates', function (Blueprint $table) {
            $table->ulid('AttachableDataStateIdentifier')
                ->comment('The identifier of the attachable data state.')
                ->primary();

            $table->string('AttachableDataParameterName', 64)
                ->comment('The attachable data that defines the state.');

            $table->string('AttachableDataEffectedParameterName', 64)
                ->comment('The attachable data that is affected by the state.');

            $table->enum(
                'AttachableDataState',
                ['Visible', 'Hidden', 'HiddenButFindable', 'Disabled', 'DisabledButFindable']
            )
                ->comment('The state applied to the affected attachable data.');

            $table->enum('StateCondition', ['WhenAttachableDataPresent', 'WhenAttachableDataNotPresent'])
                ->comment('The condition required to apply the state.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time the state rule was created.')
                ->index();

            $table->foreign('AttachableDataParameterName', 'DataStates_AttDataParameterName_FK')
                ->references('AttachableDataName')
                ->on('AttachableData')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('AttachableDataEffectedParameterName', 'DataStates_AttDataEffecParamName_FK')
                ->references('AttachableDataName')
                ->on('AttachableData')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->unique(
                ['AttachableDataParameterName', 'AttachableDataEffectedParameterName', 'StateCondition'],
                'AttachableDataStates_UniqueIndex'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('AttachableDataStates');
    }
};

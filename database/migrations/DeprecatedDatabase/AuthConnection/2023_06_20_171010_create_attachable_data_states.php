<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AttachableDataStates', function (Blueprint $table) {

            $table->string('AttachableDataParameterName')
                ->comment('The name of the attachable data that this state is attached to.');

            $table->foreign('AttachableDataParameterName', 'DataStates_AttachableDataParameterName_Foreign')
                ->references('AttachableDataName')
                ->on('AttachableData')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('AttachableDataEffectedParameterName')
                ->comment('The name of the attachable data that is effected by this state.');

            $table->foreign('AttachableDataEffectedParameterName', 'DataStates_AttachableDataEffectedParameterName_Foreign')
                ->references('AttachableDataName')
                ->on('AttachableData')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->enum('AttachableDataState', ['Visible', 'Hidden', 'HiddenButFindable', 'Disabled', 'DisabledButFindable'])
                ->comment('The state of the attachable data.
                    Visible: The attachable data is visible in the AttachableFor namespaces.
                    Hidden: The attachable data is hidden in the AttachableFor namespaces, but can be attached with a api call.
                    HiddenButFindable: The attachable data is hidden in the AttachableFor namespaces, but can be attached with a api call and can be found with a search.
                    Disabled: The attachable data is disabled and can not be attached or updated.
                    DisabledButFindable: The attachable data is disabled and can not be attached or updated, but can be found with a search. If already attached it is grayed out in the AttachableFor namespaces.');

            $table->enum('StateCondition', ['WhenAttachableDataPresent', 'WhenAttachableDataNotPresent'])
                ->comment('The condition that must be met for this state to be applied.
                    WhenAttachableDataPresent: The state is applied when the attachable data is present.
                    WhenAttachableDataNotPresent: The state is applied when the attachable data is not present.');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('AttachableDataStates');
    }
};

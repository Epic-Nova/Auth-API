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
        Schema::create('ApprovalEntityParameters', function (Blueprint $table) {
            $table->ulid('ApprovalEntityParameterIdentifier')
                ->comment('The identifier of the approval entity parameter.')
                ->primary();

            $table->foreignUlid('ApprovalEntityIdentifier')
                ->comment('The approval entity this parameter belongs to.')
                ->constrained('ApprovalEntities', 'ApprovalEntityIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('AttachableDataValueReference', 64)
                ->comment('The AttachableDataName referenced by this parameter.');

            $table->foreign('AttachableDataValueReference', 'ApprovEnt_AttDataValueRef_FK')
                ->references('AttachableDataName')
                ->on('AttachableData')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->enum('ParameterStoreIn', ['AuthorisationHeader', 'Body', 'Params', 'Header'])
                ->default('Body')
                ->comment('Where the parameter should be stored in requests.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time the parameter was created.')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ApprovalEntityParameters');
    }
};

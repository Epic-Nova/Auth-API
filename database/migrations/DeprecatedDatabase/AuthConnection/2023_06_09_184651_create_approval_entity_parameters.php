<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ApprovalEntityParameters', function (Blueprint $table) {

            $table->foreignUlid('ApprovalEntityIdentifier')
                ->comment('The ApprovalEntityIdentifier of the ApprovalEntity that this parameter belongs to.')
                ->constrained('ApprovalEntities', 'ApprovalEntityIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('AttachableDataValueReference')
                ->nullable(false)
                ->comment('The AttachableDataName of the AttachableData that this references the value of.');

            $table->foreign('AttachableDataValueReference', 'ApprovalEntity_AttachableDataValueReference_Foreign')
                ->references('AttachableDataName')
                ->on('AttachableData')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ApprovalEntityParameters');
    }
};

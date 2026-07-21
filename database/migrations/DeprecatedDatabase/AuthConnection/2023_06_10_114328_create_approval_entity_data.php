<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ApprovalEntityData', function (Blueprint $table) {

            $table->foreignUlid('ApprovalEntityIdentifier')
                ->comment('The ApprovalEntityIdentifier of the ApprovalEntity that this data belongs to.')
                ->constrained('ApprovalEntities', 'ApprovalEntityIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignUuid('AuthenticatableIdentifier')
                ->comment('The AuthenticatableIdentifier of the Authenticatable that this data belongs to.')
                ->constrained('Authenticatable', 'AuthenticatableIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->longText('AuthenticatableData')
                ->nullable(false)
                ->comment('The value that the Authenticatable has created for this Entity.');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ApprovalEntityData');
    }
};

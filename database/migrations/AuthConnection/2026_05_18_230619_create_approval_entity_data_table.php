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
        Schema::create('ApprovalEntityData', function (Blueprint $table) {
            $table->ulid('ApprovalEntityDataIdentifier')
                ->comment('The identifier of the approval entity data.')
                ->primary();

            $table->foreignUlid('ApprovalEntityIdentifier')
                ->comment('The approval entity this data belongs to.')
                ->constrained('ApprovalEntities', 'ApprovalEntityIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignUuid('AuthenticatableIdentifier')
                ->comment('The Authenticatable that owns this approval data.')
                ->constrained('Authenticatable', 'AuthenticatableIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->longText('AuthenticatableData')
                ->comment('The value created for this approval entity.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time this data was created.')
                ->index();

            $table->unique(
                ['ApprovalEntityIdentifier', 'AuthenticatableIdentifier'],
                'ApprovalEntityData_UI'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ApprovalEntityData');
    }
};

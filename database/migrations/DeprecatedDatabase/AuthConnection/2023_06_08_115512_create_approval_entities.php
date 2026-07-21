<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ApprovalEntities', function (Blueprint $table) {

            $table->ulid('ApprovalEntityIdentifier')
                ->comment('Unique identifier for each approval entity.');

            $table->string('ApprovalEntityAPIURL')
                ->nullable(false)
                ->comment('The API URL of the approval entity.');

            $table->primary('ApprovalEntityIdentifier', 'ApprovalEntities_PrimaryIndex');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ApprovalEntities');
    }
};

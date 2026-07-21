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
        Schema::create('ApprovalEntities', function (Blueprint $table) {
            $table->ulid('ApprovalEntityIdentifier')
                ->comment('Unique identifier for each approval entity.')
                ->primary();

            $table->string('ApprovalEntityAPIURL')
                ->comment('The API URL of the approval entity.');

            $table->set('RequestType', ['GET', 'POST', 'PUT', 'NewBrowserWindow'])
                ->default('GET')
                ->comment('The type of request that the approval entity accepts.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time the approval entity was created.')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ApprovalEntities');
    }
};

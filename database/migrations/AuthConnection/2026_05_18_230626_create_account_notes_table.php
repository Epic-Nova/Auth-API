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
        Schema::create('AccountNotes', function (Blueprint $table) {
            $table->ulid('AccountNoteIdentifier')
                ->comment('The identifier of the account note.')
                ->primary();

            $table->foreignUuid('AccountIdentifier')
                ->comment('The account this note is attached to.')
                ->constrained('Accounts', 'AccountIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->longText('Note')
                ->comment('The note attached to the account.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the note was created.')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('AccountNotes');
    }
};

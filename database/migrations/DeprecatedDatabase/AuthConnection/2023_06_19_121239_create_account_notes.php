<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AccountNotes', function (Blueprint $table) {

            $table->foreignUuid('AccountIdentifier')
                ->comment('The identifier of the account that this note is attached to.')
                ->constrained('Accounts', 'AccountIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->longText('Note')
                ->comment('The note that is attached to the account.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the note was created.');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('AccountNotes');
    }
};

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
        Schema::create('ReservedProfileIdentifiers', function (Blueprint $table) {
            $table->ulid('ReservedProfileIdentifierIdentifier')
                ->comment('Unique identifier for each reserved profile identifier.')
                ->primary();

            $table->string('UniqueIdentifierRangeStartsAt', 4)
                ->default('0000')
                ->comment('The range start for the reserved identifier.');

            $table->string('UniqueIdentifierRangeUpTo', 4)
                ->default('9999')
                ->comment('The range end for the reserved identifier.');

            $table->string('ReservedProfileIdentifierMatch', 128)
                ->comment('The reserved profile identifier pattern.');

            $table->enum('ReservedProfileIdentifierMatchType', ['Contains', 'StartsWith', 'EndsWith', 'RegExp'])
                ->default('Contains')
                ->comment('The match type for the reserved profile identifier.');

            $table->enum('ReservedForType', ['AccountIdentifier', 'EMail'])
                ->comment('The type of entity that reserves this identifier.');

            $table->uuid('ReservedForAccount')
                ->nullable()
                ->comment('The account identifier that reserves this profile identifier.');

            $table->boolean('bCanReservedAccountShareProfileIdentifier')
                ->default(false)
                ->comment('If true, the reserved account can share this identifier.');

            $table->integer('MaximumRequestCountPerAccount')
                ->default(1)
                ->comment('The maximum request count per account.');

            $table->date('ReservedUntil')
                ->default('2099-12-31')
                ->comment('The date until the identifier is reserved.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time this reservation was created.')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ReservedProfileIdentifiers');
    }
};

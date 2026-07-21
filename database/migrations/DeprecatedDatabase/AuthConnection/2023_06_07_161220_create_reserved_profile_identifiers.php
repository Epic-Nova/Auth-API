<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ReservedProfileIdentifiers', function (Blueprint $table) {
            $table->ulid('ReservedProfileIdentifierIdentifier')
                ->comment('Unique identifier for each reserved profile identifier.');

            $table->string('UniqueIdentifierRangeStartsAt', 4)
                ->nullable(false)
                ->default('0000')
                ->comment('The range of the reserved profile identifier starts at this value.');

            $table->string('UniqueIdentifierRangeUpTo', 4)
                ->nullable(false)
                ->default('9999')
                ->comment('The range of the reserved profile identifier ends at this value.');

            $table->string('ReservedProfileIdentifierMatch', 128)
                ->nullable(false)
                ->comment('The reserved profile identifier that respects the match type.');

            $table->enum('ReservedProfileIdentifierMatchType', ['Contains', 'StartsWith', 'EndsWith', 'RegExp'])
                ->default('Contains')
                ->comment('The match type of the reserved profile identifier.
                    Contains: The reserved profile identifier contains the ReservedProfileIdentifierMatch value.
                    StartsWith: The reserved profile identifier starts with the ReservedProfileIdentifierMatch value.
                    EndsWith: The reserved profile identifier ends with the ReservedProfileIdentifierMatch value.
                    RegExp: The reserved profile identifier matches the regular expression of the ReservedProfileIdentifierMatch value.');

            $table->enum('ReservedForType', ['AccountIdentifier', 'EMail'])
                ->nullable(false)
                ->comment('The type of the reserved profile identifier.
                    AccountIdentifier: The reserved profile identifier is reserved for an account identifier.
                    EMail: The reserved profile identifier is reserved for an e-mail address.'); //

            $table->string('ReservedForAccount')
                ->nullable()
                ->comment('The account identifier that reserves this profile identifier.');

            $table->boolean('bCanReservedAccountShareProfileIdentifier')
                ->default(false)
                ->comment('If true, the reserved account can share the reserved profile identifier with other accounts.');

            $table->integer('MaximumRequestCountPerAccount')
                ->default(1)
                ->comment('The maximum request count per account.');

            $table->date('ReservedUntil')
                ->default('2099-12-31')
                ->comment('The date until the reserved profile identifier is reserved.');

            $table->primary('ReservedProfileIdentifierIdentifier', 'ReservedProfileIdentifiers_PrimaryIndex');

            // Anna den username contains("redfield") mit der range 0000-9999 reservieren
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ReservedProfileIdentifiers');
    }
};

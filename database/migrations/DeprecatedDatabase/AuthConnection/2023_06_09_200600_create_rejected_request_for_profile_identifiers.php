<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('RejectedRequestForProfileIdentifiers', function (Blueprint $table) {

            $table->uuid('RejectedAccountIdentifier')
                ->comment('The account identifier that the request was rejected for.');

            $table->foreign('RejectedAccountIdentifier', 'RejectedRequestFor_AccountIdentifier_Foreign')
                ->references('AccountIdentifier')
                ->on('Accounts')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->ulid('RejectedOnProfileIdentifier')
                ->comment('The profile identifier that the account was rejected on.');

            $table->foreign('RejectedOnProfileIdentifier', 'RejectedRequestFor_ProfileIdentifier_Foreign')
                ->references('ReservedProfileIdentifierIdentifier')
                ->on('ReservedProfileIdentifiers')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rejected_request_profile_identifiers');
    }
};

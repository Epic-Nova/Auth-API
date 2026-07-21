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
        Schema::create('RejectedRequestForProfileIdentifiers', function (Blueprint $table) {
            $table->ulid('RejectedRequestIdentifier')
                ->comment('The identifier for the rejected request.')
                ->primary();

            $table->foreignUuid('RejectedAccountIdentifier')
                ->comment('The account identifier the request was rejected for.')
                ->constrained('Accounts', 'AccountIdentifier', 'RejReq_Account_FK')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignUlid('RejectedOnProfileIdentifier')
                ->comment('The reserved profile identifier the request was rejected on.')
                ->constrained('ReservedProfileIdentifiers', 'ReservedProfileIdentifierIdentifier', 'RejReq_Profile_FK')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time the request was rejected.')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('RejectedRequestForProfileIdentifiers');
    }
};

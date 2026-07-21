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
        Schema::create('BannedAuthenticatableAppeals', function (Blueprint $table) {
            $table->ulid('BannedAuthenticatableAppealIdentifier')
                ->comment('The identifier for the ban appeal.')
                ->primary();

            $table->uuid('BanIdentifier')
                ->comment('The BanIdentifier that this appeal belongs to.');

            $table->longText('Appeal')
                ->comment('The appeal made by the Authenticatable.');

            $table->enum('BanAppealStatus', ['Pending', 'Approved', 'Denied'])
                ->nullable()
                ->comment('The status of the appeal.');

            $table->timestamp('BanAppealDate')
                ->nullable()
                ->comment('The date that the appeal was made.');

            $table->timestamp('BanAppealDecisionDate')
                ->nullable()
                ->comment('The date when the appeal decision was made.');

            $table->uuid('BanAppealDecisionMadeBy')
                ->nullable()
                ->comment('The Authenticatable that handled the appeal.');

            $table->longText('BanAppealDecisionReason')
                ->nullable()
                ->comment('The reason for the appeal decision.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the appeal was created.')
                ->index();

            $table->index('BanIdentifier', 'BannedAuthenticatableAppeals_BanIndex');

            $table->foreign('BanIdentifier', 'BannedAuthAppeals_BanID_FK')
                ->references('BanIdentifier')
                ->on('BannedAuthenticatable')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('BannedAuthenticatableAppeals');
    }
};

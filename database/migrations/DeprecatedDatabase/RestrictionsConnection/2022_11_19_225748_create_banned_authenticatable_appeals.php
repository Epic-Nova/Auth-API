<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('BannedAuthenticatableAppeals', function (Blueprint $table) {

            $table->foreignUuid('BanIdentifier')
                ->comment('The BanIdentifier of the Ban that this data belongs to.')
                ->constrained('BannedAuthenticatable', 'BanIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->longText('Appeal')
                ->comment('The appeal made by the Authenticatable.');

            $table->enum('BanAppealStatus', ['Pending', 'Approved', 'Denied'])
                ->nullable()
                ->comment('The status of the appeal.
                    Pending: The appeal is pending review.
                    Approved: The appeal was approved.
                    Denied: The appeal was denied.');

            $table->timestamp('BanAppealDate')
                ->nullable()
                ->comment('The date that the appeal was made.');

            $table->timestamp('BanAppealDecisionDate')
                ->nullable()
                ->comment('The date that the decision was made by the Authenticatable that handled the appeal.');

            $table->foreignUuid('BanAppealDecisionMadeBy')
                ->comment('The Authenticatable that handled the appeal.')
                ->constrained('Auth.Authenticatable', 'AuthenticatableIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->longText('BanAppealDecisionReason')
                ->nullable()
                ->comment('The reason for the decision made by the Authenticatable that handled the appeal.');

        });
    }

    public function down()
    {
        Schema::dropIfExists('BannedAuthenticatableAppeals');
    }
};

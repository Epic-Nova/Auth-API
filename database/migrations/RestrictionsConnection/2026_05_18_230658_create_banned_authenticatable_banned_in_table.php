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
        Schema::create('BannedAuthenticatableBannedIn', function (Blueprint $table) {
            $table->ulid('BannedAuthenticatableBannedInIdentifier')
                ->comment('The identifier for the ban scope row.')
                ->primary();

            $table->uuid('BanIdentifier')
                ->comment('The BanIdentifier that this scope belongs to.');

            $table->enum('BannedInType', ['Application', 'Shard'])
                ->comment('Where this Authenticatable is banned in.');

            $table->uuid('BannedIn')
                ->comment('The identifier of the application or shard.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the scope was created.')
                ->index();

            $table->index('BanIdentifier', 'BannedAuthenticatableBannedIn_BanIndex');

            $table->foreign('BanIdentifier', 'BannedAuthBannedIn_BanID_FK')
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
        Schema::dropIfExists('BannedAuthenticatableBannedIn');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Accounts', function (Blueprint $table) {

            $table->foreignUuid('AccountIdentifier')
                ->comment('The reference to the AuthenticatableResource.')
                ->constrained('Authenticatable', 'AuthenticatableIdentifier')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->enum('Region', ['Europe', 'NorthAmerica', 'SouthAmerica', 'Asia', 'Africa', 'Australia'])
                ->comment('The region where this account were created.');

            $table->primary('AccountIdentifier', 'Accounts_PrimaryIndex');

        });
    }

    public function down()
    {
        Schema::dropIfExists('Accounts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Authenticatable', function (Blueprint $table) {

            $table->uuid('AuthenticatableIdentifier')
                ->comment('The identifier of the authenticatable resource.');

            $table->enum('AuthenticatableType', ['Account', 'Application'])
                ->default('Account')
                ->comment('The type of the authenticatable resource.
                    Account: The resource is an account.
                    Application: The resource is an application.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time the authenticatable resource was created.');

            $table->primary('AuthenticatableIdentifier', 'Authenticatable_PrimaryIndex');

        });
    }

    public function down()
    {
        Schema::dropIfExists('Authenticatable');
    }
};

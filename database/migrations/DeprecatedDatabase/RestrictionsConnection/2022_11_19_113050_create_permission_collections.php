<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('PermissionCollections', function (Blueprint $table) {

            $table->string('PermissionCollectionIdentifier')
                ->comment('A unique identifier for the PermissionCollection.');

            $table->string('PermissionCollectionDescription')
                ->nullable()
                ->comment('A description of the PermissionCollection.');

            $table->primary('PermissionCollectionIdentifier', 'PermissionCollections_PrimaryIndex');

        });
    }

    public function down()
    {
        Schema::dropIfExists('PermissionCollections');
    }
};

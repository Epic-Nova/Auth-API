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
        Schema::create('PermissionCollections', function (Blueprint $table) {
            $table->string('PermissionCollectionIdentifier')
                ->comment('A unique identifier for the PermissionCollection.')
                ->primary();

            $table->string('PermissionCollectionDescription')
                ->nullable()
                ->comment('A description of the PermissionCollection.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the collection was created.')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PermissionCollections');
    }
};

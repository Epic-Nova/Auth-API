<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'AuthConnection';

    public function up(): void
    {
        Schema::connection($this->connection)->table('OAuthClients', function (Blueprint $table): void {
            $table->string('ApplicationIdentity', 128)->nullable()->unique()->after('OAuthClientIdentifier');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('OAuthClients', function (Blueprint $table): void {
            $table->dropUnique(['ApplicationIdentity']);
            $table->dropColumn('ApplicationIdentity');
        });
    }
};

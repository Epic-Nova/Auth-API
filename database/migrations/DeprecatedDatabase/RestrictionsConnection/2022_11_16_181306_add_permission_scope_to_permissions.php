<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('Permissions', function (Blueprint $table) {

            $table->foreign('PermissionScope', 'Permissions_PermissionScope_Foreign')
                ->references('ScopeIdentifier')
                ->on('Scopes')
                ->nullOnDelete()
                ->cascadeOnUpdate();

        });
    }
};

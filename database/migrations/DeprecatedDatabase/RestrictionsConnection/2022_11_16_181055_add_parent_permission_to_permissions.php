<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('Permissions', function (Blueprint $table) {

            $table->foreign('ParentPermission', 'Permissions_ParentPermission_Foreign')
                ->references('PermissionIdentifier')
                ->on('Permissions')
                ->nullOnDelete()
                ->cascadeOnUpdate();

        });
    }
};

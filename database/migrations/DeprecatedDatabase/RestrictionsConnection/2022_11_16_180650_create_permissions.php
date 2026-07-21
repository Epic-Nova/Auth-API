<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Permissions', function (Blueprint $table) {

            $table->string('PermissionIdentifier')
                ->comment('The com identifier of the permission, containing the name of the permission in the last part of the identifier.');

            $table->string('PermissionDescription')
                ->nullable()
                ->comment('The description of the permission.');

            $table->string('ParentPermission')
                ->nullable()
                ->comment('The com identifier of the parent permission, containing the name of the permission in the last part of the identifier.');

            $table->string('PermissionScope')
                ->nullable()
                ->comment('The com identifier of the scope witch this permission is attached to, containing the name of the scope in the last part of the identifier.');

            $table->boolean('bIsActive')
                ->default(true)
                ->comment('If true, then the permission is active and can be used.');

            $table->primary('PermissionIdentifier', 'Permissions_PrimaryIndex');
            $table->index('ParentPermission', 'Permissions_Index');

        });
    }

    public function down()
    {
        Schema::dropIfExists('Permissions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Roles', function (Blueprint $table) {

            $table->string('RoleIdentifier')
                ->comment('The com identifier of the role, containing the name of the role in the last part of the identifier.');

            $table->string('ParentRoleIdentifier')
                ->nullable()
                ->comment('The com identifier of the parent role, containing the name of the role in the last part of the identifier.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time when the role was created.');

            $table->primary('RoleIdentifier', 'Roles_PrimaryIndex');
            $table->index('ParentRoleIdentifier', 'Roles_Index');

        });
    }

    public function down()
    {
        Schema::dropIfExists('Roles');
    }
};

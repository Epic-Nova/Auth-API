<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('RoleIgnoredErrors', function (Blueprint $table) {

            $table->string('RoleIdentifier')
                ->comment('The RoleIdentifier of the Role that this data belongs to.');

            $table->foreign('RoleIdentifier', 'RoleIgnoredErrors_RoleIdentifier_Foreign')
                ->references('RoleIdentifier')
                ->on('Roles')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('ErrorIdentifier')
                ->comment('The ErrorIdentifier that should be ignored by the defined Role.');

        });
    }

    public function down()
    {
        Schema::dropIfExists('RoleIgnoredErrors');
    }
};

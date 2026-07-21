<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Permissions', function (Blueprint $table) {

            $table->set('PermissionType', ['Head', 'Read', 'Write', 'Delete', 'Execute'])
                ->default('Head')
                ->after('PermissionIdentifier')
                ->comment('The restriction type of this Permission.
                    Head: The Permission is a head permission, and is used to group other permissions together.
                    Read: The Permission is a read permission, and is used to read data.
                    Write: The Permission is a write permission, and is used to write data.
                    Delete: The Permission is a delete permission, and is used to delete data.
                    Execute: The Permission is an execute permission, and is used to execute code.');

        });
    }

    public function down(): void
    {
        Schema::table('Permissions', function (Blueprint $table) {
            //
        });
    }
};

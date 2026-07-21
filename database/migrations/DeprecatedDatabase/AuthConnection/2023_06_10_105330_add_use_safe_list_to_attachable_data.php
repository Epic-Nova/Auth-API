<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('AttachableData', function (Blueprint $table) {

            $table->boolean('bUseSafeList')
                ->default(false)
                ->comment('If true, only the values in the SafeList will be allowed. If false, all values will be allowed.')
                ->after('MaxAllowedCharacters');

        });
    }

    public function down(): void
    {
        Schema::table('AttachableData', function (Blueprint $table) {
            //
        });
    }
};

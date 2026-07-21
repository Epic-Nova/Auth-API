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
        Schema::create('AuthenticationProviderScriptsFunctionsBlacklist', function (Blueprint $table) {
            $table->string('AuthenticationProviderScriptFunctionName', 128)
                ->comment('The name of the function that is blacklisted.')
                ->primary();

            $table->enum('ScriptLanguage', ['PHP', 'JavaScript', 'GoLang', 'Python'])
                ->default('PHP')
                ->comment('The language of the script that the function is blacklisted in.');

            $table->timestamp('CreatedAt')
                ->useCurrent()
                ->comment('The date and time the blacklist entry was created.')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('AuthenticationProviderScriptsFunctionsBlacklist');
    }
};

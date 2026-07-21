<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ApprovalEntityParameters', function (Blueprint $table) {

            $table->enum('ParameterStoreIn', ['AuthorisationHeader', 'Body',  'Params', 'Header'])
                ->default('Body')
                ->comment('Where the parameter in the request should be stored.
                    AuthorisationHeader: The parameter should be stored in the Authorisation header.
                    Body: The parameter should be stored in the body of the request.
                    Params: The parameter should be stored in the params of the request.
                    Header: The parameter should be stored in the header of the request.')
                ->after('AttachableDataValueReference');

        });
    }

    public function down(): void
    {
        Schema::table('ApprovalEntityParameters', function (Blueprint $table) {
            //
        });
    }
};

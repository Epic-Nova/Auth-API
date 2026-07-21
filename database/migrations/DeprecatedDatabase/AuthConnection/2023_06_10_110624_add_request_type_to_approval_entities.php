<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ApprovalEntities', function (Blueprint $table) {

            $table->enum('RequestType', ['GET', 'POST', 'PUT', 'NewBrowserWindow'])
                ->default('GET')
                ->comment('The type of request that the ApprovalEntity accepts.
                    GET: The request will be a GET request.
                    POST: The request will be a POST request.
                    PUT: The request will be a PUT request.
                    NewBrowserWindow: The request will be a GET request and will open in a new browser window. If the request is send by the API,
                    the API will return a message with the needed information, save the current request state with the StateSaverTrait
                    and wait for the user to respond with the correct return parameters coming from the response of the request.
                    Then the API will double check the parameters with the corresponding authentication endpoint on the authentication provider and if the authentication is successful,
                    the API will continue with the saved request state that has been now received the needed information from the user.')
                ->after('ApprovalEntityAPIURL');

        });
    }

    public function down(): void
    {
        Schema::table('ApprovalEntities', function (Blueprint $table) {
            //
        });
    }
};

<?php

namespace App\Traits\Deprecated;

use App\Exceptions\ClientNotFoundException;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

/**
 * @deprecated The LoginTrait will be removed in the next commit, it has been already replaced by LoginController
 */
trait LoginTrait
{
    use AuthenticatableTrait;

    public function GetCurrentClient()
    {
        if (Auth::check()) {
            return $this->GetClient(Auth::user()->Client); // This is the correct usage

            return Auth::user()->Client;  // Best example for the new system, the Client is just a string because it got overridden by getClientAttribute()
        }

        request()->validate([
            'AccountIdentifier' => ['required', 'string'],
        ]);

        $Client = Client::where('ClientIdentifier', request()->AccountIdentifier)->first();
        if ($Client == null) {
            throw new ClientNotFoundException(request()->AccountIdentifier);
        }

        return $Client;
    }
}

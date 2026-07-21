<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CapabilityDiscoveryController;
use App\Traits\LoginTokenTrait;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    use LoginTokenTrait;

    /**
     * Matches: GET /api/v1/account
     * Returns its own self-describing capabilities registry matrix dynamically.
     */
    public function index()
    {
        // Query the master registry specifically for v1 -> account
        $myCapabilities = CapabilityDiscoveryController::getCapabilitiesForFeature('v1', 'account');

        return response()->json([
            'feature' => 'account',
            'version' => 'v1',
            'capabilities' => $myCapabilities
        ]);

        /**
         * @TODO: Receive profiles bound the entered account (no auth needed)
         */
    }

    /**
     * Matches: GET /api/v1/account/lookup
     */
    public function lookup()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Account lookup utility executed.'
        ]);
    }

    /**
     * Matches: GET /api/v1/account/{id}
     */
    public function show($id)
    {
        return response()->json([
            'status' => 'success',
            'id' => $id
        ]);
    }

    /**
     * Matches: POST /api/v1/account
     */
    public function store(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Account record parsed.'
        ]);
    }
}

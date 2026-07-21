<?php

namespace App\Traits\Deprecated;

/**
 * This trait introduces the ability to save the state of a request.
 * This is used for example when the API sends an OTP code via SMS to the user, in these
 * cases the api has to wait for a new request from the user with the OTP code.
 * When the user sends the request with the OTP code, the API has to check if the OTP code is correct
 * with the corresponding verification endpoint in the authentication provider.
 * If the verification is successful, the API can continue with the saved request state that has been
 * now received the needed information from the user.
 *
 * Saved requests will be deleted within 24 hours.
 *
 * @TODO: Create a corresponding Model and Migration for this trait.
 */
trait StateSaverTrait {}

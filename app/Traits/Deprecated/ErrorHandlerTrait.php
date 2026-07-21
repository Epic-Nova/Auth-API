<?php

namespace App\Traits\Deprecated;

use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\NoReturn;

/**
 * @deprecated This trait will be replaced by TranslationTrait that uses the LanguageAPI for translations
 */
trait ErrorHandlerTrait
{
    /**
     * Checks if the Error is ignored by a Specific Role
     *
     * @deprecated This trait will be replaced by TranslationTrait that uses the LanguageAPI for translations
     *
     * @param  string  $ResponseIdentifier  The Response to send
     * @param  array  $MessageAttributes  The Message Attributes
     * @param  array  $AdditionalParameters  The Additional Parameters
     */
    public function HandleError(string $ResponseIdentifier, array $MessageAttributes = [], array $AdditionalParameters = []): void
    {
        Log::debug('HandleError: Handling Error for Function '.debug_backtrace()[1]['function'].' with Error Identifier: '.$ResponseIdentifier);

        $Response = $this->GetResponse($ResponseIdentifier, $MessageAttributes, $AdditionalParameters);

        // @TODO Implement Role Ignored Errors, currently the Token Validation system throws an error when the client is logged in
        /*if(Auth::check())
        {
            $Account = Auth::user();

            $Roles = $Account->Client->GetRoles($Account->AccountIdentifier);
            foreach($Roles as $Role)
            {
                $Errors = RoleIgnoredError::where('RoleIdentifier', $Role["RoleIdentifier"])->get();
                foreach($Errors as $Error)
                {
                    if($Error->ErrorIdentifier == $ResponseIdentifier)
                    {
                        Log::debug("HandleError: Error is ignored");
                        return;
                    }
                }
            }
            Log::debug("HandleError: Error is not ignored");
        }
        else
        {
            Log::debug("HandleError: Ignored Error check skipped, User is not Logged in");
        }*/

        response()->json($Response['ResponseJSON'], $Response['Response'][1] ?? 200)->send();
        exit();
    }

    /**
     * Handling Message
     *
     * @deprecated This trait will be replaced by TranslationTrait that uses the LanguageAPI for translations
     *
     * @param  string  $ResponseIdentifier  The Response to send
     * @param  array  $MessageAttributes  The Message Attributes
     * @param  array  $AdditionalParameters  The Additional Parameters
     */
    #[NoReturn]
    public function HandleMessage(string $ResponseIdentifier, array $MessageAttributes = [], array $AdditionalParameters = [], $ShouldDieRequest = true): void
    {
        Log::debug('HandleMessage: '.$ResponseIdentifier);

        $Response = $this->GetResponse($ResponseIdentifier, $MessageAttributes, $AdditionalParameters);

        response()->json($Response['ResponseJSON'], $Response['Response'][1] ?? 200)->send();

        if ($ShouldDieRequest) {
            exit();
        }
    }

    /**
     * Returns a custom response based on the ResponseIdentifier type
     *
     * @deprecated This trait will be replaced by TranslationTrait that uses the LanguageAPI for translations
     *
     * @param  string  $ResponseIdentifier  The Response to send
     * @param  array  $MessageAttributes  The Message Attributes
     * @param  array  $AdditionalParameters  The Additional Parameters
     * @return array The Response Array
     */
    private function GetResponse(string $ResponseIdentifier, array $MessageAttributes = [], array $AdditionalParameters = []): array
    {
        Log::debug('GetResponse: Getting Response for: '.$ResponseIdentifier);

        $Translation = __($ResponseIdentifier, $MessageAttributes);

        $Response = explode('+', $Translation);

        $FriendlyResponseIdentifier = 'friendly.'.str_replace(debug_backtrace()[1]['function'] == 'HandleError' ? 'errors.' : 'messages.', '', $ResponseIdentifier);

        $ResponseJSON = [
            'MessageCode' => $ResponseIdentifier,
            'Message' => __($Response[0], $MessageAttributes),
            'FriendlyMessageCode' => $FriendlyResponseIdentifier,
            'FriendlyMessage' => __($FriendlyResponseIdentifier),
            'ResponseParameters' => $AdditionalParameters,
            'StatusCodeImage' => "https://http.cat/{$Response[1]}",
        ];

        return ['Response' => $Response, 'ResponseJSON' => $ResponseJSON];
    }
}

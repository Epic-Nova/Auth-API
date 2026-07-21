<?php

namespace App\Traits\Deprecated;

trait ConversationHelperTrait
{
    /**
     * Converts an Identifier to a name, for example: "com.epicnova.authentication_provider.password" to "Password"
     *
     * @param  $Identifier  string The identifier to convert
     * @return string Returns the name of the identifier
     */
    public function ConvertIdentifierToName(string $Identifier): string
    {
        $Identifier = explode('.', $Identifier);
        $Identifier = end($Identifier);
        $Identifier = ucfirst($Identifier);

        return str_replace('_', ' ', $Identifier);
    }
}

<?php

return [
    /*
     * This is intentionally unset by default. KeyForge is the only caller
     * allowed to create confidential application credentials.
     */
    'oauth_provisioning_key' => env('KEYFORGE_OAUTH_PROVISIONING_KEY'),
    'oauth_provisioning_key_minimum_length' => 32,
    'oauth_client_name_prefix' => env('KEYFORGE_OAUTH_CLIENT_NAME_PREFIX', 'keyforge:'),
    'oauth_client_maximum_ttl' => env('KEYFORGE_OAUTH_CLIENT_MAXIMUM_TTL', 3600),

    // Local integration testing only.  When enabled, a specifically attached
    // local-bypass provider can issue a bootstrap token without contacting an
    // external authentication-provider service.  It defaults to off and is
    // never part of the production deployment profile.
    'local_login_bypass' => env('NOVA_LOCAL_LOGIN_BYPASS', false),
];

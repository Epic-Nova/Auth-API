<?php

use App\Models\Authentication\Account;
use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'NovaUserProvider',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        'NovaProvider' => [
            'driver' => 'NovaProvider',
            'model' => Account::class,
        ],
        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],

        'Clients' => [
            'provider' => 'NovaProvider',
            'table' => 'PasswordResets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

    'jwt' => [
        'issuer' => 'auth.epicnova.net',
        'leeway' => 0,
        'access_ttl' => env('AUTH_JWT_ACCESS_TTL', 3600),
    ],

    'jwk' => [
        'kty' => 'RSA',
        'alg' => 'PS512',
        'use' => 'sig',
        'kid' => 'e47836586219c849',
        'n' => '3_J3g6ujnoCbWQcLG8zBWX2igY7ZG0NfoJ1WFNmSYtZY-lK1PDdf3OyJAWBMq_CIJ3oDc7NaJWWCWySWJ5D8cEvyXHlVIhgTBmobcjZlTMmd7eAkho_TDWke07Zh424Y_KvFWRrKqZTnmIp_JiqRs3_n9fu9bN3_wU3NFDJn8ipYxLXHSO73i1uTfU8gA590GG8uK7sSYgART3bx31wjbbX6zHU5Um-czJF7WQ9AMVTjJWwtFewukO5yi6GfA1CIQly3hwsubP-VnvMBR-_yX3E1PFm3e1i7RFALPTR3fxG4XASPgfVCP7vGQum_OK5Sjic3tIOjxTNfa8d55fjWHw',
        'e' => 'AQAB',
        'd' => 'EHsQjpEYFncSd6fMCny0Efjr96rikV4Q8IoDpr8oRbvOioWBru_IT0LdBkvm-JRYAMe6703YqX3h7QExR7Ik0cLVEDchq8OnysYORQZ5x__ibnobVgfJmWgOC7iVVZ5pmlatzFPrbDV4IcK3fsCNPm-AS5r-Ir_nrXsjR4Q6IBkYcx-jCK4yvwyZ2Eb6F38P3MuudufVbN1MkGXPnYE06gwkX272xpLZ-COMdZy_g8MiqnM79xrzajXI8Ner_uqhG8Z2Q4INw_SHFBE_PgvbLG43f6zo5fQelk1LI16rbOdTMvs45ZX1ntTvI1r5Q-m2UAwQSdqRAqea4H9Y7nFV2Q',
        'p' => '9uAlgTB82FT4A4FVNFEidBa-pN-0wrNdItLrS3JXJPSJ5NilszeYEkpksgxpQREqN1CYj_uQcx5kR8zGXObYo2hlJPx2jC0RxlbV-VlyJ6rjmwlHyhDuibsKfSQ-zwaUy-oMZ0sxabiaYWHZQ4F-_iRoVhRuG8769i0mKN8mT3U',
        'q' => '6DlhFAZwj1rxITcCcmL3wLDf3nUlIfGwmmzrncHZZNlmxNEvbS6pA9j8FMTqFxcb1uaB2ZhRucseMj_b514zNVnN2as1E7ttn0RGQcWrHA9nVg1Bj9g7_G9SiTtrBtCPLWXKulnAcBRYHr8WgFbmmXtLeESVZ9ZXHZoFMDwnEMM',
        'dp' => 'YgcY_VZqjGAY5xNo6NA6tQv_AWrO1Ozx040a3yd00PjE9HjOOv6dRM4Dc-uXs72EHNjE649BiWSM_zGhpmwHo8MlPm19CSYwQ6hhaJSKzpN9sycZOXRFs6zwekZ1JjF_zgSOjkl-QnOJ4Zc_9R0rdvCv-VY9bo9eeXfuChapmK0',
        'dq' => 'p-aUof3l9EA0h2dWhY24w8Eem6DnMBunLIAD-xPlMl1dSIBWYgKSgQ2pdPrKpdc3cbFHwSO4MzUAqCQzwp0BOVms0-xNe-7P249pnLRmeutIM5dyB6a3-FQkwMQNJeg1HQW0BfhOgrqy28gdZL1wDRhwAIzV2TXH1sc9aRqUxDE',
        'qi' => 'TGfT7FbCMVT6NmKtdQX1yjeuPwtnRCINZI6P1SVwOqR8ZEFfPucsM1mRf2soxUGX6rm3dncMPHDvxcFZ8PDOF8d0UfgDr7xpuZbvvzmIeUxE3kW1XnN_exCkqF_qBkbxobf5x06cDP5ggmbUirT1_6cKUWjFppzvS8yORDZ4RL4',
    ],

];

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\PS512;
use Random\RandomException;
use Symfony\Component\Console\Command\Command as CommandAlias;

class GenerateTokenKeysCommand extends Command
{
    protected $signature = 'jwt:generate-keys';
    protected $description = 'Generate secure RSA cryptographic keys in PEM formatting and matching JWK array assignments';

    public function handle(): int
    {
        // Environment Guard Clause
        if (!extension_loaded('openssl')) {
            $this->error("❌ Critical Error: The OpenSSL PHP extension is missing or disabled.");
            $this->line("");
            $this->line("To resolve this issue:");
            $this->line("  1. Locate your active 'php.ini' config file.");
            $this->line("  2. Search for ';extension=openssl' and remove the leading semicolon.");
            $this->line("  3. Save the file and restart your terminal session or local server environment.");
            return CommandAlias::FAILURE;
        }

        $this->info("Initializing 2048-bit RSA Cryptographic engine...");

        // 1. Generate OpenSSL Key Pair (Safe now that extension availability is verified)
        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);
        if (!$res) {
            $this->error("Unable to initialize token key pair resource layout structures.");
            return CommandAlias::FAILURE;
        }

        // Extract Private PEM Format
        openssl_pkey_export($res, $privateKeyPem);

        // Extract Public PEM Format
        $publicKeyDetails = openssl_pkey_get_details($res);
        $publicKeyPem = $publicKeyDetails["key"];

        // 2. Map the generated PEM key directly into a matching JWK configuration
        try {
            $jwk = JWKFactory::createFromKey($privateKeyPem, null, [
                'alg' => 'PS512',
                'use' => 'sig',
                'kid' => bin2hex(random_bytes(8))
            ]);
        } catch (RandomException $e) {
            $this->error("Failed to generate a secure random 'kid' value for the JWK configuration.");
            $this->line("Error Details: " . $e->getMessage());
            return CommandAlias::FAILURE;
        }

        $jwkArray = $jwk->jsonSerialize();

        // 3. Output configuration blocks
        $this->info("\n=================== SYSTEM ENVIRONMENT VALUES (.env) ===================");
        $this->line("JWT_ISSUER=auth.epicnova.net");
        $this->line("JWT_KID=" . $jwkArray['kid']);

        $this->info("\n=================== LARAVEL CONFIG ARRAY (config/auth.php) ===================");
        $this->line("Incorporate these updated settings blocks directly into your runtime auth setups:");
        dump([
            'jwt' => [
                'issuer' => env('JWT_ISSUER', 'auth.epicnova.net'),
                'leeway' => 0,
            ],
            'jwk' => [
                'kty' => $jwkArray['kty'],
                'alg' => $jwkArray['alg'],
                'use' => $jwkArray['use'],
                'kid' => $jwkArray['kid'],
                'n' => $jwkArray['n'],
                'e' => $jwkArray['e'],
                'd' => $jwkArray['d'],
                'p' => $jwkArray['p'],
                'q' => $jwkArray['q'],
                'dp' => $jwkArray['dp'],
                'dq' => $jwkArray['dq'],
                'qi' => $jwkArray['qi'],
            ]
        ]);

        $this->info("\n=================== RAW PUBLIC KEY PEM FORMAT ===================");
        $this->line($publicKeyPem);

        $this->info("\n=================== RAW PRIVATE KEY PEM FORMAT ===================");
        $this->line($privateKeyPem);

        return CommandAlias::SUCCESS;
    }
}

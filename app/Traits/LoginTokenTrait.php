<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Jose\Component\Checker;
use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm\PS512;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Symfony\Component\Clock\NativeClock;

trait LoginTokenTrait
{
    /**
     * Build an Access Token (JWS) and a matching Refresh Token.
     */
    public function generateTokenPair(
        array $audiences,
        array $scopes,
        array $permissions,
        array $roles,
        array $subjects,
        int $lifetime,
        int $nbfOffset = 0,
        ?string $currentProfileId = null,
        ?string $oauthClientId = null,
        ?string $grantType = null,
    ): array {
        $jti = uniqid('jti_', true);
        $issuer = config('auth.jwt.issuer', 'auth.epicnova.net');
        $kid = config('auth.jwk.kid');

        Log::debug("Generating JWT pair. JTI: {$jti} for Issuer: {$issuer}");

        // Build Payload
        $payload = json_encode(array_filter([
            'iat' => time(),
            'nbf' => time() + $nbfOffset,
            'exp' => time() + $lifetime,
            'iss' => $issuer,
            'sub' => $subjects,
            'aud' => $audiences,
            'Scopes' => $scopes,
            'Permissions' => $permissions,
            'Roles' => $roles,
            'CurrentProfile' => $currentProfileId ?? Auth::user()?->current_profile_id ?? 'guest',
            'jti' => $jti,
            'client_id' => $oauthClientId,
            'scope' => implode(' ', $scopes),
            'grant_type' => $grantType,
        ], static fn (mixed $value): bool => $value !== null));

        // Sign Access Token
        $algorithmManager = new AlgorithmManager([new PS512]);
        $jwsBuilder = new JWSBuilder($algorithmManager);
        $serializer = new CompactSerializer;

        $jws = $jwsBuilder->create()
            ->withPayload($payload)
            ->addSignature($this->getJwkFromConfig(), ['alg' => 'PS512', 'kid' => $kid])
            ->build();

        $accessToken = $serializer->serialize($jws);

        // Build & Sign Refresh Token
        $refreshTokenData = $this->generateRefreshJwt($jti, $issuer, $kid);

        // Fire database storage hook (Handles your IssuedTokenTrait)
        if (method_exists($this, 'ConstructIssuedToken')) {
            $this->ConstructIssuedToken($jti, $refreshTokenData['jti'], $payload);
        }

        return [
            'access_token' => $accessToken,
            'access_token_jti' => $jti,
            'refresh_token' => $refreshTokenData['token'],
            'refresh_token_jti' => $refreshTokenData['jti'],
        ];
    }

    /**
     * Create a signed back-reference Refresh Token
     */
    private function generateRefreshJwt(string $parentJti, string $issuer, string $kid): array
    {
        $refreshJti = uniqid('rt_', true);
        $algorithmManager = new AlgorithmManager([new PS512]);
        $jwsBuilder = new JWSBuilder($algorithmManager);
        $serializer = new CompactSerializer;

        $payload = json_encode([
            'iat' => time(),
            'iss' => $issuer,
            'jti' => $refreshJti,
            'OriginalTokenJTI' => $parentJti,
        ]);

        $jws = $jwsBuilder->create()
            ->withPayload($payload)
            ->addSignature($this->getJwkFromConfig(), ['alg' => 'PS512', 'kid' => $kid])
            ->build();

        return [
            'token' => $serializer->serialize($jws),
            'jti' => $refreshJti,
        ];
    }

    /**
     * Universal parser & validator for tokens using web-token framework managers
     *
     * @throws Exception
     */
    public function verifyAndDecodeToken(string $token, array $requiredClaims = []): array
    {
        $algorithmManager = new AlgorithmManager([new PS512]);
        $jwsVerifier = new JWSVerifier($algorithmManager);
        $serializerManager = new JWSSerializerManager([new CompactSerializer]);
        $headerChecker = new HeaderCheckerManager([new AlgorithmChecker(['PS512'])], [new JWSTokenSupport]);

        try {
            $jws = $serializerManager->unserialize($token);
            $headerChecker->check($jws, 0);

            $payload = json_decode($jws->getPayload(), true);

            // Execute fundamental security checks
            $clock = new NativeClock('UTC');
            $leeway = (int) config('auth.jwt.leeway', 0);
            $claimChecker = new ClaimCheckerManager([
                new Checker\ExpirationTimeChecker($clock, $leeway),
                new Checker\IssuedAtChecker($clock, $leeway),
                new Checker\NotBeforeChecker($clock, $leeway),
                new Checker\IssuerChecker([config('auth.jwt.issuer', 'auth.epicnova.net')]),
            ]);

            $claimChecker->check($payload, $requiredClaims);

            // Cryptographic validation
            $jwsLoader = new JWSLoader($serializerManager, $jwsVerifier, $headerChecker);
            $jwsVerified = $jwsLoader->loadAndVerifyWithKey($token, $this->getJwkFromConfig(), $signatureIndex);

            return json_decode($jwsVerified->getPayload(), true);

        } catch (Exception $e) {
            Log::error('Token verification failure: '.$e->getMessage());
            if (method_exists($this, 'HandleError')) {
                $this->HandleError('errors.com.epicnova.token.invalid', [], ['msg' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    /**
     * Resolve JWK object dynamically from application runtime config
     */
    public function getJwkFromConfig(): JWK
    {
        return new JWK(config('auth.jwk'));
    }
}

<?php

namespace App\Services;

use App\Models\Authentication\IssuedToken;
use Exception;
use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm\PS512;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use RuntimeException;

class BearerTokenService
{
    public function resolvePayloadFromBearer(?string $bearerToken): array
    {
        if (! $bearerToken) {
            throw new RuntimeException('Bearer token missing.');
        }

        $serializerManager = new JWSSerializerManager([new CompactSerializer]);
        $headerChecker = new HeaderCheckerManager([new AlgorithmChecker(['PS512'])], [new JWSTokenSupport]);
        $verifier = new JWSVerifier(new AlgorithmManager([new PS512]));
        $jwk = new JWK(config('auth.jwk'));

        try {
            $jws = $serializerManager->unserialize($bearerToken);
            $headerChecker->check($jws, 0);

            if (! $verifier->verifyWithKey($jws, $jwk, 0)) {
                throw new RuntimeException('Bearer token signature is invalid.');
            }

            $payload = json_decode((string) $jws->getPayload(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            throw new RuntimeException('Bearer token validation failed: '.$exception->getMessage(), previous: $exception);
        }

        $issuedToken = IssuedToken::query()->find($payload['jti'] ?? null);

        if (! $issuedToken) {
            throw new RuntimeException('Issued token not found.');
        }

        if ($issuedToken->TokenState !== 'Active') {
            throw new RuntimeException('Issued token is not active.');
        }

        if ($issuedToken->TokenExpiry->isPast()) {
            throw new RuntimeException('Issued token expired.');
        }

        return $payload;
    }
}

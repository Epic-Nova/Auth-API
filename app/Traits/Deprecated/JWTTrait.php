<?php

namespace App\Traits\Deprecated;

use App\Checkers\AudienceChecker;
use App\Checkers\OriginalJTIChecker;
use App\Checkers\PermissionsChecker;
use App\Checkers\ScopeChecker;
use App\Checkers\SubjectChecker;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Jose\Component\Checker;
use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm\PS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;

/**
 * This is a class that does not need authentication at all to be used.
 * so it is not required to have a scope or permissions to use this class.
 *
 * @TODO Simplify the JWT Trait
 *
 * @deprecated The JWTTrait is no longer compatible with the current Database structure, it will be removed in the next commit and replaced by the LoginTokenTrait
 */
trait JWTTrait
{
    use AccountTrait, ErrorHandlerTrait, IssuedTokenTrait;

    /**
     * Constructs a fresh JWS for a Authenticatable with the needed access,
     * this only works when the Authenticatable that tries to Construct this token
     * has the needed access rights
     *
     * @param  array  $Audiences  The APIs also called Shards that this token is valid for
     * @param  array  $Scopes  The scopes or functions in an API that this token is valid for
     * @param  array  $Permissions  The permissions that this token is valid for
     * @param  array  $Subjects  The Authenticatable that this token is valid for
     * @param  int  $Time  The time in seconds that this token is valid for
     * @param  int  $NotValidBeforeTime  The time in seconds that this token is not valid before
     * @return array The token, refresh token and jti
     */
    public function ConstructJWT(array $Audiences, array $Scopes, array $Permissions, array $Subjects, int $Time, int $NotValidBeforeTime = 0): array
    {

        // TODO add CurrentProfile to the token

        Log::debug('ConstructJWT: Constructing JWT for Authenticatable: '.Auth::user()->Authenticatable);

        $AlgorithmManager = new AlgorithmManager([new PS256]);
        $JWSBuilder = new JWSBuilder($AlgorithmManager);
        $CompactSerializer = new CompactSerializer;

        $JTI = uniqid('', true);
        Log::debug('ConstructJWT: Constructing JWT with JTI: '.$JTI);

        $Authenticatable = Auth::user()->Authenticatable()->first();
        $AuthenticatableIdentifier = Auth::user()->Authenticatable;
        foreach ($Audiences as $Shard) {
            $Authenticatable->HasAccessToShard($Shard, $AuthenticatableIdentifier);
        }

        foreach ($Scopes as $Scope) {
            $Authenticatable->HasAccessToScope($Scope, $AuthenticatableIdentifier);
        }
        $Authenticatable->HasAccessToPermissions($Permissions, $AuthenticatableIdentifier);

        // @TODO: Implement is Banned check

        $TokenPayload = json_encode([
            'iat' => time(),
            'nbf' => time() + $NotValidBeforeTime,
            'exp' => time() + $Time,
            'iss' => 'auth.epicnova.net', // TODO get these information from the env
            'sub' => $Subjects,
            'aud' => $Audiences,
            'Scopes' => $Scopes,
            'Permissions' => $Permissions,
            'CurrentProfile' => 'TODO#0000',
            'jti' => $JTI,
        ]);

        $JWS = $JWSBuilder->create()->withPayload($TokenPayload)->addSignature($this->GetJWK(), ['alg' => 'PS256', 'kid' => config('auth.jwk.kid')])->build();

        $Token = $CompactSerializer->serialize($JWS);
        $RefreshToken = $this->ConstructRefreshJWT($JTI);

        $this->ConstructIssuedToken($JTI, $RefreshToken['RefreshTokenJTI'], $TokenPayload);

        Log::debug('ConstructJWT: Construction of JWT for Authenticatable: '.$AuthenticatableIdentifier.' is done');

        return ['Token' => $Token, 'TokenJTI' => $JTI, 'RefreshToken' => $RefreshToken['RefreshToken'], 'RefreshTokenJTI' => $RefreshToken['RefreshTokenJTI']];
    }

    /**
     * Constructs a Refresh Token that is only redeemable by the $Authenticatable(s) that it`s issued to.
     *
     * @param  string  $JTI  The JTI of the token that needs to be refreshed
     * @return array Refresh Token and Refresh token JTI
     */
    public function ConstructRefreshJWT(string $JTI): array
    {
        $AlgorithmManager = new AlgorithmManager([new PS256]);
        $JWSBuilder = new JWSBuilder($AlgorithmManager);
        $CompactSerializer = new CompactSerializer;
        $RefreshJTI = uniqid('', true);

        $TokenPayload = json_encode([
            'iat' => time(),
            'iss' => 'auth.epicnova.net', // TODO get these information from the env
            'jti' => $RefreshJTI,
            'OriginalTokenJTI' => $JTI,
        ]);

        $JWS = $JWSBuilder->create()->withPayload($TokenPayload)->addSignature($this->GetJWK(), ['alg' => 'PS256', 'kid' => config('auth.jwk.kid')])->build();

        return ['RefreshToken' => $CompactSerializer->serialize($JWS), 'RefreshTokenJTI' => $RefreshJTI];
    }

    /**
     * Verifies the Token, this function is mostly used to log in a user with an issued token
     *
     * @param  string  $Token  The JWS Token
     * @param  string  $RedeemingAudience  The Audience that is trying to redeem the token
     * @param  string  $RedeemingAuthenticatable  The Authenticatable that is trying to use the Token
     * @param  int  $SubjectIndex  The index of the subject that is trying to use the Token
     * @param  string  $RedeemingScope  The Scope that is trying to use the Token
     * @param  array  $RedeemingPermissions  The Permissions that are trying to use the Token
     * @return mixed The Token Payload
     */
    public function VerifyJWT(string $Token, string $RedeemingAudience, string $RedeemingScope, array $RedeemingPermissions, int $SubjectIndex, string $RedeemingAuthenticatable = ''): mixed
    {
        // @TODO Support multiple JWKs (JWKs) so when we enroll a new JWK for verifying the old can still be used for a while also different environments can have different JWKs

        $AlgorithmManager = new AlgorithmManager([new PS256]);
        $JWSVerifier = new JWSVerifier($AlgorithmManager);
        $SerializerManager = new JWSSerializerManager([new CompactSerializer]);
        $HeaderCheckerManager = new HeaderCheckerManager([new AlgorithmChecker(['PS256'])], [new JWSTokenSupport]);
        $JWK = $this->GetJWK();

        if ($this->IsRefreshToken($Token)) {
            $this->HandleError('errors.com.epicnova.token.refresh_token_not_validatable');
        }

        try {
            $JWS = $SerializerManager->unserialize($Token);
            $JWSPayload = $JWS->getPayload();
            if (func_num_args() != 6) { // @TODO Replace the if statement with a check if the RedeemingAuthenticatable is empty
                Log::debug('VerifyJWT: RedeemingAuthenticatable is not set, getting it from the Token Payload.');
                $RedeemingAuthenticatable = json_decode($JWSPayload, true)['sub'][$SubjectIndex];
            }
            $ClaimCheckerManager = new ClaimCheckerManager([
                new AudienceChecker($RedeemingAudience, $RedeemingAuthenticatable),
                new ScopeChecker($RedeemingScope, $RedeemingAuthenticatable),
                new PermissionsChecker($RedeemingPermissions, $RedeemingAuthenticatable),
                new SubjectChecker($RedeemingAuthenticatable, $SubjectIndex),
                new Checker\ExpirationTimeChecker(600),
                new Checker\IssuedAtChecker,
                new Checker\NotBeforeChecker(5),
                new Checker\IssuerChecker(['auth.epicnova.net'])]); // TODO Get this value from the available token issuers from the corresponding api

            $ClaimCheckerManager->check(json_decode($JWSPayload, true), ['iss', 'sub', 'aud', 'iat', 'exp', 'nbf', 'jti', 'Scopes', 'Permissions', 'CurrentProfile']);
            $HeaderCheckerManager->check($JWS, 0, ['alg', 'kid']);
        } catch (Exception $e) {
            $this->HandleError('errors.com.epicnova.token.something_went_wrong', [],
                ['Exception Message' => $e->getMessage(), 'Exception Code' => $e->getCode()]);
        }

        $JWSLoader = new JWSLoader($SerializerManager, $JWSVerifier, $HeaderCheckerManager);

        $JWS = $JWSLoader->loadAndVerifyWithKey($Token, $JWK, $Signature);
        $JWSDecodedPayload = json_decode($JWS->getPayload(), true);

        $this->IssuedTokenExists($JWSDecodedPayload['jti']);
        $this->SaveTokenUsage($JWSDecodedPayload['jti'], $RedeemingAudience, $RedeemingScope, $RedeemingPermissions, $SubjectIndex);

        return $JWSDecodedPayload;
    }

    private function VerifyRefreshJWT(string $RefreshToken): array
    {
        $AlgorithmManager = new AlgorithmManager([new PS256]);
        $JWSVerifier = new JWSVerifier($AlgorithmManager);
        $SerializerManager = new JWSSerializerManager([new CompactSerializer]);
        $HeaderCheckerManager = new HeaderCheckerManager([new AlgorithmChecker(['PS256'])], [new JWSTokenSupport]);
        $ClaimCheckerManager = new ClaimCheckerManager([
            new OriginalJTIChecker,
            new Checker\IssuedAtChecker,
            new Checker\IssuerChecker(['auth.epicnova.net'])]); // TODO Get this value from the available token issuers from the corresponding api
        $JWK = $this->GetJWK();

        try {
            $JWS = $SerializerManager->unserialize($RefreshToken);
            $JWSPayload = $JWS->getPayload();

            $ClaimCheckerManager->check(json_decode($JWSPayload, true), ['OriginalTokenJTI', 'iss', 'iat', 'jti']);
            $HeaderCheckerManager->check($JWS, 0, ['alg', 'kid']);
        } catch (Exception $e) {
            $this->HandleError('errors.com.epicnova.token.something_went_wrong', [],
                ['Exception Message' => $e->getMessage(), 'Exception Code' => $e->getCode()]);
        }

        $JWSLoader = new JWSLoader($SerializerManager, $JWSVerifier, $HeaderCheckerManager);

        $JWS = $JWSLoader->loadAndVerifyWithKey($RefreshToken, $JWK, $Signature);
        $JWSDecodedPayload = json_decode($JWS->getPayload(), true);

        $IssuedToken = $this->GetIssuedTokenByRefreshJTI($JWSDecodedPayload['jti']);
        $IssuedTokenPayload = json_decode($IssuedToken->TokenPayload, true);

        // $this->SaveTokenUsage($JWSDecodedPayload["jti"], "com.epicnova.shard.auth", "", [], 0);

        // @TODO save to the TokenUsage table when the token is used for and what and witch RedeemingAuthenticatable is using it
        return ['RefreshTokenPayload' => $JWSDecodedPayload, 'IssuedTokenPayload' => $IssuedTokenPayload];
    }

    public function RedeemRefreshToken(string $RefreshToken, int $SubjectIndex): array
    {
        // @TODO
        if (! $this->IsRefreshToken($RefreshToken)) {
            $this->HandleError('errors.com.epicnova.token.not_refresh_token');
        }

        $DecodedRefreshTokenPayload = $this->VerifyRefreshJWT($RefreshToken);

        $AuthenticatableIdentifier = $this->GetAuthenticatableFromRefreshTokenJTI($DecodedRefreshTokenPayload['RefreshTokenPayload']['jti'], $SubjectIndex);
        Auth::setUser($this->GetAccount($AuthenticatableIdentifier));

        $this->HandleMessage('messages.com.epicnova.token.redeemed_refresh_token', [],
            [$this->ConstructJWT($DecodedRefreshTokenPayload['IssuedTokenPayload']['aud'],
                $DecodedRefreshTokenPayload['IssuedTokenPayload']['Scopes'],
                $DecodedRefreshTokenPayload['IssuedTokenPayload']['Permissions'],
                $DecodedRefreshTokenPayload['IssuedTokenPayload']['sub'],
                3600, $DecodedRefreshTokenPayload['IssuedTokenPayload']['nbf'])]);

        // @TODO Delete the IssuedTokens entry and check if the OriginalTokenJTI is pointing to a valid Token and then create the exact same token then do some other validation stuff
    }

    /**
     * Returns the JWK from the Auth configuration file and constructs a new JWK object.
     */
    public function GetJWK(): JWK
    {
        return new JWK([
            'p' => config('auth.jwk.p'),
            'kty' => config('auth.jwk.kty'),
            'q' => config('auth.jwk.q'),
            'd' => config('auth.jwk.d'),
            'e' => config('auth.jwk.e'),
            'use' => config('auth.jwk.use'),
            'kid' => config('auth.jwk.kid'),
            'qi' => config('auth.jwk.qi'),
            'dp' => config('auth.jwk.dp'),
            'alg' => config('auth.jwk.alg'),
            'dq' => config('auth.jwk.dq'),
            'n' => config('auth.jwk.n'),
        ]);
    }

    /**
     * Determines if the entered token is a refresh token or not
     *
     * @param  string  $RefreshToken  The JWS Token
     * @return bool True if the token is a refresh token, false if not
     */
    private function IsRefreshToken(string $RefreshToken): bool
    {
        $SerializerManager = new JWSSerializerManager([new CompactSerializer]);

        try {
            $JWS = $SerializerManager->unserialize($RefreshToken);
            $JWSPayload = $JWS->getPayload();
        } catch (Exception $e) {
            $this->HandleError('errors.com.epicnova.token.something_went_wrong', [],
                ['Exception Message' => $e->getMessage(), 'Exception Code' => $e->getCode()]);
        }

        return array_key_exists('OriginalTokenJTI', json_decode($JWSPayload, true));
    }

    /**
     * Returns the Authenticatable registered in the token by receiving the saved payload of the token in the database
     * from the JTI of the original token
     *
     * @param  string  $RefreshTokenJTI  The JTI of the refresh token
     * @param  int  $SubjectIndex  The index of the subject in the database
     * @return string The Authenticatable that the issued the token belongs to
     */
    private function GetAuthenticatableFromRefreshTokenJTI(string $RefreshTokenJTI, int $SubjectIndex): string
    {
        $IssuedToken = $this->GetIssuedTokenByRefreshJTI($RefreshTokenJTI);

        return json_decode($IssuedToken->TokenPayload, true)['sub'][$SubjectIndex];
    }

    /**
     * Saves for what a Token is used for and when it was used
     *
     * @param  string  $TokenJTI  The JTI of the Token
     * @param  string  $AccessedShard  The shard the token was used for
     * @param  string  $AccessedScope  The scope the token was used for
     * @param  array  $AccessedPermissions  The permissions the token was used for
     * @param  int  $SubjectIndex  The index of the account the token was used for
     */
    public function SaveTokenUsage(string $TokenJTI, string $AccessedShard, string $AccessedScope, array $AccessedPermissions, int $SubjectIndex): void
    {
        // @TODO Create the TokenUsage table and save the data
    }
}

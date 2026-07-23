<?php

namespace App\Traits;

use App\Models\Authentication\IssuedToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;

trait IssuedTokenTrait
{
    public function ConstructIssuedToken(string $issuedTokenJti, string $issuedRefreshTokenJti, string $tokenPayload): IssuedToken
    {
        $payload = json_decode($tokenPayload, true) ?? [];
        $issuedToAuthenticatable = $this->resolveIssuedToAuthenticatable($payload['sub'] ?? []);

        if (! $issuedToAuthenticatable) {
            Log::error('ConstructIssuedToken: Missing AuthenticatableIdentifier in token subjects.');
            if (method_exists($this, 'HandleError')) {
                $this->HandleError('errors.com.epicnova.token.subject_missing');
            }
            throw new RuntimeException('Missing AuthenticatableIdentifier in token subjects.');
        }

        $issuedAt = isset($payload['iat'])
            ? Carbon::createFromTimestamp((int) $payload['iat'])
            : now();

        $tokenExpiry = isset($payload['exp'])
            ? Carbon::createFromTimestamp((int) $payload['exp'])
            : now()->addHour();

        $issuedToken = new IssuedToken;
        $issuedToken->IssuedTokenJTI = $issuedTokenJti;
        $issuedToken->IssuedRefreshTokenJTI = $issuedRefreshTokenJti;
        $issuedToken->TokenPayload = $tokenPayload;
        $issuedToken->TokenState = 'Active';
        $issuedToken->TokenExpiry = $tokenExpiry;
        $issuedToken->IssuedToAuthenticatable = $issuedToAuthenticatable;
        $issuedToken->IssuedAt = $issuedAt;
        $issuedToken->save();

        return $issuedToken;
    }

    private function resolveIssuedToAuthenticatable(mixed $subjects): ?string
    {
        $subjectList = is_array($subjects) ? $subjects : [$subjects];

        foreach ($subjectList as $subject) {
            if (! is_string($subject)) {
                continue;
            }

            if (str_starts_with($subject, 'authenticatable_id:')) {
                return substr($subject, strlen('authenticatable_id:'));
            }

            if (str_starts_with($subject, 'account_id:')) {
                return substr($subject, strlen('account_id:'));
            }

            if (str_starts_with($subject, 'client_id:')) {
                return substr($subject, strlen('client_id:'));
            }
        }

        return null;
    }
}

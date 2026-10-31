<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachableDataUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'AttachableForType' => ['sometimes', 'array', 'min:1'],
            'AttachableForType.*' => ['string', Rule::in(['Authenticatable', 'Account', 'Profile', 'AuthenticationProvider'])],
            'AuthenticationType' => ['sometimes', Rule::in(['NoAuthenticationNeeded', 'NeedsAuthentication', 'NeedsApproval', 'NeedsApprovalAndAuthentication'])],
            'MultiFactorAuthenticationType' => ['sometimes', Rule::in(['NoMultiFactorAuthenticationNeeded', 'UseMultiFactorIfAvailable', 'ForceMultiFactor'])],
            'AuthenticationAndOrApprovalOn' => ['nullable', 'array'],
            'AuthenticationAndOrApprovalOn.*' => ['string', Rule::in(['Execute', 'Create', 'Update', 'Delete'])],
            'AuthenticationProviderIdentifier' => ['nullable', 'ulid', 'exists:AuthenticationProviders,AuthenticationProviderIdentifier'],
            'ApprovalEntityIdentifier' => ['nullable', 'ulid', 'exists:ApprovalEntities,ApprovalEntityIdentifier'],
            'bIsUnique' => ['sometimes', 'boolean'],
            'bCanHaveMultiple' => ['sometimes', 'boolean'],
            'MaxAllowedCharacters' => ['sometimes', 'integer', 'min:1', 'max:65535'],
            'bInvertSafeList' => ['sometimes', 'boolean'],
            'bUseSafeList' => ['sometimes', 'boolean'],
            'SafeList' => ['nullable', 'string', 'max:1024'],
            'ValueMatch' => ['nullable', 'string', 'max:1024'],
            'ValueMatchType' => ['sometimes', Rule::in(['NoMatchType', 'Contains', 'StartsWith', 'EndsWith', 'RegExp'])],
            'AttachmentPossibility' => ['sometimes', 'array', 'min:1'],
            'AttachmentPossibility.*' => ['string', Rule::in(['OnAccountCreation', 'OnApplicationCreation', 'OnProfileCreation', 'InSettings'])],
            'AccessType' => ['sometimes', Rule::in(['NoAccess', 'PublicAvailable', 'NeedsAuthentication', 'NeedsExtraMultiFactorAuthenticationIfAvailable', 'TrustedApplicationsOnly'])],
        ];
    }
}

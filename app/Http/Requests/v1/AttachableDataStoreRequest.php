<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachableDataStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'AttachableDataName' => ['required', 'string', 'max:64'],
            'AttachableForType' => ['required', 'array', 'min:1'],
            'AttachableForType.*' => ['string', Rule::in(['Authenticatable', 'Account', 'Profile'])],
            'AuthenticationType' => ['required', Rule::in(['NoAuthenticationNeeded', 'NeedsAuthentication', 'NeedsApproval', 'NeedsApprovalAndAuthentication'])],
            'MultiFactorAuthenticationType' => ['required', Rule::in(['NoMultiFactorAuthenticationNeeded', 'UseMultiFactorIfAvailable', 'ForceMultiFactor'])],
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
            'ValueMatchType' => ['required', Rule::in(['NoMatchType', 'Contains', 'StartsWith', 'EndsWith', 'RegExp'])],
            'AttachmentPossibility' => ['required', 'array', 'min:1'],
            'AttachmentPossibility.*' => ['string', Rule::in(['OnAccountCreation', 'OnApplicationCreation', 'OnProfileCreation', 'InSettings'])],
            'AccessType' => ['required', Rule::in(['NoAccess', 'PublicAvailable', 'NeedsAuthentication', 'NeedsExtraMultiFactorAuthenticationIfAvailable', 'TrustedApplicationsOnly'])],
        ];
    }
}

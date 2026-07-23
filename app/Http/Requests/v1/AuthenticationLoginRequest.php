<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;

class AuthenticationLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'authenticatable_identifier' => ['required', 'uuid'],
            'provider_identifier' => ['required', 'string', 'max:128'],
            'input' => ['required', 'string', 'max:512'],
            'parameters' => ['sometimes', 'array'],
            'parameters.*.name' => ['required_with:parameters', 'string', 'max:128'],
            'parameters.*.value' => ['required_with:parameters', 'string', 'max:2048'],
            'login_bypass' => ['sometimes', 'boolean'],
        ];
    }
}

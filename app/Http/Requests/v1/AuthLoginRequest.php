<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;

class AuthLoginRequest extends FormRequest
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
        ];
    }
}

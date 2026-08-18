<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'old_password' => ['required', 'string'],

            // Same strength policy as registration, so this can't be used to downgrade to a weak password.
            'new_password' => [
                'required',
                'string',
                'different:old_password',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'old_password.required' => 'Please enter your current password.',
            'new_password.different' => 'Your new password must be different from your current one.',
            'new_password.confirmed' => 'The new password and its confirmation do not match.',
        ];
    }
}

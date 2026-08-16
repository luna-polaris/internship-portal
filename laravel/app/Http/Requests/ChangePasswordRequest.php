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
            // The current password must be supplied and is re-checked against
            // the stored hash in AuthController::changePassword().
            'old_password' => ['required', 'string'],

            // Same strength policy as registration — otherwise this endpoint
            // would be a way to downgrade an account to a weak password.
            // `confirmed` pairs with new_password_confirmation so a typo can't
            // silently lock the user out of their own account.
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

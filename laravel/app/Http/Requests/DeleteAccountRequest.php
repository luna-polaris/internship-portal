<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Re-checked against the stored hash in ProfileController::destroy().
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Enter your password to confirm account deletion.',
        ];
    }
}

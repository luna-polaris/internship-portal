<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeleteAccountRequest extends FormRequest
{
    /**
     * Admin accounts are created by AdminSeeder, not self-service, so they
     * can't be self-deleted. Refusing here rather than in the controller means
     * a consistent 403 whatever the request body contains, since FormRequest
     * validation would otherwise run first and answer 422.
     */
    public function authorize(): bool
    {
        return $this->user()?->role !== 'Admin';
    }

    /** Keep the refusal in the same {success, message} shape as the rest of the API. */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Admin accounts cannot be deleted.',
        ], 403));
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

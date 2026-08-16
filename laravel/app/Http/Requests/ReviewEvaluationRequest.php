<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'Admin';
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            // Rejecting sends the evaluation back to the employer, so they need to be told why.
            'remark'   => ['required_if:decision,reject', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'remark.required_if' => 'Tell the employer what needs changing before you reject it.',
        ];
    }
}
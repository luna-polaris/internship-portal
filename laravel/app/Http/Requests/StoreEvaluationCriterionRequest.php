<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEvaluationCriterionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'Admin';
    }

    public function rules(): array
    {
        $criterionId = $this->route('criterion')?->criterion_id ?? $this->route('criterion');

        return [
            'name'        => [
                'required', 'string', 'max:100',
                Rule::unique('evaluation_criteria', 'name')->ignore($criterionId, 'criterion_id'),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'category'    => ['required', Rule::in(['Technical', 'Soft Skills', 'Professionalism', 'Other'])],
            'max_score'   => ['required', 'integer', 'min:1', 'max:100'],
            'weight'      => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'  => 'A criterion with this name already exists.',
            'weight.max'   => 'A single criterion cannot be worth more than 100% of the total.',
        ];
    }
}
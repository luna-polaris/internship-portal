<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInternshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'skills_required' => ['nullable', 'array', 'max:20'],
            'skills_required.*' => ['string', 'max:50'],
            'min_cgpa' => ['nullable', 'numeric', 'between:0,4'],
            'category' => ['nullable', 'string', 'max:100'],
            'work_mode' => ['nullable', 'in:Onsite,Remote,Hybrid'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'allowance' => ['nullable', 'numeric', 'min:0'],
            'duration_months' => ['nullable', 'integer', 'min:1', 'max:60'],
            'vacancies' => ['nullable', 'integer', 'min:1'],
            'application_deadline' => ['nullable', 'date', 'after:today'],
        ];
    }
}

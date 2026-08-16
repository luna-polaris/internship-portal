<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'university' => ['nullable', 'string', 'max:100'],
            'faculty' => ['nullable', 'string', 'max:100'],
            'programme' => ['nullable', 'string', 'max:100'],
            'cgpa' => ['nullable', 'numeric', 'between:0,4'],
            'graduation_year' => ['nullable', 'digits:4'],
            'skills' => ['nullable', 'array', 'max:20'],
            'skills.*' => ['string', 'max:50'],
            'interests' => ['nullable', 'array', 'max:20'],
            'interests.*' => ['string', 'max:50'],
            'preferred_locations' => ['nullable', 'array', 'max:10'],
            'preferred_locations.*' => ['string', 'max:100'],
        ];
    }
}

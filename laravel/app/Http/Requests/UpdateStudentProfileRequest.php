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
        ];
    }
}

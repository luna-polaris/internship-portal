<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Admin accounts are never self-registered — see database/seeders/AdminSeeder.php.
            'role' => ['required', Rule::in(['Student', 'Employer'])],
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],

            'matric_no' => ['required_if:role,Student', 'string', 'max:20', 'unique:students,matric_no'],
            'university' => ['nullable', 'string', 'max:100'],
            'faculty' => ['nullable', 'string', 'max:100'],
            'programme' => ['nullable', 'string', 'max:100'],
            'cgpa' => ['nullable', 'numeric', 'between:0,4'],
            'graduation_year' => ['nullable', 'digits:4'],

            'position' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'company_name' => ['required_if:role,Employer', 'string', 'max:150'],
            'company_email' => ['nullable', 'email', 'max:100'],
            'company_phone' => ['nullable', 'string', 'max:20'],
            'industry' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'string', 'max:10'],
            'website' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ];
    }
}

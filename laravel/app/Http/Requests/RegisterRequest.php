<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    // Browser-side checks in register.blade.php are only for feedback; every rule here is enforced server-side too.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Admin accounts are never self-registered — see database/seeders/AdminSeeder.php.
            'role' => ['required', Rule::in(['Student', 'Employer'])],

            // Letters only; spaces are permitted so multi-word names still work.
            'full_name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z ]+$/'],

            'email' => ['required', 'email', 'max:100', 'unique:users,email'],

            // `confirmed` pairs this with the password_confirmation field.
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],

            // Malaysian mobile format: '01' followed by 8 or 9 digits (10-11 total).
            'phone' => ['nullable', 'string', 'regex:/^01[0-9]{8,9}$/'],

            'matric_no' => ['required_if:role,Student', 'string', 'max:20', 'unique:students,matric_no'],
            'university' => ['nullable', 'string', 'max:100'],
            'faculty' => ['nullable', 'string', 'max:100'],
            'programme' => ['nullable', 'string', 'max:100'],
            'cgpa' => ['nullable', 'numeric', 'between:0,4'],
            'graduation_year' => ['nullable', 'integer', 'between:2000,2050'],

            'position' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'company_name' => ['required_if:role,Employer', 'string', 'max:150'],
            'company_email' => ['nullable', 'email', 'max:100'],
            'company_phone' => ['nullable', 'string', 'regex:/^01[0-9]{8,9}$/'],
            'industry' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'string', 'max:10'],
            'website' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.regex' => 'Full name may only contain letters and spaces.',

            'password.confirmed' => 'Password confirmation does not match.',

            'phone.regex' => 'Phone must start with 01 and be 10 or 11 digits in total (e.g. 0123456789).',
            'company_phone.regex' => 'Company phone must start with 01 and be 10 or 11 digits in total (e.g. 0123456789).',

            'graduation_year.between' => 'Graduation year must be between 2000 and 2050.',
            'graduation_year.integer' => 'Graduation year must be a four-digit year between 2000 and 2050.',

            'matric_no.required_if' => 'Matric number is required for students.',
            'matric_no.unique' => 'That matric number is already registered.',
            'company_name.required_if' => 'Company name is required for employers.',

            'cgpa.between' => 'CGPA must be between 0.00 and 4.00.',
        ];
    }
}

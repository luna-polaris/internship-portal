<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'company_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('companies', 'company_name')
                    ->ignore($this->user()->employer?->company?->company_id, 'company_id'),
            ],
            'registration_no' => ['nullable', 'string', 'max:50'],
            'industry' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'string', 'max:10'],
            'website' => ['nullable', 'string', 'max:150'],
            'company_email' => ['nullable', 'email', 'max:100'],
            'company_phone' => ['nullable', 'string', 'regex:/^01[0-9]{8,9}$/'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.unique' => 'That company name is already registered.',
            'company_phone.regex' => 'Company phone must start with 01 and be 10 or 11 digits in total (e.g. 0123456789).',
        ];
    }
}

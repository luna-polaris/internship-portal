<?php

namespace App\Http\Requests\WebService;

use App\Support\WebService\ServiceResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;


class GetInternshipStatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requestId' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9\-]+$/'],
            'timeStamp' => ['required', 'string', 'date_format:' . config('webservice.timestamp_format')],
        ];
    }

    public function messages(): array
    {
        return [
            'requestId.required' => 'requestId is required so the call can be traced.',
            'requestId.regex' => 'requestId may contain letters, numbers and hyphens only.',
            'timeStamp.date_format' => 'timeStamp must be formatted as YYYY-MM-DD HH:MM:SS.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ServiceResponse::error(
                'Request failed validation.',
                $this->input('requestId'),
                422,
                ['errors' => $validator->errors()->toArray()],
            )
        );
    }
}

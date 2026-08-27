<?php

namespace App\Http\Requests\WebService;

use App\Support\WebService\ServiceResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Validates a getInternshipStats call against the Interface Agreement.
 *
 * Unlike getUserInfo, this contract makes requestId mandatory: the call carries no
 * business identifier at all, so the correlation id is the only handle either side
 * has on a particular exchange.
 */
class GetInternshipStatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The caller is authenticated by VerifyServiceKey before this runs.
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

    /** Rejections are re-wrapped so even a validation failure satisfies the contract. */
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

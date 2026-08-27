<?php

namespace App\Http\Requests\WebService;

use App\Support\WebService\ServiceResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Validates a getUserInfo call against the Interface Agreement.
 *
 * Every rule here mirrors one row of the IFA request table, so the contract and
 * the enforcement cannot drift apart.
 */
class GetUserInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The caller is authenticated by VerifyServiceKey before this runs.
        return true;
    }

    public function rules(): array
    {
        return [
            // Format column: numeric only. Kept as a string in the contract so a
            // consumer written in another language cannot lose precision on it.
            'userId' => ['required', 'string', 'regex:/^[0-9]+$/'],

            // 1: contact details, 2: role profile, 3: both.
            'queryFlag' => ['required', 'integer', Rule::in([1, 2, 3])],

            // Mandatory tracking field required by the IFA.
            'timeStamp' => ['required', 'string', 'date_format:' . config('webservice.timestamp_format')],

            // Optional correlation id; echoed back untouched when supplied.
            'requestId' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9\-]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'userId.regex' => 'userId must contain digits only.',
            'queryFlag.in' => 'queryFlag must be 1 (contact), 2 (profile) or 3 (both).',
            'timeStamp.date_format' => 'timeStamp must be formatted as YYYY-MM-DD HH:MM:SS.',
            'requestId.regex' => 'requestId may contain letters, numbers and hyphens only.',
        ];
    }

    /**
     * Laravel's default 422 body has no `status` field, which would break the
     * contract on exactly the responses a consumer is most likely to mishandle.
     * Rejections are re-wrapped in the standard envelope instead.
     */
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

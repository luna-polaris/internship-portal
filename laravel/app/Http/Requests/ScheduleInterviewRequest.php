<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScheduleInterviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date', 'after:now'],
            'mode' => ['required', Rule::in(['Onsite', 'Online'])],
            'location' => ['required_if:mode,Onsite', 'nullable', 'string', 'max:255'],
            'meeting_link' => ['required_if:mode,Online', 'nullable', 'string', 'max:255', 'url'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'scheduled_at.after' => 'The interview must be scheduled for a future date and time.',
            'location.required_if' => 'A venue is required for an onsite interview.',
            'meeting_link.required_if' => 'A meeting link is required for an online interview.',
        ];
    }
}

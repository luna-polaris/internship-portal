<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'Employer';
    }

    public function rules(): array
    {
        return [
            'student_id'      => ['required', 'integer', 'exists:students,student_id'],
            'application_id'  => ['nullable', 'integer'],
            'period'          => ['required', Rule::in(['Midterm', 'Final'])],
            'strengths'       => ['nullable', 'string', 'max:2000'],
            'improvements'    => ['nullable', 'string', 'max:2000'],
            'overall_comment' => ['nullable', 'string', 'max:2000'],
            'action'          => ['required', Rule::in(['save', 'submit'])],

            'scores'                => ['required', 'array', 'min:1'],
            'scores.*.criterion_id' => ['required', 'integer', 'exists:evaluation_criteria,criterion_id'],
            'scores.*.score'        => ['required', 'numeric', 'min:0'],
            'scores.*.remark'       => ['nullable', 'string', 'max:255'],
        ];
    }

    // Cross-field checks (score vs. its own criterion's scale, full coverage on submit) that plain rules can't express.
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $criteria = \App\Models\EvaluationCriterion::active()
                ->get()
                ->keyBy('criterion_id');

            foreach ((array) $this->input('scores', []) as $i => $row) {
                $criterion = $criteria->get($row['criterion_id'] ?? null);
                if (! $criterion) {
                    $validator->errors()->add("scores.{$i}.criterion_id", 'This criterion is no longer in use.');
                    continue;
                }
                if (isset($row['score']) && $row['score'] > $criterion->max_score) {
                    $validator->errors()->add(
                        "scores.{$i}.score",
                        "{$criterion->name} is scored out of {$criterion->max_score}."
                    );
                }
            }

            if ($this->input('action') === 'submit') {
                $given   = collect($this->input('scores', []))->pluck('criterion_id')->all();
                $missing = $criteria->keys()->diff($given);

                if ($missing->isNotEmpty()) {
                    $names = $criteria->only($missing->all())->pluck('name')->implode(', ');
                    $validator->errors()->add('scores', "Score every criterion before submitting. Still missing: {$names}.");
                }

                if (blank($this->input('overall_comment'))) {
                    $validator->errors()->add('overall_comment', 'Add an overall comment before submitting.');
                }
            }
        });
    }
}
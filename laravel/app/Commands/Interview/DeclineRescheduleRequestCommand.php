<?php

namespace App\Commands\Interview;

use App\Models\Interview;

/** Employer/Admin rejects the student's proposed time — the original schedule stands. */
class DeclineRescheduleRequestCommand extends InterviewCommand
{
    public function __construct(
        private Interview $interview,
        private ?string $reason,
        int $performedBy,
    ) {
        parent::__construct($performedBy);
    }

    protected function apply(): Interview
    {
        $this->interview->update([
            'requested_scheduled_at' => null,
            'requested_reason' => null,
            'requested_by' => null,
        ]);

        return $this->interview;
    }

    protected function action(): string
    {
        return 'reschedule_declined';
    }

    protected function details(Interview $interview): array
    {
        return ['decline_reason' => $this->reason];
    }
}

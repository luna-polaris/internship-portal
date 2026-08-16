<?php

namespace App\Commands\Interview;

use App\Models\Interview;

/** Employer/Admin confirms the student's proposed time — this is what actually moves the interview. */
class ApproveRescheduleRequestCommand extends InterviewCommand
{
    private ?array $previous = null;

    public function __construct(
        private Interview $interview,
        int $performedBy,
    ) {
        parent::__construct($performedBy);
    }

    protected function apply(): Interview
    {
        $this->previous = ['scheduled_at' => $this->interview->scheduled_at?->toDateTimeString()];

        $this->interview->update([
            'scheduled_at' => $this->interview->requested_scheduled_at,
            'status' => Interview::STATUS_RESCHEDULED,
            'requested_scheduled_at' => null,
            'requested_reason' => null,
            'requested_by' => null,
        ]);

        return $this->interview;
    }

    protected function action(): string
    {
        return 'reschedule_approved';
    }

    protected function details(Interview $interview): array
    {
        return [
            'from' => $this->previous,
            'to' => ['scheduled_at' => $interview->scheduled_at?->toDateTimeString()],
        ];
    }
}

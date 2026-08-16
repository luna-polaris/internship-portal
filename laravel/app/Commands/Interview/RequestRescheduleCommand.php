<?php

namespace App\Commands\Interview;

use App\Models\Interview;

/** Student proposes a new time — the interview's actual schedule is untouched until an employer/admin approves it. */
class RequestRescheduleCommand extends InterviewCommand
{
    public function __construct(
        private Interview $interview,
        private array $data,
        int $performedBy,
    ) {
        parent::__construct($performedBy);
    }

    protected function apply(): Interview
    {
        $this->interview->update([
            'requested_scheduled_at' => $this->data['requested_scheduled_at'],
            'requested_reason' => $this->data['reason'] ?? null,
            'requested_by' => $this->performedBy,
        ]);

        return $this->interview;
    }

    protected function action(): string
    {
        return 'reschedule_requested';
    }

    protected function details(Interview $interview): array
    {
        return [
            'requested_scheduled_at' => $interview->requested_scheduled_at?->toDateTimeString(),
            'reason' => $interview->requested_reason,
        ];
    }
}

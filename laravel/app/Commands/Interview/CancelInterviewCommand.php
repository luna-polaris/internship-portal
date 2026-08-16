<?php

namespace App\Commands\Interview;

use App\Models\Interview;

class CancelInterviewCommand extends InterviewCommand
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
            'status' => Interview::STATUS_CANCELLED,
            'cancelled_reason' => $this->reason,
        ]);

        return $this->interview;
    }

    protected function action(): string
    {
        return 'cancelled';
    }

    protected function details(Interview $interview): array
    {
        return ['reason' => $this->reason];
    }
}

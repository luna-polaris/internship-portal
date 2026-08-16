<?php

namespace App\Commands\Interview;

use App\Models\Interview;

class CompleteInterviewCommand extends InterviewCommand
{
    public function __construct(
        private Interview $interview,
        int $performedBy,
    ) {
        parent::__construct($performedBy);
    }

    protected function apply(): Interview
    {
        $this->interview->update(['status' => Interview::STATUS_COMPLETED]);

        return $this->interview;
    }

    protected function action(): string
    {
        return 'completed';
    }
}

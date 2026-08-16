<?php

namespace App\Commands\Interview;

use App\Models\Application;
use App\Models\Interview;

class ScheduleInterviewCommand extends InterviewCommand
{
    public function __construct(
        private Application $application,
        private array $data,
        int $performedBy,
    ) {
        parent::__construct($performedBy);
    }

    protected function apply(): Interview
    {
        return Interview::create($this->data + [
            'application_id' => $this->application->application_id,
            'status' => Interview::STATUS_SCHEDULED,
            'created_by' => $this->performedBy,
        ]);
    }

    protected function action(): string
    {
        return 'scheduled';
    }

    protected function details(Interview $interview): array
    {
        return [
            'scheduled_at' => $interview->scheduled_at?->toDateTimeString(),
            'mode' => $interview->mode,
        ];
    }
}

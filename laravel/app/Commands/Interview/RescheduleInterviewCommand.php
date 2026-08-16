<?php

namespace App\Commands\Interview;

use App\Models\Interview;

class RescheduleInterviewCommand extends InterviewCommand
{
    private ?array $previous = null;

    public function __construct(
        private Interview $interview,
        private array $data,
        int $performedBy,
    ) {
        parent::__construct($performedBy);
    }

    protected function apply(): Interview
    {
        $this->previous = [
            'scheduled_at' => $this->interview->scheduled_at?->toDateTimeString(),
            'mode' => $this->interview->mode,
        ];

        $this->interview->update($this->data + ['status' => Interview::STATUS_RESCHEDULED]);

        return $this->interview;
    }

    protected function action(): string
    {
        return 'rescheduled';
    }

    protected function details(Interview $interview): array
    {
        return [
            'from' => $this->previous,
            'to' => [
                'scheduled_at' => $interview->scheduled_at?->toDateTimeString(),
                'mode' => $interview->mode,
            ],
        ];
    }
}

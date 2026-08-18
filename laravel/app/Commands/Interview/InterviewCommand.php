<?php

namespace App\Commands\Interview;

use App\Models\Interview;
use App\Models\InterviewLog;
use Illuminate\Support\Facades\DB;

/** Each concrete command applies its own state change; execute() wraps it in a transaction and appends one interview_logs row. */
abstract class InterviewCommand
{
    public function __construct(protected int $performedBy) {}

    final public function execute(): Interview
    {
        return DB::transaction(function () {
            $interview = $this->apply();

            InterviewLog::create([
                'interview_id' => $interview->interview_id,
                'action' => $this->action(),
                'performed_by' => $this->performedBy,
                'details' => $this->details($interview),
            ]);

            return $interview;
        });
    }

    abstract protected function apply(): Interview;

    abstract protected function action(): string;

    protected function details(Interview $interview): array
    {
        return [];
    }
}

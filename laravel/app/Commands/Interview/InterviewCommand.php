<?php

namespace App\Commands\Interview;

use App\Models\Interview;
use App\Models\InterviewLog;
use Illuminate\Support\Facades\DB;

/**
 * Module 4 — Command pattern: each scheduling action (Schedule, Reschedule,
 * Cancel, Complete) is its own object instead of a controller method doing
 * everything inline. That makes every action uniformly executable and
 * auditable — `execute()` always wraps the state change in a transaction
 * and appends one `interview_logs` row, whatever the concrete action is.
 */
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

    /** The state-changing part of the command — create or update the interview row. */
    abstract protected function apply(): Interview;

    /** Short verb used as the audit log's `action` column. */
    abstract protected function action(): string;

    /** Optional structured snapshot of what changed, stored alongside the log entry. */
    protected function details(Interview $interview): array
    {
        return [];
    }
}

<?php

namespace App\Support\Filters;

use Illuminate\Database\Eloquent\Builder;

/** Filters by exact work mode (Onsite, Remote, Hybrid). */
class WorkModeFilterStrategy implements InternshipFilterStrategy
{
    public function supports(array $filters): bool
    {
        return filled($filters['work_mode'] ?? null);
    }

    public function apply(Builder $query, array $filters): void
    {
        $query->where('work_mode', $filters['work_mode']);
    }
}

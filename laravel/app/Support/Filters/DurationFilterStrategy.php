<?php

namespace App\Support\Filters;

use Illuminate\Database\Eloquent\Builder;

/** Filters by a minimum and/or maximum internship duration, in months. */
class DurationFilterStrategy implements InternshipFilterStrategy
{
    public function supports(array $filters): bool
    {
        return filled($filters['min_duration'] ?? null) || filled($filters['max_duration'] ?? null);
    }

    public function apply(Builder $query, array $filters): void
    {
        if (filled($filters['min_duration'] ?? null)) {
            $query->where('duration_months', '>=', $filters['min_duration']);
        }

        if (filled($filters['max_duration'] ?? null)) {
            $query->where('duration_months', '<=', $filters['max_duration']);
        }
    }
}

<?php

namespace App\Support\Filters;

use Illuminate\Database\Eloquent\Builder;

/** Filters by a minimum and/or maximum monthly allowance. */
class AllowanceRangeFilterStrategy implements InternshipFilterStrategy
{
    public function supports(array $filters): bool
    {
        return filled($filters['min_allowance'] ?? null) || filled($filters['max_allowance'] ?? null);
    }

    public function apply(Builder $query, array $filters): void
    {
        if (filled($filters['min_allowance'] ?? null)) {
            $query->where('allowance', '>=', $filters['min_allowance']);
        }

        if (filled($filters['max_allowance'] ?? null)) {
            $query->where('allowance', '<=', $filters['max_allowance']);
        }
    }
}

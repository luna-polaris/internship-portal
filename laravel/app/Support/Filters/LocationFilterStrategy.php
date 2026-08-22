<?php

namespace App\Support\Filters;

use Illuminate\Database\Eloquent\Builder;

/** Filters by city and/or state. */
class LocationFilterStrategy implements InternshipFilterStrategy
{
    public function supports(array $filters): bool
    {
        return filled($filters['city'] ?? null) || filled($filters['state'] ?? null);
    }

    public function apply(Builder $query, array $filters): void
    {
        if (filled($filters['city'] ?? null)) {
            $query->where('city', 'like', "%{$filters['city']}%");
        }

        if (filled($filters['state'] ?? null)) {
            $query->where('state', 'like', "%{$filters['state']}%");
        }
    }
}

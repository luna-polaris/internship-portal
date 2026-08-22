<?php

namespace App\Support\Filters;

use Illuminate\Database\Eloquent\Builder;

/** Filters by industry/category (e.g. IT, Finance, Healthcare). */
class IndustryFilterStrategy implements InternshipFilterStrategy
{
    public function supports(array $filters): bool
    {
        return filled($filters['category'] ?? null);
    }

    public function apply(Builder $query, array $filters): void
    {
        $query->where('category', 'like', "%{$filters['category']}%");
    }
}

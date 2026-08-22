<?php

namespace App\Support\Filters;

use Illuminate\Database\Eloquent\Builder;

/** Free-text search over the title and description. */
class KeywordFilterStrategy implements InternshipFilterStrategy
{
    public function supports(array $filters): bool
    {
        return filled($filters['q'] ?? null);
    }

    public function apply(Builder $query, array $filters): void
    {
        $value = $filters['q'];

        $query->where(function (Builder $q) use ($value) {
            $q->where('title', 'like', "%{$value}%")->orWhere('description', 'like', "%{$value}%");
        });
    }
}

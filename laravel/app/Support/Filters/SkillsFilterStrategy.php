<?php

namespace App\Support\Filters;

use Illuminate\Database\Eloquent\Builder;

/** Filters to internships requiring at least one of the given skills. */
class SkillsFilterStrategy implements InternshipFilterStrategy
{
    public function supports(array $filters): bool
    {
        return filled($filters['skills'] ?? null);
    }

    public function apply(Builder $query, array $filters): void
    {
        $skills = is_array($filters['skills']) ? $filters['skills'] : [$filters['skills']];

        $query->where(function (Builder $q) use ($skills) {
            foreach ($skills as $skill) {
                $q->orWhereJsonContains('skills_required', $skill);
            }
        });
    }
}

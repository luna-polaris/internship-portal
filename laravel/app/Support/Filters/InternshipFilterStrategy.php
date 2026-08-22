<?php

namespace App\Support\Filters;

use Illuminate\Database\Eloquent\Builder;

/** One interchangeable search/filter algorithm the Internship query context can apply. */
interface InternshipFilterStrategy
{
    /** Whether this strategy is relevant for the given raw filter input. */
    public function supports(array $filters): bool;

    /** Constrain the query according to this strategy's own filtering logic. */
    public function apply(Builder $query, array $filters): void;
}

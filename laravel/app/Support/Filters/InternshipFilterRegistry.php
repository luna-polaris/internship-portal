<?php

namespace App\Support\Filters;

/** The set of filter strategies available to the internship search context. */
class InternshipFilterRegistry
{
    /** @return InternshipFilterStrategy[] */
    public static function all(): array
    {
        return [
            new KeywordFilterStrategy(),
            new IndustryFilterStrategy(),
            new LocationFilterStrategy(),
            new WorkModeFilterStrategy(),
            new SkillsFilterStrategy(),
            new AllowanceRangeFilterStrategy(),
            new DurationFilterStrategy(),
        ];
    }
}

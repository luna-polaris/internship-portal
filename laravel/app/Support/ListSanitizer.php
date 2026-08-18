<?php

namespace App\Support;

/** Shared cleanup for free-text list fields (skills, interests, locations) feeding the recommendation engine: strips markup, trims, dedupes, and caps length/count before storage. */
class ListSanitizer
{
    public static function toJson(mixed $value, int $maxItems = 20, int $maxLength = 50): ?string
    {
        $clean = static::toArray($value, $maxItems, $maxLength);

        return empty($clean) ? null : json_encode($clean);
    }

    public static function toArray(mixed $value, int $maxItems = 20, int $maxLength = 50): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn ($item) => trim(strip_tags((string) $item)))
            ->filter(fn ($item) => $item !== '')
            ->map(fn ($item) => mb_substr($item, 0, $maxLength))
            ->unique(fn ($item) => mb_strtolower($item))
            ->values()
            ->take($maxItems)
            ->all();
    }
}

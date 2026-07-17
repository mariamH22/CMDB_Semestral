<?php
declare(strict_types=1);

namespace App\Core;

final class Sanitizer
{
    public static function text(?string $value, int $maxLength = 255): string
    {
        $value = trim((string) $value);
        $value = strip_tags($value);
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return function_exists('mb_substr')
            ? \mb_substr($value, 0, $maxLength)
            : substr($value, 0, $maxLength);
    }

    public static function email(?string $value): string
    {
        return strtolower(trim((string) $value));
    }

    public static function decimal(mixed $value): float
    {
        return (float) str_replace(',', '.', (string) $value);
    }

    public static function integer(mixed $value): int
    {
        return (int) $value;
    }
}

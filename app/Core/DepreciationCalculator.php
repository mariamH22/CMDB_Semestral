<?php
declare(strict_types=1);

namespace App\Core;

final class DepreciationCalculator
{
    public static function limitDate(string $entryDate, int $usefulLifeMonths): string
    {
        $date = self::date($entryDate);
        $months = max(1, $usefulLifeMonths);

        return $date->modify('+' . $months . ' months')->format('Y-m-d');
    }

    public static function elapsedMonths(string $entryDate, string $asOfDate, int $usefulLifeMonths): int
    {
        $entry = self::date($entryDate);
        $asOf = self::date($asOfDate);
        if ($asOf < $entry) {
            return 0;
        }

        $diff = $entry->diff($asOf);
        $months = ($diff->y * 12) + $diff->m;
        if ($diff->d > 0) {
            $months++;
        }

        return max(0, min(max(1, $usefulLifeMonths), $months));
    }

    public static function straightLine(float $cost, int $usefulLifeMonths, string $entryDate, string $asOfDate): array
    {
        $months = max(1, $usefulLifeMonths);
        $monthly = round($cost / $months, 2);
        $elapsed = self::elapsedMonths($entryDate, $asOfDate, $months);
        $accumulated = min($cost, round($monthly * $elapsed, 2));

        return [
            'vida_util_meses' => $months,
            'fecha_limite' => self::limitDate($entryDate, $months),
            'depreciacion_mensual' => $monthly,
            'meses_transcurridos' => $elapsed,
            'depreciacion_acumulada' => $accumulated,
            'valor_libros' => max(0.0, round($cost - $accumulated, 2)),
        ];
    }

    private static function date(string $value): \DateTimeImmutable
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date ?: new \DateTimeImmutable('today');
    }
}

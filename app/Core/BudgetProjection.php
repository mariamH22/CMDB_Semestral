<?php
declare(strict_types=1);

namespace App\Core;

final class BudgetProjection
{
    public static function years(string $type, int $startYear): array
    {
        return match ($type) {
            'ANUAL' => [$startYear],
            'QUINQUENAL' => range($startYear, $startYear + 4),
            default => throw new \RuntimeException('Tipo de presupuesto inválido.'),
        };
    }

    public static function yearIndex(int $startYear, int $year): int
    {
        return max(0, $year - $startYear);
    }

    public static function factor(int $yearIndex, float $growthPercent, float $inflationPercent): float
    {
        return pow(1 + ($growthPercent / 100), $yearIndex)
            * pow(1 + ($inflationPercent / 100), $yearIndex);
    }

    public static function projectedUnitCost(float $baseUnitCost, int $yearIndex, float $growthPercent, float $inflationPercent): float
    {
        return round($baseUnitCost * self::factor($yearIndex, $growthPercent, $inflationPercent), 2);
    }

    public static function subtotal(int $quantity, ?float $unitCost): ?float
    {
        if ($unitCost === null) {
            return null;
        }

        return round($quantity * $unitCost, 2);
    }

    public static function projectBase(float $baseBudget, int $yearIndex, float $growthPercent, float $inflationPercent): float
    {
        return round($baseBudget * self::factor($yearIndex, $growthPercent, $inflationPercent), 2);
    }

    public static function hasCost(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }

    public static function noCostReason(array $need): string
    {
        if (!array_key_exists('costo_unitario_estimado', $need) && !array_key_exists('costo_estimado', $need)) {
            return 'La instalación actual no tiene columna de costo estimado.';
        }

        return 'Solicitud sin costo unitario estimado.';
    }

    public static function summarizeRows(array $rows): array
    {
        $summary = [
            'base' => 0.0,
            'total' => 0.0,
            'without_cost_count' => 0,
            'without_cost' => [],
            'by_year' => [],
            'by_category' => [],
            'by_type' => [],
            'by_priority' => [],
            'by_status' => [],
        ];

        $seenNoCost = [];
        foreach ($rows as $row) {
            $hasCost = (int) ($row['tiene_costo'] ?? 1) === 1;
            $year = (int) ($row['anio'] ?? 0);
            $subtotal = $hasCost ? (float) ($row['subtotal'] ?? 0) : 0.0;

            if ($hasCost) {
                $summary['total'] += $subtotal;
                if ((int) ($row['year_index'] ?? 0) === 0) {
                    $summary['base'] += $subtotal;
                }
                self::addGroup($summary['by_year'], (string) $year, $subtotal);
                self::addGroup($summary['by_category'], (string) ($row['categoria_nombre'] ?? 'Sin categoría'), $subtotal);
                self::addGroup($summary['by_type'], (string) ($row['tipo_necesidad'] ?? 'Sin tipo'), $subtotal);
                self::addGroup($summary['by_priority'], (string) ($row['prioridad'] ?? 'Sin prioridad'), $subtotal);
                self::addGroup($summary['by_status'], (string) ($row['estado_solicitud'] ?? 'Sin estado'), $subtotal);
                continue;
            }

            $key = (string) ($row['necesidad_id'] ?? $row['descripcion'] ?? spl_object_id((object) $row));
            if (!isset($seenNoCost[$key])) {
                $seenNoCost[$key] = true;
                $summary['without_cost_count']++;
                $summary['without_cost'][] = $row;
            }
        }

        $summary['base'] = round($summary['base'], 2);
        $summary['total'] = round($summary['total'], 2);

        return $summary;
    }

    private static function addGroup(array &$groups, string $label, float $amount): void
    {
        if (!isset($groups[$label])) {
            $groups[$label] = ['label' => $label, 'total' => 0.0, 'count' => 0];
        }

        $groups[$label]['total'] = round($groups[$label]['total'] + $amount, 2);
        $groups[$label]['count']++;
    }
}

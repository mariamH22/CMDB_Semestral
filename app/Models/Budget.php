<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\BudgetProjection;
use App\Core\Database;
use App\Core\NeedStatus;

final class Budget
{
    public function __construct(private Database $db)
    {
    }

    public function schemaReady(): bool
    {
        return $this->db->tableExists('presupuestos') && $this->db->tableExists('presupuesto_detalles');
    }

    public function all(): array
    {
        if (!$this->schemaReady()) {
            return [];
        }

        return $this->db->fetchAll(
            "SELECT p.*, u.nombre_usuario
             FROM presupuestos p
             LEFT JOIN usuarios u ON u.id = p.created_by
             ORDER BY p.created_at DESC"
        );
    }

    public function details(int $budgetId): array
    {
        if (!$this->schemaReady()) {
            return [];
        }

        return $this->db->fetchAll(
            "SELECT d.*, c.nombre AS categoria_nombre
             FROM presupuesto_detalles d
             LEFT JOIN categorias c ON c.id = d.categoria_id
             WHERE d.presupuesto_id = :id
             ORDER BY d.anio, d.id",
            ['id' => $budgetId]
        );
    }

    public function summary(int $budgetId): array
    {
        return BudgetProjection::summarizeRows($this->details($budgetId));
    }

    public function find(int $budgetId): ?array
    {
        if (!$this->schemaReady()) {
            return null;
        }

        return $this->db->fetch(
            "SELECT p.*, u.nombre_usuario
             FROM presupuestos p
             LEFT JOIN usuarios u ON u.id = p.created_by
             WHERE p.id = :id",
            ['id' => $budgetId]
        );
    }

    public function generateFromNeeds(
        string $name,
        string $type,
        int $startYear,
        ?int $userId,
        float $annualGrowth = 0.0,
        float $annualInflation = 0.0,
        array $filters = []
    ): int
    {
        if (!$this->schemaReady()) {
            throw new \RuntimeException('La migración de presupuesto no está aplicada.');
        }

        $years = BudgetProjection::years($type, $startYear);
        $endYear = max($years);

        return $this->db->transaction(function (Database $db) use ($name, $type, $startYear, $endYear, $years, $userId, $annualGrowth, $annualInflation, $filters): int {
            [$where, $params] = $this->needFilters($filters);
            $needs = $db->fetchAll(
                "SELECT n.*
                 FROM necesidades n
                 WHERE " . implode(' AND ', $where) . "
                 ORDER BY n.prioridad DESC, n.created_at ASC",
                $params
            );

            $budgetData = [
                'nombre' => $name,
                'tipo' => $type,
                'anio_inicio' => $startYear,
                'anio_fin' => $endYear,
                'total_estimado' => 0,
                'estado' => 'BORRADOR',
                'created_by' => $userId,
                'presupuesto_base' => 0,
                'inflacion_anual' => $annualInflation,
                'crecimiento_anual' => $annualGrowth,
                'total_quinquenal' => 0,
                'registros_sin_costo' => 0,
                'supuestos' => $this->assumptions($type, $startYear, $endYear, $annualGrowth, $annualInflation),
                'filtros_json' => json_encode($filters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];

            $budgetId = $this->insertBudget($budgetData);

            $hasEstimatedCost = $db->columnExists('necesidades', 'costo_estimado');
            $hasUnitEstimatedCost = $db->columnExists('necesidades', 'costo_unitario_estimado');
            $hasQuantity = $db->columnExists('necesidades', 'cantidad');
            $hasTargetYear = $db->columnExists('necesidades', 'anio_objetivo');
            $total = 0.0;
            $baseBudget = 0.0;
            $withoutCost = [];

            // Se proyecta cada necesidad por año de planificación respetando la configuración del DB del entorno.
            foreach ($needs as $need) {
                $quantity = $hasQuantity ? max(0, (int) ($need['cantidad'] ?? 0)) : 1;
                $baseUnitCost = $this->unitCostFromNeed($need, $hasUnitEstimatedCost, $hasEstimatedCost, max(1, $quantity));
                $hasCost = $baseUnitCost !== null && $quantity > 0;

                if (!$hasCost) {
                    if (!$db->columnExists('presupuesto_detalles', 'tiene_costo')) {
                        throw new \RuntimeException('Aplique la migración de presupuesto formal para separar solicitudes sin costo estimado.');
                    }
                    $withoutCost[(int) $need['id']] = true;
                    $this->insertBudgetDetail($this->detailData(
                        $budgetId,
                        $need,
                        $startYear,
                        0,
                        $quantity,
                        null,
                        null,
                        $annualGrowth,
                        $annualInflation,
                        BudgetProjection::noCostReason($need)
                    ));
                    continue;
                }

                foreach ($years as $year) {
                    $yearIndex = BudgetProjection::yearIndex($startYear, $year);
                    $unitCost = BudgetProjection::projectedUnitCost($baseUnitCost, $yearIndex, $annualGrowth, $annualInflation);
                    $yearSubtotal = BudgetProjection::subtotal($quantity, $unitCost) ?? 0.0;
                    $total += $yearSubtotal;
                    if ($yearIndex === 0) {
                        $baseBudget += $yearSubtotal;
                    }

                    $this->insertBudgetDetail($this->detailData(
                        $budgetId,
                        $need,
                        $year,
                        $yearIndex,
                        $quantity,
                        $baseUnitCost,
                        $unitCost,
                        $annualGrowth,
                        $annualInflation,
                        null
                    ));
                }
            }

            $this->updateBudgetTotals($budgetId, $baseBudget, $total, count($withoutCost));

            return $budgetId;
        });
    }

    private function needFilters(array $filters): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['estado'])) {
            $formalStatus = NeedStatus::normalize((string) $filters['estado']);
            NeedStatus::assertValid($formalStatus);
            $legacyStatus = NeedStatus::storageStatus($formalStatus, false);
            $where[] = '(n.estado = :estado_formal OR n.estado = :estado_legacy)';
            $params['estado_formal'] = $formalStatus;
            $params['estado_legacy'] = $legacyStatus;
        } else {
            $where[] = "n.estado IN ('PENDIENTE', 'EN_REVISION', 'EN_ESPERA', 'EN_TRAMITE')";
        }

        if (!empty($filters['anio']) && $this->db->columnExists('necesidades', 'anio_objetivo')) {
            $where[] = 'n.anio_objetivo = :anio';
            $params['anio'] = (int) $filters['anio'];
        }

        if (!empty($filters['categoria_id'])) {
            $where[] = 'n.categoria_id = :categoria_id';
            $params['categoria_id'] = (int) $filters['categoria_id'];
        }

        if (!empty($filters['tipo'])) {
            $where[] = 'n.tipo_necesidad = :tipo';
            $params['tipo'] = (string) $filters['tipo'];
        }

        if (!empty($filters['prioridad'])) {
            $where[] = 'n.prioridad = :prioridad';
            $params['prioridad'] = (string) $filters['prioridad'];
        }

        return [$where, $params];
    }

    private function unitCostFromNeed(array $need, bool $hasUnitEstimatedCost, bool $hasEstimatedCost, int $quantity): ?float
    {
        if ($hasUnitEstimatedCost && BudgetProjection::hasCost($need['costo_unitario_estimado'] ?? null)) {
            return (float) $need['costo_unitario_estimado'];
        }

        if (!$hasEstimatedCost || !BudgetProjection::hasCost($need['costo_estimado'] ?? null)) {
            return null;
        }

        $estimated = (float) $need['costo_estimado'];

        return $quantity > 0 ? round($estimated / $quantity, 2) : null;
    }

    private function insertBudget(array $data): int
    {
        $columns = ['nombre', 'tipo', 'anio_inicio', 'anio_fin', 'total_estimado', 'estado', 'created_by'];
        foreach ($this->budgetOptionalColumns() as $column) {
            if (array_key_exists($column, $data)) {
                $columns[] = $column;
            }
        }

        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

        return $this->db->insert(
            "INSERT INTO presupuestos (" . implode(', ', $columns) . ")
             VALUES (" . implode(', ', $placeholders) . ")",
            array_intersect_key($data, array_flip($columns))
        );
    }

    private function updateBudgetTotals(int $budgetId, float $baseBudget, float $total, int $withoutCostCount): void
    {
        $sets = ['total_estimado = :total_estimado'];
        $params = [
            'id' => $budgetId,
            'total_estimado' => round($total, 2),
        ];

        foreach ([
            'presupuesto_base' => round($baseBudget, 2),
            'total_quinquenal' => round($total, 2),
            'registros_sin_costo' => $withoutCostCount,
        ] as $column => $value) {
            if ($this->db->columnExists('presupuestos', $column)) {
                $sets[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
        }

        $this->db->execute(
            "UPDATE presupuestos SET " . implode(', ', $sets) . " WHERE id = :id",
            $params
        );
    }

    private function detailData(
        int $budgetId,
        array $need,
        int $year,
        int $yearIndex,
        int $quantity,
        ?float $baseUnitCost,
        ?float $projectedUnitCost,
        float $annualGrowth,
        float $annualInflation,
        ?string $noCostReason
    ): array {
        $hasCost = $projectedUnitCost !== null && $noCostReason === null;
        $factor = BudgetProjection::factor($yearIndex, $annualGrowth, $annualInflation);

        return [
            'presupuesto_id' => $budgetId,
            'categoria_id' => $need['categoria_id'] ?: null,
            'necesidad_id' => (int) $need['id'],
            'tipo_necesidad' => $need['tipo_necesidad'],
            'descripcion' => $need['descripcion'],
            'cantidad' => $quantity,
            'costo_unitario' => $projectedUnitCost ?? 0.0,
            'subtotal' => $hasCost ? (BudgetProjection::subtotal($quantity, $projectedUnitCost) ?? 0.0) : 0.0,
            'anio' => $year,
            'costo_base' => $baseUnitCost,
            'year_index' => $yearIndex,
            'factor_proyeccion' => round($factor, 8),
            'inflacion_anual' => $annualInflation,
            'crecimiento_anual' => $annualGrowth,
            'tiene_costo' => $hasCost ? 1 : 0,
            'motivo_sin_costo' => $noCostReason,
            'prioridad' => $need['prioridad'] ?? null,
            'estado_solicitud' => isset($need['estado']) ? NeedStatus::normalize((string) $need['estado']) : null,
        ];
    }

    private function insertBudgetDetail(array $data): void
    {
        $columns = ['presupuesto_id', 'categoria_id', 'necesidad_id', 'tipo_necesidad', 'descripcion', 'cantidad', 'costo_unitario', 'subtotal', 'anio'];
        foreach ($this->detailOptionalColumns() as $column) {
            if (array_key_exists($column, $data)) {
                $columns[] = $column;
            }
        }

        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

        $this->db->insert(
            "INSERT INTO presupuesto_detalles (" . implode(', ', $columns) . ")
             VALUES (" . implode(', ', $placeholders) . ")",
            array_intersect_key($data, array_flip($columns))
        );
    }

    private function budgetOptionalColumns(): array
    {
        $columns = [
            'presupuesto_base',
            'inflacion_anual',
            'crecimiento_anual',
            'total_quinquenal',
            'registros_sin_costo',
            'supuestos',
            'filtros_json',
        ];

        return array_values(array_filter($columns, fn (string $column): bool => $this->db->columnExists('presupuestos', $column)));
    }

    private function detailOptionalColumns(): array
    {
        $columns = [
            'costo_base',
            'year_index',
            'factor_proyeccion',
            'inflacion_anual',
            'crecimiento_anual',
            'tiene_costo',
            'motivo_sin_costo',
            'prioridad',
            'estado_solicitud',
        ];

        return array_values(array_filter($columns, fn (string $column): bool => $this->db->columnExists('presupuesto_detalles', $column)));
    }

    private function assumptions(string $type, int $startYear, int $endYear, float $annualGrowth, float $annualInflation): string
    {
        return sprintf(
            '%s %d-%d. Base calculada con cantidad por costo unitario estimado. Crecimiento anual %.2f%% e inflación anual %.2f%%.',
            $type,
            $startYear,
            $endYear,
            $annualGrowth,
            $annualInflation
        );
    }
}

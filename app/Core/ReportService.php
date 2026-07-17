<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Assignment;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\InventoryStateHistory;
use App\Models\LicenseAssignment;
use App\Models\NeedRequest;
use App\Models\ReturnReview;

final class ReportService
{
    public function __construct(
        private InventoryItem $inventory,
        private Assignment $assignments,
        private Category $categories,
        private LicenseAssignment $licenseAssignments,
        private NeedRequest $needs,
        private ReturnReview $returnReviews,
        private InventoryStateHistory $stateHistory
    ) {
    }

    public function dashboard(array $filters): array
    {
        $assets = $this->assets($filters);
        $assignments = $this->activeAssignments($filters);
        $licenses = $this->licenses($filters);

        return [
            'assets' => $assets,
            'grouped' => self::groupByType($assets),
            'assignments' => $assignments,
            'categories' => $this->categories->all(true),
            'categorySummary' => self::categorySummary($assets),
            'assignedByCategory' => self::assignedByCategorySummary($assets),
            'available' => $this->available($filters),
            'repairs' => $this->repairs($filters),
            'donations' => $this->donations($filters),
            'discards' => $this->discards($filters),
            'licenses' => $licenses,
            'licenseTotals' => $this->licenseTotals($licenses),
            'expirations' => $this->expirations($filters),
            'returns' => $this->returns(),
            'stateHistory' => $this->stateHistory($filters),
        ];
    }

    public function assetsReport(array $filters): array
    {
        $assets = $this->assets($filters);

        return self::payload(
            'CMDB - Reporte de inventario',
            ['Código', 'Activo', 'Tipo', 'Categoría', 'Marca', 'Serie', 'Estado', 'Responsable', 'Ingreso', 'Límite depreciación', 'Costo', 'Integridad'],
            self::assetRows($assets),
            [
                'Registros' => count($assets),
                'Costo total' => self::money(self::sum($assets, 'costo')),
            ]
        );
    }

    public function assignmentsReport(array $filters): array
    {
        $assignments = $this->assignments($filters);

        return self::payload(
            'CMDB - Reporte de responsables de equipos',
            ['Código', 'Equipo', 'Tipo', 'Colaborador', 'Departamento', 'Ubicación', 'IP', 'Desde', 'Hasta', 'Asignó', 'Estado'],
            self::assignmentRows($assignments),
            ['Registros' => count($assignments)]
        );
    }

    public function availableReport(array $filters): array
    {
        $assets = $this->available($filters);

        return self::payload(
            'CMDB - Reporte de activos disponibles',
            ['Código', 'Activo', 'Tipo', 'Categoría', 'Marca', 'Modelo', 'Ingreso', 'Límite depreciación', 'Costo'],
            self::availableRows($assets),
            [
                'Registros' => count($assets),
                'Costo disponible' => self::money(self::sum($assets, 'costo')),
            ]
        );
    }

    public function categoriesReport(array $filters): array
    {
        $summary = self::categorySummary($this->assets($filters));

        return self::payload(
            'CMDB - Reporte por categoría',
            ['Categoría', 'Tipo principal', 'Total', 'Asignados', 'Disponibles', 'Licencias', 'Costo total'],
            self::categoryRows($summary),
            ['Categorías' => count($summary)]
        );
    }

    public function assignedByCategoryReport(array $filters): array
    {
        $summary = self::assignedByCategorySummary($this->assets($filters));

        return self::payload(
            'CMDB - Reporte de asignados por categoría',
            ['Categoría', 'Tipo principal', 'Asignados', 'Responsables distintos', 'Costo asignado'],
            self::assignedByCategoryRows($summary),
            ['Categorías con asignación' => count($summary)]
        );
    }

    public function repairsReport(array $filters): array
    {
        $assets = $this->repairs($filters);

        return self::payload(
            'CMDB - Reporte de reparación',
            ['Código', 'Activo', 'Categoría', 'Estado', 'Marca', 'Modelo', 'Serie', 'Ingreso', 'Notas'],
            self::repairRows($assets),
            ['Registros en reparación' => count($assets)]
        );
    }

    public function donationsReport(array $filters): array
    {
        $assets = $this->donations($filters);

        return self::payload(
            'CMDB - Reporte de donaciones',
            ['Código', 'Activo', 'Categoría', 'Responsable', 'Beneficiario', 'Fecha', 'Evidencia', 'Observación'],
            self::donationRows($assets),
            ['Donaciones' => count($assets)]
        );
    }

    public function discardsReport(array $filters): array
    {
        $assets = $this->discards($filters);

        return self::payload(
            'CMDB - Reporte de descartes',
            ['Código', 'Activo', 'Categoría', 'Serie', 'Opinión técnica', 'Fecha evaluación', 'Evidencia'],
            self::discardRows($assets),
            ['Descartes' => count($assets)]
        );
    }

    public function licensesReport(array $filters): array
    {
        $licenses = $this->licenses($filters);
        $totals = $this->licenseTotals($licenses);

        return self::payload(
            'CMDB - Reporte de licencias',
            ['Código', 'Licencia', 'Proveedor', 'Tipo', 'Adquisición', 'Vencimiento', 'Estado', 'URL', 'Cupos', 'Usados', 'Disponibles', 'Observaciones'],
            self::licenseRows($licenses),
            [
                'Licencias' => count($licenses),
                'Cupos totales' => $totals['total'],
                'Cupos usados' => $totals['used'],
                'Cupos disponibles' => $totals['available'],
            ]
        );
    }

    public function licenseSeatsReport(array $filters): array
    {
        $rows = $this->licenseSeatRows($filters);

        return self::payload(
            'CMDB - Reporte de cupos de licencias',
            ['Licencia', 'Código', 'Colaborador', 'Departamento', 'Cupos', 'Fecha asignación', 'Estado', 'Observaciones'],
            $rows,
            ['Cupos asignados' => self::sumRows($rows, 4)]
        );
    }

    public function expirationsReport(array $filters): array
    {
        $rows = self::expirationRows($this->expirations($filters));

        return self::payload(
            'CMDB - Reporte de vencimientos',
            ['Código', 'Licencia', 'Proveedor', 'Vencimiento', 'Días restantes', 'Estado'],
            $rows,
            ['Registros con vencimiento' => count($rows)]
        );
    }

    public function depreciationReport(array $filters): array
    {
        $assets = $this->assets($filters);

        return self::payload(
            'CMDB - Reporte de depreciación',
            ['Código', 'Activo', 'Categoría', 'Ingreso', 'Vida útil meses', 'Límite', 'Costo', 'Depreciación mensual', 'Meses transcurridos', 'Depreciación acumulada', 'Valor en libros'],
            self::depreciationRows($assets, date('Y-m-d')),
            [
                'Registros' => count($assets),
                'Costo total' => self::money(self::sum($assets, 'costo')),
            ]
        );
    }

    public function needsReport(): array
    {
        $needs = $this->needs->all();

        return self::payload(
            'CMDB - Reporte de solicitudes',
            ['Fecha', 'Colaborador', 'Departamento', 'Tipo', 'Categoría', 'Prioridad', 'Estado', 'Cantidad', 'Año objetivo', 'Costo unitario estimado', 'Costo total estimado', 'Justificación', 'Descripción', 'Respuesta administrativa', 'Procesador'],
            self::needRows($needs),
            ['Solicitudes' => count($needs)]
        );
    }

    public function returnsReport(): array
    {
        $returns = $this->returns();

        return self::payload(
            'CMDB - Reporte de devoluciones',
            ['Código', 'Equipo', 'Serie', 'Colaborador', 'Motivo', 'Estado físico', 'Estado devolución', 'Fecha devolución', 'Observación', 'Evidencia'],
            self::returnRows($returns),
            ['Devoluciones' => count($returns)]
        );
    }

    public function reviewsReport(): array
    {
        $reviews = $this->returns();

        return self::payload(
            'CMDB - Reporte de revisiones técnicas',
            ['Código', 'Equipo', 'Serie', 'Colaborador', 'Resultado revisión', 'Técnico', 'Fecha revisión', 'Observación técnica', 'Evidencia'],
            self::reviewRows($reviews),
            ['Revisiones' => count(array_filter($reviews, static fn (array $row): bool => !empty($row['revision_id'])))]
        );
    }

    public function stateHistoryReport(array $filters): array
    {
        $history = $this->stateHistory($filters);

        return self::payload(
            'CMDB - Reporte de historial de estados',
            ['Fecha', 'Código', 'Activo', 'Estado anterior', 'Estado nuevo', 'Motivo', 'Usuario', 'Origen', 'Observación'],
            self::stateHistoryRows($history),
            ['Transiciones' => count($history)]
        );
    }

    public function assets(array $filters): array
    {
        return $this->inventory->all($filters);
    }

    public function assignments(array $filters): array
    {
        $assignments = $this->assignments->all();

        return self::filterAssignments($assignments, $filters);
    }

    public function activeAssignments(array $filters): array
    {
        $assignments = $this->assignments->all(true);

        return self::filterAssignments($assignments, $filters);
    }

    public function available(array $filters): array
    {
        return $this->assetsForStates($filters, [InventoryStatus::DISPONIBLE], true);
    }

    public function repairs(array $filters): array
    {
        return $this->assetsForStates($filters, [InventoryStatus::EN_REPARACION, InventoryStatus::MANTENIMIENTO], false);
    }

    public function donations(array $filters): array
    {
        return $this->inventory->donationsHistorical($filters);
    }

    public function discards(array $filters): array
    {
        return $this->assetsForStates($filters, [InventoryStatus::DESCARTE], false);
    }

    public function licenses(array $filters): array
    {
        $scoped = $filters;
        unset($scoped['licencias']);

        $licenses = array_values(array_filter(
            $this->inventory->all($scoped),
            static fn (array $item): bool => (int) ($item['es_licencia'] ?? 0) === 1
        ));

        foreach ($licenses as &$license) {
            $used = $this->licenseAssignments->usedQuantity((int) $license['id']);
            $license['cupos_usados'] = $used;
            $license['cupos_disponibles'] = max(0, (int) ($license['cantidad'] ?? 0) - $used);
        }

        if (!empty($filters['licencias'])) {
            $licenses = array_values(array_filter(
                $licenses,
                static fn (array $license): bool => (int) ($license['cupos_disponibles'] ?? 0) > 0
            ));
        }

        return $licenses;
    }

    public function licenseTotals(array $licenses): array
    {
        $totals = ['total' => 0, 'used' => 0, 'available' => 0];

        foreach ($licenses as $license) {
            $totals['total'] += (int) ($license['cantidad'] ?? 0);
            $totals['used'] += (int) ($license['cupos_usados'] ?? 0);
            $totals['available'] += (int) ($license['cupos_disponibles'] ?? 0);
        }

        return $totals;
    }

    public function licenseSeatRows(array $filters): array
    {
        $rows = [];

        foreach ($this->licenses($filters) as $license) {
            foreach ($this->licenseAssignments->activeByInventory((int) $license['id']) as $assignment) {
                $rows[] = [
                    $license['nombre'],
                    $license['codigo_activo'],
                    $assignment['colaborador_nombre'] ?? '',
                    $assignment['departamento'] ?? '',
                    (int) ($assignment['cantidad'] ?? 0),
                    $assignment['fecha_asignacion'] ?? '',
                    $assignment['estado'] ?? '',
                    $assignment['observaciones'] ?? '',
                ];
            }
        }

        return $rows;
    }

    public function expirations(array $filters): array
    {
        $licenses = array_values(array_filter(
            $this->licenses($filters),
            static fn (array $item): bool => !empty($item['fecha_vencimiento_licencia'])
        ));

        usort(
            $licenses,
            static fn (array $a, array $b): int => strcmp((string) $a['fecha_vencimiento_licencia'], (string) $b['fecha_vencimiento_licencia'])
        );

        return $licenses;
    }

    public function returns(): array
    {
        return $this->returnReviews->all();
    }

    public function stateHistory(array $filters): array
    {
        return $this->stateHistory->all($filters);
    }

    public static function payload(string $title, array $headers, array $rows, array $totals = []): array
    {
        return [
            'title' => $title,
            'headers' => $headers,
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    public static function groupByType(array $assets): array
    {
        return [
            'HARDWARE' => array_values(array_filter($assets, static fn (array $item): bool => $item['tipo_activo'] === 'HARDWARE')),
            'SOFTWARE' => array_values(array_filter($assets, static fn (array $item): bool => $item['tipo_activo'] === 'SOFTWARE')),
        ];
    }

    public static function categorySummary(array $assets): array
    {
        $stats = [];

        foreach ($assets as $asset) {
            $category = $asset['categoria_nombre'] ?? 'Sin categoría';
            $stats[$category] ??= [
                'categoria' => $category,
                'tipo' => $asset['tipo_activo'] ?? '',
                'cantidad' => 0,
                'asignados' => 0,
                'disponibles' => 0,
                'licencias' => 0,
                'costo' => 0.0,
            ];
            $stats[$category]['cantidad']++;
            if (!empty($asset['asignacion_actual']) || ($asset['estado'] ?? '') === InventoryStatus::ASIGNADO) {
                $stats[$category]['asignados']++;
            }
            if (($asset['estado'] ?? '') === InventoryStatus::DISPONIBLE && empty($asset['asignacion_actual'])) {
                $stats[$category]['disponibles']++;
            }
            if ((int) ($asset['es_licencia'] ?? 0) === 1) {
                $stats[$category]['licencias']++;
            }
            $stats[$category]['costo'] += (float) ($asset['costo'] ?? 0);
        }

        ksort($stats);

        return array_values($stats);
    }

    public static function assignedByCategorySummary(array $assets): array
    {
        $stats = [];

        foreach ($assets as $asset) {
            if (empty($asset['asignacion_actual']) && ($asset['estado'] ?? '') !== InventoryStatus::ASIGNADO) {
                continue;
            }

            $category = $asset['categoria_nombre'] ?? 'Sin categoría';
            $stats[$category] ??= [
                'categoria' => $category,
                'tipo' => $asset['tipo_activo'] ?? '',
                'asignados' => 0,
                'responsables' => [],
                'costo' => 0.0,
            ];
            $stats[$category]['asignados']++;
            if (!empty($asset['asignado_a'])) {
                $stats[$category]['responsables'][$asset['asignado_a']] = true;
            }
            $stats[$category]['costo'] += (float) ($asset['costo'] ?? 0);
        }

        foreach ($stats as &$row) {
            $row['responsables_distintos'] = count($row['responsables']);
            unset($row['responsables']);
        }

        ksort($stats);

        return array_values($stats);
    }

    public static function assetRows(array $assets): array
    {
        return array_map(static fn (array $item): array => [
            $item['codigo_activo'] ?? '',
            $item['nombre'] ?? '',
            $item['tipo_activo'] ?? '',
            $item['categoria_nombre'] ?? '',
            $item['marca'] ?? '',
            $item['serie'] ?? '',
            InventoryStatus::label((string) ($item['estado'] ?? '')),
            $item['asignado_a'] ?? 'Sin asignar',
            $item['fecha_ingreso'] ?? '',
            $item['fecha_limite_depreciacion'] ?? '',
            self::money((float) ($item['costo'] ?? 0)),
            !empty($item['integridad_valida']) ? 'Firma válida' : 'Firma no válida',
        ], $assets);
    }

    public static function assignmentRows(array $assignments): array
    {
        return array_map(static fn (array $assignment): array => [
            $assignment['codigo_activo'] ?? '',
            $assignment['equipo_nombre'] ?? '',
            $assignment['tipo_activo'] ?? '',
            $assignment['colaborador_nombre'] ?? '',
            $assignment['departamento'] ?? '',
            $assignment['ubicacion'] ?? '',
            $assignment['ip_asignada'] ?? '',
            $assignment['fecha_asignacion'] ?? '',
            ($assignment['fecha_devolucion'] ?? '') ?: 'En custodia',
            $assignment['asignador_nombre'] ?? '',
            $assignment['estado'] ?? '',
        ], $assignments);
    }

    public static function availableRows(array $assets): array
    {
        return array_map(static fn (array $item): array => [
            $item['codigo_activo'] ?? '',
            $item['nombre'] ?? '',
            $item['tipo_activo'] ?? '',
            $item['categoria_nombre'] ?? '',
            $item['marca'] ?? '',
            $item['modelo'] ?? '',
            $item['fecha_ingreso'] ?? '',
            $item['fecha_limite_depreciacion'] ?? '',
            self::money((float) ($item['costo'] ?? 0)),
        ], $assets);
    }

    public static function categoryRows(array $summary): array
    {
        return array_map(static fn (array $row): array => [
            $row['categoria'] ?? '',
            $row['tipo'] ?? '',
            (int) ($row['cantidad'] ?? 0),
            (int) ($row['asignados'] ?? 0),
            (int) ($row['disponibles'] ?? 0),
            (int) ($row['licencias'] ?? 0),
            self::money((float) ($row['costo'] ?? 0)),
        ], $summary);
    }

    public static function assignedByCategoryRows(array $summary): array
    {
        return array_map(static fn (array $row): array => [
            $row['categoria'] ?? '',
            $row['tipo'] ?? '',
            (int) ($row['asignados'] ?? 0),
            (int) ($row['responsables_distintos'] ?? 0),
            self::money((float) ($row['costo'] ?? 0)),
        ], $summary);
    }

    public static function repairRows(array $assets): array
    {
        return array_map(static fn (array $item): array => [
            $item['codigo_activo'] ?? '',
            $item['nombre'] ?? '',
            $item['categoria_nombre'] ?? '',
            InventoryStatus::label((string) ($item['estado'] ?? '')),
            $item['marca'] ?? '',
            $item['modelo'] ?? '',
            $item['serie'] ?? '',
            $item['fecha_ingreso'] ?? '',
            $item['notas'] ?? '',
        ], $assets);
    }

    public static function donationRows(array $assets): array
    {
        return array_map(static fn (array $item): array => [
            $item['codigo_activo'] ?? '',
            $item['nombre'] ?? '',
            $item['categoria_nombre'] ?? '',
            $item['responsable_donacion'] ?? '',
            $item['beneficiario_donacion'] ?? '',
            $item['fecha_donacion'] ?? '',
            $item['evidencia_donacion'] ?? '',
            $item['observacion_donacion'] ?? '',
        ], $assets);
    }

    public static function discardRows(array $assets): array
    {
        return array_map(static fn (array $item): array => [
            $item['codigo_activo'] ?? '',
            $item['nombre'] ?? '',
            $item['categoria_nombre'] ?? '',
            $item['serie'] ?? '',
            $item['observacion_tecnica_descarte'] ?? '',
            $item['fecha_evaluacion_descarte'] ?? '',
            $item['evidencia_descarte'] ?? '',
        ], $assets);
    }

    public static function licenseRows(array $licenses): array
    {
        return array_map(static fn (array $item): array => [
            $item['codigo_activo'] ?? '',
            $item['nombre'] ?? '',
            $item['proveedor_licencia'] ?? '',
            $item['tipo_licencia'] ?? '',
            $item['fecha_adquisicion_licencia'] ?? '',
            $item['fecha_vencimiento_licencia'] ?? '',
            $item['estado_licencia'] ?? 'ACTIVA',
            $item['url_licencia'] ?? '',
            (int) ($item['cantidad'] ?? 0),
            (int) ($item['cupos_usados'] ?? 0),
            (int) ($item['cupos_disponibles'] ?? 0),
            $item['observaciones_licencia'] ?? '',
        ], $licenses);
    }

    public static function expirationRows(array $licenses, ?string $today = null): array
    {
        $today ??= date('Y-m-d');

        return array_map(static function (array $item) use ($today): array {
            $expiration = (string) ($item['fecha_vencimiento_licencia'] ?? '');
            $days = $expiration !== '' ? self::daysBetween($today, $expiration) : null;
            $status = match (true) {
                $days === null => 'Sin vencimiento',
                $days < 0 => 'Vencida',
                $days <= 30 => 'Por vencer',
                $days <= 90 => 'Próxima',
                default => 'Vigente',
            };

            return [
                $item['codigo_activo'] ?? '',
                $item['nombre'] ?? '',
                $item['proveedor_licencia'] ?? '',
                $expiration,
                $days === null ? '' : $days,
                $status,
            ];
        }, $licenses);
    }

    public static function depreciationRows(array $assets, string $asOfDate): array
    {
        return array_map(static function (array $item) use ($asOfDate): array {
            $months = max(1, (int) ($item['vida_util_meses'] ?? 1));
            $cost = (float) ($item['costo'] ?? 0);
            $depreciation = DepreciationCalculator::straightLine(
                $cost,
                $months,
                (string) ($item['fecha_ingreso'] ?? $asOfDate),
                $asOfDate
            );

            return [
                $item['codigo_activo'] ?? '',
                $item['nombre'] ?? '',
                $item['categoria_nombre'] ?? '',
                $item['fecha_ingreso'] ?? '',
                $months,
                $depreciation['fecha_limite'],
                self::money($cost),
                self::money($depreciation['depreciacion_mensual']),
                $depreciation['meses_transcurridos'],
                self::money($depreciation['depreciacion_acumulada']),
                self::money($depreciation['valor_libros']),
            ];
        }, $assets);
    }

    public static function needRows(array $needs): array
    {
        return array_map(static fn (array $need): array => [
            $need['created_at'] ?? '',
            $need['colaborador_nombre'] ?? '',
            $need['departamento'] ?? '',
            $need['tipo_necesidad'] ?? '',
            $need['categoria_nombre'] ?? '',
            $need['prioridad'] ?? '',
            NeedStatus::label((string) ($need['estado'] ?? '')),
            $need['cantidad'] ?? 1,
            $need['anio_objetivo'] ?? '',
            $need['costo_unitario_estimado'] ?? '',
            $need['costo_estimado'] ?? '',
            $need['justificacion'] ?? '',
            $need['descripcion'] ?? '',
            ($need['respuesta_administrativa'] ?? '') ?: ($need['comentario_resolucion'] ?? ''),
            $need['procesador_nombre'] ?? '',
        ], $needs);
    }

    public static function returnRows(array $returns): array
    {
        return array_map(static fn (array $return): array => [
            $return['codigo_activo'] ?? '',
            $return['equipo_nombre'] ?? '',
            $return['serie'] ?? '',
            $return['colaborador_nombre'] ?? '',
            $return['motivo'] ?? '',
            $return['estado_fisico'] ?? '',
            $return['estado_devolucion'] ?? '',
            $return['fecha_devolucion'] ?? '',
            $return['observacion_devolucion'] ?? '',
            $return['evidencia'] ?? '',
        ], $returns);
    }

    public static function reviewRows(array $reviews): array
    {
        return array_map(static fn (array $review): array => [
            $review['codigo_activo'] ?? '',
            $review['equipo_nombre'] ?? '',
            $review['serie'] ?? '',
            $review['colaborador_nombre'] ?? '',
            $review['resultado'] ?? 'Pendiente',
            $review['tecnico'] ?? '',
            $review['fecha_revision'] ?? '',
            $review['observacion_tecnica'] ?? '',
            $review['evidencia'] ?? '',
        ], $reviews);
    }

    public static function stateHistoryRows(array $history): array
    {
        return array_map(static fn (array $row): array => [
            $row['created_at'] ?? '',
            $row['codigo_activo'] ?? '',
            $row['equipo_nombre'] ?? '',
            $row['estado_anterior'] ? InventoryStatus::label((string) $row['estado_anterior']) : 'Registro inicial',
            InventoryStatus::label((string) ($row['estado_nuevo'] ?? '')),
            $row['motivo'] ?? '',
            $row['nombre_usuario'] ?? 'Sistema',
            $row['entidad_origen'] ?? 'inventario',
            $row['observacion'] ?? '',
        ], $history);
    }

    public static function filterSummary(array $filters): string
    {
        $labels = [];
        foreach ($filters as $key => $value) {
            if ($value === '' || $value === false || $value === null) {
                continue;
            }
            $labels[] = $key . ': ' . ($value === true ? 'sí' : (string) $value);
        }

        return $labels ? implode(' | ', $labels) : 'Sin filtros';
    }

    private function assetsForStates(array $filters, array $states, bool $withoutAssignment): array
    {
        if (!empty($filters['estado']) && !in_array($filters['estado'], $states, true)) {
            return [];
        }

        $scoped = $filters;
        unset($scoped['estado']);
        $assets = $this->inventory->all($scoped);

        return array_values(array_filter($assets, static function (array $item) use ($states, $withoutAssignment): bool {
            if (!in_array((string) ($item['estado'] ?? ''), $states, true)) {
                return false;
            }

            return !$withoutAssignment || empty($item['asignacion_actual']);
        }));
    }

    private static function filterAssignments(array $assignments, array $filters): array
    {
        return array_values(array_filter($assignments, static function (array $assignment) use ($filters): bool {
            if (!empty($filters['tipo']) && ($assignment['tipo_activo'] ?? '') !== $filters['tipo']) {
                return false;
            }
            if (!empty($filters['estado']) && $filters['estado'] !== InventoryStatus::ASIGNADO) {
                return false;
            }
            if (!empty($filters['buscar'])) {
                $lower = static fn (string $value): string => function_exists('mb_strtolower')
                    ? \mb_strtolower($value)
                    : strtolower($value);
                $haystack = $lower(implode(' ', [
                    $assignment['codigo_activo'] ?? '',
                    $assignment['equipo_nombre'] ?? '',
                    $assignment['serie'] ?? '',
                    $assignment['colaborador_nombre'] ?? '',
                    $assignment['departamento'] ?? '',
                    $assignment['ubicacion'] ?? '',
                ]));
                if (!str_contains($haystack, $lower((string) $filters['buscar']))) {
                    return false;
                }
            }
            if (!empty($filters['fecha_desde']) && strcmp((string) ($assignment['fecha_asignacion'] ?? ''), (string) $filters['fecha_desde']) < 0) {
                return false;
            }
            if (!empty($filters['fecha_hasta']) && strcmp((string) ($assignment['fecha_asignacion'] ?? ''), (string) $filters['fecha_hasta']) > 0) {
                return false;
            }

            return true;
        }));
    }

    private static function daysBetween(string $startDate, string $endDate): int
    {
        $start = new \DateTimeImmutable($startDate);
        $end = new \DateTimeImmutable($endDate);

        return (int) $start->diff($end)->format('%r%a');
    }

    private static function sum(array $rows, string $column): float
    {
        return array_sum(array_map(static fn (array $row): float => (float) ($row[$column] ?? 0), $rows));
    }

    private static function sumRows(array $rows, int $index): int
    {
        return array_sum(array_map(static fn (array $row): int => (int) ($row[$index] ?? 0), $rows));
    }

    private static function money(float $value): string
    {
        return number_format($value, 2);
    }
}

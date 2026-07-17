<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Core\BudgetProjection;

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$assert(BudgetProjection::years('ANUAL', 2026) === [2026], 'Presupuesto anual debe tener un año.');
$assert(BudgetProjection::years('QUINQUENAL', 2026) === [2026, 2027, 2028, 2029, 2030], 'Presupuesto quinquenal debe tener cinco años reales.');

$assert(BudgetProjection::subtotal(3, 100.00) === 300.00, 'Subtotal debe usar cantidad por costo unitario.');
$assert(BudgetProjection::subtotal(3, null) === null, 'Costo ausente no debe convertirse silenciosamente a cero.');
$assert(!BudgetProjection::hasCost(null), 'Null debe tratarse como costo ausente.');
$assert(!BudgetProjection::hasCost(''), 'Cadena vacía debe tratarse como costo ausente.');
$assert(BudgetProjection::hasCost(0), 'Costo cero explícito es distinto a costo ausente.');

$yearOneUnit = BudgetProjection::projectedUnitCost(100.00, 1, 10.0, 5.0);
$assert(abs($yearOneUnit - 115.50) < 0.001, 'Proyección debe aplicar crecimiento e inflación.');

$yearTwoBase = BudgetProjection::projectBase(1000.00, 2, 10.0, 5.0);
$assert(abs($yearTwoBase - 1334.03) < 0.001, 'Base proyectada año 2 incorrecta.');

$rows = [];
$total = 0.0;
foreach (BudgetProjection::years('QUINQUENAL', 2026) as $year) {
    $index = BudgetProjection::yearIndex(2026, $year);
    $unit = BudgetProjection::projectedUnitCost(100.00, $index, 10.0, 5.0);
    $subtotal = BudgetProjection::subtotal(2, $unit);
    $total += $subtotal ?? 0.0;
    $rows[] = [
        'anio' => $year,
        'year_index' => $index,
        'subtotal' => $subtotal,
        'tiene_costo' => 1,
        'categoria_nombre' => 'Equipo de Cómputo',
        'tipo_necesidad' => 'EQUIPO',
        'prioridad' => 'ALTA',
        'estado_solicitud' => 'EN_ESPERA',
        'necesidad_id' => 10,
    ];
}

$rows[] = [
    'anio' => 2026,
    'year_index' => 0,
    'subtotal' => 0,
    'tiene_costo' => 0,
    'categoria_nombre' => 'Software',
    'tipo_necesidad' => 'SOFTWARE',
    'prioridad' => 'MEDIA',
    'estado_solicitud' => 'EN_TRAMITE',
    'descripcion' => 'Solicitud sin costo',
    'motivo_sin_costo' => BudgetProjection::noCostReason([]),
    'necesidad_id' => 11,
];

$summary = BudgetProjection::summarizeRows($rows);
$assert(abs($summary['base'] - 200.00) < 0.001, 'Presupuesto base debe ser el año inicial.');
$assert(abs($summary['total'] - round($total, 2)) < 0.001, 'Total quinquenal incorrecto.');
$assert(count($summary['by_year']) === 5, 'Resumen debe tener cinco años.');
$assert($summary['without_cost_count'] === 1, 'Debe separar registros sin costo.');
$assert(count($summary['without_cost']) === 1, 'Debe listar registros sin costo.');

echo "OK Phase6BBudgetProjectionTest\n";

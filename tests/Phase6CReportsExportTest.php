<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
require_once dirname(__DIR__) . '/app/Core/helpers.php';

use App\Core\ExcelExporter;
use App\Core\InventoryStatus;
use App\Core\ReportService;

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$assets = [
    [
        'id' => 1,
        'codigo_activo' => 'CMDB-001',
        'nombre' => 'Laptop Financiera',
        'tipo_activo' => 'HARDWARE',
        'categoria_nombre' => 'Equipo de Cómputo',
        'marca' => 'Lenovo',
        'modelo' => 'ThinkPad',
        'serie' => '=SERIE-PELIGROSA',
        'estado' => InventoryStatus::ASIGNADO,
        'asignacion_actual' => 10,
        'asignado_a' => 'Ana Perez',
        'fecha_ingreso' => '2025-01-13',
        'fecha_limite_depreciacion' => '2028-01-13',
        'vida_util_meses' => 36,
        'costo' => 1200.00,
        'es_licencia' => 0,
        'integridad_valida' => true,
    ],
    [
        'id' => 2,
        'codigo_activo' => 'LIC-365',
        'nombre' => 'Microsoft 365',
        'tipo_activo' => 'SOFTWARE',
        'categoria_nombre' => 'Licencias',
        'marca' => 'Microsoft',
        'modelo' => 'Business',
        'serie' => 'LIC-365',
        'estado' => InventoryStatus::DISPONIBLE,
        'asignacion_actual' => null,
        'asignado_a' => null,
        'fecha_ingreso' => '2026-01-13',
        'fecha_limite_depreciacion' => '2027-01-13',
        'vida_util_meses' => 12,
        'costo' => 600.00,
        'es_licencia' => 1,
        'cantidad' => 10,
        'cupos_usados' => 4,
        'cupos_disponibles' => 6,
        'proveedor_licencia' => '+Proveedor',
        'tipo_licencia' => 'SUSCRIPCION',
        'fecha_vencimiento_licencia' => '2026-08-01',
        'estado_licencia' => 'ACTIVA',
        'integridad_valida' => true,
    ],
    [
        'id' => 3,
        'codigo_activo' => 'DON-001',
        'nombre' => 'Monitor donado',
        'tipo_activo' => 'HARDWARE',
        'categoria_nombre' => 'Monitores',
        'marca' => 'Dell',
        'modelo' => 'P2419H',
        'serie' => 'DON-MON-001',
        'estado' => InventoryStatus::DONADO,
        'activo' => 0,
        'asignacion_actual' => null,
        'asignado_a' => null,
        'fecha_ingreso' => '2023-04-10',
        'fecha_limite_depreciacion' => '2026-04-10',
        'vida_util_meses' => 36,
        'costo' => 180.00,
        'es_licencia' => 0,
        'responsable_donacion' => 'Comité de baja',
        'beneficiario_donacion' => 'Escuela Técnica',
        'fecha_donacion' => '2026-07-01',
        'evidencia_donacion' => 'acta-001.pdf',
        'observacion_donacion' => 'Donación autorizada',
        'integridad_valida' => true,
    ],
];

$summary = ReportService::categorySummary($assets);
$assigned = ReportService::assignedByCategorySummary($assets);
$assetRows = ReportService::assetRows($assets);
$licenseRows = ReportService::licenseRows([$assets[1]]);
$donationRows = ReportService::donationRows([$assets[2]]);
$reportSource = file_get_contents(dirname(__DIR__) . '/app/Core/ReportService.php') ?: '';
$inventorySource = file_get_contents(dirname(__DIR__) . '/app/Models/InventoryItem.php') ?: '';
$depreciation = ReportService::depreciationRows([$assets[0]], '2026-07-13');
$expirations = ReportService::expirationRows([$assets[1]], '2026-07-13');

$assert(count($summary) === 3, 'El resumen debe agrupar por categoría.');
$assert($summary[0]['cantidad'] === 1, 'Cada categoría de prueba debe contar un activo.');
$assert(count($assigned) === 1, 'Solo una categoría tiene activos asignados.');
$assert($assigned[0]['responsables_distintos'] === 1, 'Debe contar responsables distintos.');
$assert($assetRows[0][5] === '=SERIE-PELIGROSA', 'El servicio conserva el dato original; el escape ocurre al exportar.');
$assert($licenseRows[0][9] === 4 && $licenseRows[0][10] === 6, 'Licencias debe exponer cupos usados y disponibles.');
$assert($donationRows[0][0] === 'DON-001' && $donationRows[0][4] === 'Escuela Técnica', 'Donaciones debe conservar datos históricos aunque el activo esté inactivo.');
$assert(str_contains($reportSource, 'donationsHistorical'), 'Reporte de donaciones debe usar consulta historica independiente.');
$assert(str_contains($inventorySource, 'WHERE i.estado = :estado_donado'), 'Consulta historica de donaciones no debe depender de activo = 1.');
$assert($depreciation[0][8] === 18, 'Depreciación debe calcular meses transcurridos.');
$assert($expirations[0][5] === 'Por vencer', 'Vencimiento a 19 días debe marcarse por vencer.');

$payload = ReportService::payload(
    'CMDB - Prueba de exportación',
    ['Código', 'Serie'],
    [[$assetRows[0][0], $assetRows[0][5]]],
    ['Registros' => 1]
);

$html = ExcelExporter::table(
    $payload['title'],
    $payload['headers'],
    $payload['rows'],
    [
        'user' => '@usuario',
        'filters' => '+estado: ASIGNADO',
        'totals' => $payload['totals'],
    ]
);

$assert(str_contains($html, 'CMDB - Prueba de exportación'), 'El Excel debe incluir título.');
$assert(str_contains($html, 'Registros'), 'El Excel debe incluir totales.');
$assert(str_contains($html, '&#039;=SERIE-PELIGROSA'), 'El Excel debe neutralizar Formula Injection en filas.');
$assert(str_contains($html, '&#039;@usuario'), 'El Excel debe neutralizar Formula Injection en usuario.');
$assert(str_contains($html, '&#039;+estado: ASIGNADO'), 'El Excel debe neutralizar Formula Injection en filtros.');

echo "OK Phase6CReportsExportTest\n";

<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\ExcelExporter;
use App\Core\InventoryStatus;
use App\Core\ModelFactory;
use App\Core\ReportService;
use App\Core\Response;
use App\Core\Sanitizer;
use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\InventoryStateHistory;
use App\Models\LicenseAssignment;
use App\Models\NeedRequest;
use App\Models\ReturnReview;

final class ReportsController extends Controller
{
    private ReportService $reports;
    private AuditLog $audit;

    public function __construct(?ModelFactory $models = null)
    {
        $models ??= ModelFactory::default();
        $this->reports = $models->reports();
        $this->audit = $models->audit();
    }

    public function index(): void
    {
        $this->authorize('reports.view');

        $filters = $this->filters();
        $queryString = http_build_query(array_filter(
            $filters,
            static fn (mixed $value): bool => $value !== '' && $value !== false && $value !== null
        ));

        $this->render('reports/index', array_merge($this->reports->dashboard($filters), [
            'title' => 'Reportes CMDB',
            'filters' => $filters,
            'queryString' => $queryString,
        ]));
    }

    public function assetsExcel(): void
    {
        $this->downloadFilteredReport('assetsReport', 'reporte_inventario_cmdb', 'EXPORTAR_INVENTARIO');
    }

    public function assignmentsExcel(): void
    {
        $this->downloadFilteredReport('assignmentsReport', 'reporte_asignaciones_cmdb', 'EXPORTAR_ASIGNACIONES');
    }

    public function availableExcel(): void
    {
        $this->downloadFilteredReport('availableReport', 'reporte_disponibles_cmdb', 'EXPORTAR_DISPONIBLES');
    }

    public function categoriesExcel(): void
    {
        $this->downloadFilteredReport('categoriesReport', 'reporte_categorias_cmdb', 'EXPORTAR_CATEGORIAS');
    }

    public function assignedCategoriesExcel(): void
    {
        $this->downloadFilteredReport('assignedByCategoryReport', 'reporte_asignados_por_categoria_cmdb', 'EXPORTAR_ASIGNADOS_CATEGORIA');
    }

    public function repairsExcel(): void
    {
        $this->downloadFilteredReport('repairsReport', 'reporte_reparacion_cmdb', 'EXPORTAR_REPARACION');
    }

    public function donationsExcel(): void
    {
        $this->downloadFilteredReport('donationsReport', 'reporte_donaciones_cmdb', 'EXPORTAR_DONACIONES');
    }

    public function discardsExcel(): void
    {
        $this->downloadFilteredReport('discardsReport', 'reporte_descartes_cmdb', 'EXPORTAR_DESCARTES');
    }

    public function licensesExcel(): void
    {
        $this->downloadFilteredReport('licensesReport', 'reporte_licencias_cmdb', 'EXPORTAR_LICENCIAS');
    }

    public function licenseSeatsExcel(): void
    {
        $this->downloadFilteredReport('licenseSeatsReport', 'reporte_cupos_licencias_cmdb', 'EXPORTAR_CUPOS_LICENCIAS');
    }

    public function expirationsExcel(): void
    {
        $this->downloadFilteredReport('expirationsReport', 'reporte_vencimientos_cmdb', 'EXPORTAR_VENCIMIENTOS');
    }

    public function depreciationExcel(): void
    {
        $this->downloadFilteredReport('depreciationReport', 'reporte_depreciacion_cmdb', 'EXPORTAR_DEPRECIACION');
    }

    public function needsExcel(): void
    {
        $this->downloadGeneralReport('needsReport', 'reporte_solicitudes_cmdb', 'EXPORTAR_SOLICITUDES');
    }

    public function returnsExcel(): void
    {
        $this->downloadGeneralReport('returnsReport', 'reporte_devoluciones_cmdb', 'EXPORTAR_DEVOLUCIONES');
    }

    public function reviewsExcel(): void
    {
        $this->downloadGeneralReport('reviewsReport', 'reporte_revisiones_tecnicas_cmdb', 'EXPORTAR_REVISIONES');
    }

    public function stateHistoryExcel(): void
    {
        $this->downloadFilteredReport('stateHistoryReport', 'reporte_historial_estados_cmdb', 'EXPORTAR_HISTORIAL_ESTADOS');
    }

    private function downloadFilteredReport(string $method, string $filename, string $action): void
    {
        $this->authorize('reports.export');

        $filters = $this->filters();
        $report = $this->reports->{$method}($filters);
        $this->download($report, $filename, $action, $filters);
    }

    private function downloadGeneralReport(string $method, string $filename, string $action): void
    {
        $this->authorize('reports.export');

        $report = $this->reports->{$method}();
        $this->download($report, $filename, $action, []);
    }

    private function download(array $report, string $filename, string $action, array $filters): void
    {
        $filterSummary = ReportService::filterSummary($filters);
        $html = ExcelExporter::table(
            (string) $report['title'],
            $report['headers'],
            $report['rows'],
            [
                'user' => $this->exportUser(),
                'filters' => $filterSummary,
                'totals' => $report['totals'] ?? [],
            ]
        );

        $this->audit->create(
            Auth::id(),
            'REPORTES',
            $action,
            (string) $report['title'] . '. Filtros: ' . $filterSummary
        );

        Response::downloadExcel($filename, $html);
    }

    private function filters(): array
    {
        $type = $_GET['tipo'] ?? '';
        $status = $_GET['estado'] ?? '';

        return [
            'tipo' => in_array($type, ['HARDWARE', 'SOFTWARE'], true) ? $type : '',
            'estado' => in_array($status, InventoryStatus::values(), true) ? $status : '',
            'categoria_id' => (int) ($_GET['categoria_id'] ?? 0) ?: null,
            'buscar' => Sanitizer::text($_GET['buscar'] ?? '', 100),
            'sin_asignar' => isset($_GET['sin_asignar']),
            'licencias' => isset($_GET['licencias']),
            'fecha_desde' => Sanitizer::text($_GET['fecha_desde'] ?? '', 10),
            'fecha_hasta' => Sanitizer::text($_GET['fecha_hasta'] ?? '', 10),
        ];
    }

    private function exportUser(): string
    {
        $user = Auth::user();

        return (string) ($user['nombre_usuario'] ?? 'Sistema');
    }
}

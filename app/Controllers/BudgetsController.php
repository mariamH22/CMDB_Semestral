<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\ExcelExporter;
use App\Core\ModelFactory;
use App\Core\Response;
use App\Core\Sanitizer;
use App\Core\Validator;
use App\Models\AuditLog;
use App\Models\Budget;
use App\Models\Category;

final class BudgetsController extends Controller
{
    private Budget $budgets;
    private AuditLog $audit;
    private Category $categories;

    public function __construct(?ModelFactory $models = null)
    {
        $models ??= ModelFactory::default();
        $this->budgets = $models->budgets();
        $this->audit = $models->audit();
        $this->categories = $models->categories();
    }

    public function index(): void
    {
        $this->authorize('budgets.view');
        $budgetId = (int) ($_GET['id'] ?? 0);

        $this->render('budgets/index', [
            'title' => 'Presupuesto CMDB',
            'schemaReady' => $this->budgets->schemaReady(),
            'budgets' => $this->budgets->all(),
            'categories' => $this->categories->all(true),
            'selectedBudgetId' => $budgetId,
            'selectedBudget' => $budgetId > 0 ? $this->budgets->find($budgetId) : null,
            'details' => $budgetId > 0 ? $this->budgets->details($budgetId) : [],
            'summary' => $budgetId > 0 ? $this->budgets->summary($budgetId) : null,
        ]);
    }

    public function generate(): void
    {
        try {
            $this->authorize('budgets.manage');
            $this->csrf();

            $type = $_POST['tipo'] ?? 'ANUAL';
            if (!in_array($type, ['ANUAL', 'QUINQUENAL'], true)) {
                throw new \RuntimeException('Tipo de presupuesto inválido.');
            }

            $year = Validator::integerRange((int) ($_POST['anio_inicio'] ?? date('Y')), 2020, 2100, 'Año de inicio');
            $name = Validator::required(Sanitizer::text($_POST['nombre'] ?? '', 160), 'Nombre');
            $growth = $this->percentage('crecimiento_anual', 'Crecimiento anual');
            $inflation = $this->percentage('inflacion_anual', 'Inflación anual');
            $filters = $this->filtersFromPost();
            $id = $this->budgets->generateFromNeeds($name, $type, $year, Auth::id(), $growth, $inflation, $filters);

            $this->audit->create(Auth::id(), 'PRESUPUESTO', 'GENERAR', "Presupuesto #{$id} generado desde necesidades pendientes.", 'INFO', [
                'entity' => 'presupuestos',
                'entity_id' => $id,
                'after' => [
                    'nombre' => $name,
                    'tipo' => $type,
                    'anio_inicio' => $year,
                    'crecimiento_anual' => $growth,
                    'inflacion_anual' => $inflation,
                    'filtros' => $filters,
                ],
            ]);
            flash('success', 'Presupuesto generado como borrador.');
            $this->redirect('budgets?id=' . $id);
        } catch (\Throwable $exception) {
            $this->formError($exception, 'budgets');
        }
    }

    public function excel(): void
    {
        $this->authorize('reports.export');
        $id = (int) ($_GET['id'] ?? 0);
        $budget = $this->budgets->find($id);

        if (!$budget) {
            flash('error', 'Presupuesto no encontrado o migración no aplicada.');
            $this->redirect('budgets');
        }

        $rows = [];
        $summary = $this->budgets->summary($id);
        foreach ($this->budgets->details($id) as $detail) {
            $hasCost = (int) ($detail['tiene_costo'] ?? 1) === 1;
            $rows[] = [
                $budget['nombre'],
                $budget['tipo'],
                $detail['anio'],
                $detail['tipo_necesidad'],
                $detail['categoria_nombre'] ?? '',
                $detail['prioridad'] ?? '',
                $detail['estado_solicitud'] ?? '',
                $detail['descripcion'],
                $detail['cantidad'],
                $hasCost ? number_format((float) $detail['costo_unitario'], 2) : 'Sin costo',
                $hasCost ? number_format((float) $detail['subtotal'], 2) : 'Sin costo',
                $detail['motivo_sin_costo'] ?? '',
            ];
        }
        $rows[] = ['RESUMEN', 'Base', '', '', '', '', '', '', '', number_format((float) $summary['base'], 2), '', ''];
        $rows[] = ['RESUMEN', 'Total', '', '', '', '', '', '', '', number_format((float) $summary['total'], 2), '', ''];
        $rows[] = ['RESUMEN', 'Registros sin costo', '', '', '', '', '', '', '', (string) $summary['without_cost_count'], '', ''];

        $html = ExcelExporter::table(
            'CMDB - Presupuesto ' . $budget['nombre'],
            ['Presupuesto', 'Tipo', 'Año', 'Necesidad', 'Categoría', 'Prioridad', 'Estado solicitud', 'Descripción', 'Cantidad', 'Costo unitario', 'Subtotal', 'Observación'],
            $rows
        );

        $this->audit->create(Auth::id(), 'PRESUPUESTO', 'EXPORTAR', "Presupuesto #{$id} exportado a Excel.", 'INFO', [
            'entity' => 'presupuestos',
            'entity_id' => $id,
            'result' => 'EXPORTADO',
        ]);
        Response::downloadExcel('presupuesto_cmdb_' . $id, $html);
    }

    private function percentage(string $key, string $field): float
    {
        $value = Validator::positiveNumber(Sanitizer::decimal($_POST[$key] ?? 0), $field);
        if ($value > 100) {
            throw new \RuntimeException("El campo {$field} debe estar entre 0 y 100.");
        }

        return $value;
    }

    private function filtersFromPost(): array
    {
        $filters = [];

        $year = (int) ($_POST['filtro_anio'] ?? 0);
        if ($year > 0) {
            $filters['anio'] = Validator::integerRange($year, 2020, 2100, 'Año objetivo');
        }

        $categoryId = (int) ($_POST['filtro_categoria_id'] ?? 0);
        if ($categoryId > 0) {
            $filters['categoria_id'] = $categoryId;
        }

        $type = Sanitizer::text($_POST['filtro_tipo'] ?? '', 20);
        if (in_array($type, ['EQUIPO', 'SOFTWARE', 'LICENCIA'], true)) {
            $filters['tipo'] = $type;
        }

        $priority = Sanitizer::text($_POST['filtro_prioridad'] ?? '', 20);
        if (in_array($priority, ['BAJA', 'MEDIA', 'ALTA'], true)) {
            $filters['prioridad'] = $priority;
        }

        $status = Sanitizer::text($_POST['filtro_estado'] ?? '', 30);
        if ($status !== '') {
            $filters['estado'] = $status;
        }

        return $filters;
    }
}

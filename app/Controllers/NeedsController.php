<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\ModelFactory;
use App\Core\NeedStatus;
use App\Core\Sanitizer;
use App\Core\Validator;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\NeedRequest;

final class NeedsController extends Controller
{
    private NeedRequest $needs;
    private Category $categories;
    private AuditLog $audit;

    public function __construct(?ModelFactory $models = null)
    {
        $models ??= ModelFactory::default();
        $this->needs = $models->needs();
        $this->categories = $models->categories();
        $this->audit = $models->audit();
    }

    public function index(): void
    {
        $this->authorize('needs.view');
        $needs = $this->needs->all();

        // Se envían flags de compatibilidad para que la vista se adapte a esquemas con o sin columnas nuevas.
        $this->render('needs/index', [
            'title' => 'Necesidades de equipos y software',
            'needs' => $needs,
            'needHistory' => $this->needs->historyMap(array_column($needs, 'id')),
            'supportsEstimatedCost' => $this->needs->supportsEstimatedCost(),
            'supportsJustification' => $this->needs->supportsJustification(),
            'supportsUnitEstimatedCost' => $this->needs->supportsUnitEstimatedCost(),
            'supportsNeedQuantity' => $this->needs->supportsQuantity(),
            'supportsAnioObjetivo' => $this->needs->supportsAnioObjetivo(),
            'supportsAdminResponse' => $this->needs->supportsAdminResponse(),
            'supportsProcessor' => $this->needs->supportsProcessor(),
        ]);
    }

    public function updateStatus(): void
    {
        try {
            $this->authorize('needs.manage');
            $this->csrf();
            $status = NeedStatus::normalize((string) ($_POST['estado'] ?? ''));
            NeedStatus::assertValid($status);

            $id = (int) ($_POST['id'] ?? 0);
            $before = $this->needs->find($id);
            if (!$before) {
                throw new \RuntimeException('La solicitud no existe.');
            }

            $comment = Sanitizer::text($_POST['respuesta_administrativa'] ?? $_POST['comentario_resolucion'] ?? '', 1000);
            $auditId = $this->audit->create(Auth::id(), 'NECESIDADES', 'ACTUALIZAR_ESTADO', "Solicitud #{$id} cambió a {$status}.", 'INFO', [
                'entity' => 'necesidades',
                'entity_id' => $id,
                'result' => $status,
                'reason' => $comment,
                'before' => $before,
                'after' => [
                    'estado' => $status,
                    'respuesta_administrativa' => $comment,
                    'usuario_procesador_id' => Auth::id(),
                ],
            ]);
            $this->needs->transitionStatus($id, $status, $comment, Auth::id(), $auditId);
            flash('success', 'Solicitud actualizada.');
            $this->redirect('needs');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'needs');
        }
    }

    public function createPortalRequest(): void
    {
        try {
            $this->requireCollaborator();
            $this->csrf();
            $user = Auth::user();

            $type = $_POST['tipo_necesidad'] ?? '';
            if (!in_array($type, ['EQUIPO', 'SOFTWARE', 'LICENCIA'], true)) {
                throw new \RuntimeException('Tipo de necesidad inválido.');
            }

            $data = [
                'colaborador_id' => (int) $user['colaborador_id'],
                'categoria_id' => (int) ($_POST['categoria_id'] ?? 0) ?: null,
                'tipo_necesidad' => $type,
                'descripcion' => Validator::required(Sanitizer::text($_POST['descripcion'] ?? '', 500), 'Descripción'),
                'justificacion' => Validator::required(Sanitizer::text($_POST['justificacion'] ?? $_POST['descripcion'] ?? '', 1000), 'Justificación'),
                'prioridad' => in_array($_POST['prioridad'] ?? '', ['BAJA', 'MEDIA', 'ALTA'], true) ? $_POST['prioridad'] : 'MEDIA',
            ];

            $quantity = $this->needs->supportsQuantity()
                ? Validator::integerRange((int) ($_POST['cantidad'] ?? 1), 1, 100000, 'Cantidad')
                : 1;
            $unitCost = Validator::positiveNumber(
                Sanitizer::decimal($_POST['costo_unitario_estimado'] ?? $_POST['costo_estimado'] ?? 0),
                'Costo unitario estimado'
            );

            // Campos opcionales por soporte de esquema: solo se incluyen cuando existen en la base activa.
            if ($this->needs->supportsEstimatedCost()) {
                $data['costo_estimado'] = $unitCost * $quantity;
            }
            if ($this->needs->supportsUnitEstimatedCost()) {
                $data['costo_unitario_estimado'] = $unitCost;
            }
            if ($this->needs->supportsQuantity()) {
                $data['cantidad'] = $quantity;
            }
            if ($this->needs->supportsAnioObjetivo()) {
                $year = (int) ($_POST['anio_objetivo'] ?? date('Y'));
                $data['anio_objetivo'] = Validator::integerRange($year, 2020, 2100, 'Año objetivo');
            }

            $id = $this->needs->create($data);

            $this->audit->create(Auth::id(), 'NECESIDADES', 'CREAR_PORTAL', "Solicitud #{$id} creada desde Portal del Colaborador.", 'INFO', [
                'entity' => 'necesidades',
                'entity_id' => $id,
                'after' => $data,
            ]);
            flash('success', 'Solicitud enviada al administrador.');
            $this->redirect('portal');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'portal');
        }
    }
}

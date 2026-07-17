<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\InventoryStatus;
use App\Core\ModelFactory;
use App\Core\Sanitizer;
use App\Core\Validator;
use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\Collaborator;
use App\Models\InventoryItem;
use App\Models\ReturnReview;

final class AssignmentsController extends Controller
{
    private Assignment $assignments;
    private InventoryItem $inventory;
    private Collaborator $collaborators;
    private AuditLog $audit;
    private ReturnReview $returns;

    public function __construct(?ModelFactory $models = null)
    {
        $models ??= ModelFactory::default();
        $this->assignments = $models->assignments();
        $this->inventory = $models->inventory();
        $this->collaborators = $models->collaborators();
        $this->audit = $models->audit();
        $this->returns = $models->returns();
    }

    public function index(): void
    {
        $this->authorize('assignments.view');
        $this->render('assignments/index', [
            'title' => 'Asignación de equipos',
            'assignments' => $this->assignments->all(),
            'returnSchemaReady' => $this->assignments->supportsFormalReturns(),
            'pendingReviews' => $this->returns->pending(),
        ]);
    }

    public function create(): void
    {
        $this->authorize('assignments.manage');
        $this->render('assignments/form', [
            'title' => 'Asignar activo a colaborador',
            'availableItems' => $this->inventory->available(),
            'collaborators' => $this->collaborators->all(true),
        ]);
    }

    public function store(): void
    {
        try {
            $this->authorize('assignments.manage');
            $this->csrf();
            // La asignacion cambia el activo a ASIGNADO dentro del modelo y deja auditoria.
            $data = [
                'inventario_id' => Validator::integerRange((int) ($_POST['inventario_id'] ?? 0), 1, PHP_INT_MAX, 'Activo'),
                'colaborador_id' => Validator::integerRange((int) ($_POST['colaborador_id'] ?? 0), 1, PHP_INT_MAX, 'Colaborador'),
                'fecha_asignacion' => Validator::date(Sanitizer::text($_POST['fecha_asignacion'] ?? '', 10), 'Fecha de asignación'),
                'ip_asignada' => Sanitizer::text($_POST['ip_asignada'] ?? '', 50),
                'observaciones' => Sanitizer::text($_POST['observaciones'] ?? '', 500),
            ];

            $auditId = $this->audit->create(Auth::id(), 'ASIGNACIONES', 'ASIGNAR', "Solicitud de asignación para activo #{$data['inventario_id']}.", 'INFO', [
                'entity' => 'asignaciones',
                'after' => $data,
            ]);
            $id = $this->assignments->create($data, Auth::id(), $auditId);
            flash('success', 'Activo asignado correctamente al colaborador.');
            $this->redirect('assignments');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'assignments/create');
        }
    }

    public function close(): void
    {
        try {
            $this->authorize('assignments.manage');
            $this->csrf();
            $id = (int) ($_POST['id'] ?? 0);

            $returnData = [
                'motivo' => Validator::required(Sanitizer::text($_POST['motivo'] ?? '', 160), 'Motivo de devolución'),
                'estado_fisico' => null,
                'observaciones' => Sanitizer::text($_POST['observaciones'] ?? '', 500),
                'evidencia' => Sanitizer::text($_POST['evidencia'] ?? '', 255),
            ];

            $auditId = $this->audit->create(Auth::id(), 'ASIGNACIONES', 'SOLICITAR_DEVOLUCION', "Solicitud de devolución para asignación #{$id}.", 'INFO', [
                'entity' => 'asignaciones',
                'entity_id' => $id,
                'reason' => $returnData['motivo'],
                'after' => $returnData,
            ]);
            $this->assignments->close($id, $returnData['observaciones'], $returnData, Auth::id(), $auditId);
            flash('success', 'Solicitud de devolución registrada. Queda pendiente de recepción física.');
            $this->redirect('assignments');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'assignments');
        }
    }

    public function receive(): void
    {
        try {
            $this->authorize('assignments.manage');
            $this->csrf();

            $returnId = Validator::integerRange((int) ($_POST['devolucion_id'] ?? 0), 1, PHP_INT_MAX, 'Devolución');
            $physicalState = strtoupper(Sanitizer::text($_POST['estado_fisico'] ?? 'BUENO', 20));
            if (!in_array($physicalState, ['BUENO', 'REGULAR', 'DANADO', 'INCOMPLETO'], true)) {
                throw new \RuntimeException('Estado físico inválido.');
            }

            $receiptData = [
                'estado_fisico' => $physicalState,
                'evidencia' => Sanitizer::text($_POST['evidencia'] ?? '', 255),
                'accesorios_recibidos' => Sanitizer::text($_POST['accesorios_recibidos'] ?? '', 1000),
                'observacion_recepcion' => Sanitizer::text($_POST['observacion_recepcion'] ?? '', 1000),
            ];

            $auditId = $this->audit->create(Auth::id(), 'ASIGNACIONES', 'RECIBIR_DEVOLUCION', "Recepción física de devolución #{$returnId}.", 'INFO', [
                'entity' => 'devoluciones',
                'entity_id' => $returnId,
                'after' => $receiptData,
            ]);

            $this->returns->receiveReturn($returnId, (int) Auth::id(), $auditId, $receiptData);
            flash('success', 'Recepción física registrada. La devolución queda lista para revisión técnica.');
            $this->redirect('assignments');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'assignments');
        }
    }

    public function review(): void
    {
        try {
            $this->authorize('assignments.manage');
            $this->csrf();

            $result = $_POST['resultado'] ?? '';
            // La revision tecnica decide si el activo vuelve, queda en descarte o sale por donacion.
            if (!in_array($result, InventoryStatus::reviewResults(), true)) {
                throw new \RuntimeException('Resultado de revisión inválido.');
            }

            $returnId = (int) ($_POST['devolucion_id'] ?? 0);
            $observation = Validator::required(Sanitizer::text($_POST['observacion_tecnica'] ?? '', 1000), 'Observación técnica');
            $reviewData = [
                'diagnostico' => Validator::required(Sanitizer::text($_POST['diagnostico'] ?? '', 1000), 'Diagnóstico'),
                'opinion_tecnica' => $observation,
                'recomendacion' => Sanitizer::text($_POST['recomendacion'] ?? '', 1000),
                'evidencia' => Sanitizer::text($_POST['evidencia'] ?? '', 255),
                'aprobador_id' => Auth::id(),
            ];

            if ($result === InventoryStatus::DESCARTE) {
                $reviewData['observacion_tecnica_descarte'] = $observation;
                $reviewData['evaluador_descarte_id'] = Auth::id();
                $reviewData['fecha_evaluacion_descarte'] = date('Y-m-d');
                $reviewData['evidencia_descarte'] = $reviewData['evidencia'];
                $reviewData['responsable_descarte_id'] = Auth::id();
                $reviewData['motivo_descarte'] = Validator::required(Sanitizer::text($_POST['motivo_final'] ?? '', 255), 'Motivo de descarte');
            }

            if ($result === InventoryStatus::DONADO) {
                $reviewData['responsable_donacion'] = Validator::required(Sanitizer::text($_POST['responsable_donacion'] ?? '', 120), 'Responsable de donación');
                $reviewData['beneficiario_donacion'] = Validator::required(Sanitizer::text($_POST['beneficiario_donacion'] ?? '', 160), 'Beneficiario de donación');
                $reviewData['valor_donacion'] = Validator::positiveNumber(Sanitizer::decimal($_POST['valor_donacion'] ?? 0), 'Valor de donación');
                $reviewData['fecha_donacion'] = date('Y-m-d');
                $reviewData['evidencia_donacion'] = $reviewData['evidencia'];
                $reviewData['observacion_donacion'] = Validator::required(Sanitizer::text($_POST['motivo_final'] ?? '', 1000), 'Motivo de donación');
                $reviewData['autorizador_donacion_id'] = Auth::id();
            }

            $auditId = $this->audit->create(Auth::id(), 'ASIGNACIONES', 'REVISION_TECNICA', "Devolución #{$returnId} revisada con resultado {$result}.", 'INFO', [
                'entity' => 'devoluciones',
                'entity_id' => $returnId,
                'result' => $result,
                'after' => [
                    'resultado' => $result,
                    'observacion_tecnica' => $observation,
                    'diagnostico' => $reviewData['diagnostico'],
                    'recomendacion' => $reviewData['recomendacion'],
                ],
            ]);
            $this->returns->completeReview($returnId, (int) Auth::id(), $result, $observation, Auth::id(), $auditId, $reviewData);
            flash('success', 'Revisión técnica registrada.');
            $this->redirect('assignments');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'assignments');
        }
    }
}

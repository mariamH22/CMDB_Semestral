<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\ModelFactory;
use App\Core\Sanitizer;
use App\Core\Validator;
use App\Models\Assignment;
use App\Models\LicenseAssignment;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\NeedRequest;
use App\Models\ReturnReview;
use App\Models\User;

final class PortalController extends Controller
{
    private Assignment $assignments;
    private NeedRequest $needs;
    private Category $categories;
    private AuditLog $audit;
    private User $users;
    private LicenseAssignment $licenseAssignments;
    private ReturnReview $returns;

    public function __construct(?ModelFactory $models = null)
    {
        $models ??= ModelFactory::default();
        $this->assignments = $models->assignments();
        $this->needs = $models->needs();
        $this->categories = $models->categories();
        $this->audit = $models->audit();
        $this->users = $models->users();
        $this->licenseAssignments = $models->licenseAssignments();
        $this->returns = $models->returns();
    }

    public function index(): void
    {
        $this->requireCollaborator();
        $user = Auth::user();
        $needs = $this->needs->all((int) $user['colaborador_id']);
        // Cada visita al portal queda registrada para trazabilidad del colaborador.
        $this->audit->portalAccess((int) $user['id']);

        // Vista del portal: muestra además historial formal de solicitudes y licencias activas para el colaborador.
        $this->render('portal/index', [
            'title' => 'Portal del Colaborador',
            'equipment' => $this->assignments->forCollaborator((int) $user['colaborador_id']),
            'supportsFormalReturns' => $this->assignments->supportsFormalReturns(),
            'needs' => $needs,
            'needHistory' => $this->needs->historyMap(array_column($needs, 'id')),
            'licenses' => $this->licenseAssignments->activeByCollaborator((int) $user['colaborador_id']),
            'returns' => $this->returns->forCollaborator((int) $user['colaborador_id']),
            'categories' => $this->categories->all(true),
            'history' => $this->audit->portalHistory((int) $user['id']),
            'supportsEstimatedCost' => $this->needs->supportsEstimatedCost(),
            'supportsJustification' => $this->needs->supportsJustification(),
            'supportsUnitEstimatedCost' => $this->needs->supportsUnitEstimatedCost(),
            'supportsNeedQuantity' => $this->needs->supportsQuantity(),
            'supportsAnioObjetivo' => $this->needs->supportsAnioObjetivo(),
        ]);
    }

    public function requestReturn(): void
    {
        try {
            $this->requireCollaborator();
            $this->csrf();

            $user = Auth::user();
            $assignmentId = Validator::integerRange((int) ($_POST['asignacion_id'] ?? 0), 1, PHP_INT_MAX, 'Asignación');

            $reason = Validator::required(Sanitizer::text($_POST['motivo'] ?? '', 160), 'Motivo');
            $observation = Sanitizer::text($_POST['observaciones'] ?? '', 1000);
            $evidence = Sanitizer::text($_POST['evidencia'] ?? '', 255);

            $auditId = $this->audit->create(Auth::id(), 'PORTAL', 'SOLICITAR_DEVOLUCION', "Solicitud de devolución para asignación #{$assignmentId}.", 'INFO', [
                'entity' => 'asignaciones',
                'entity_id' => $assignmentId,
                'after' => [
                    'motivo' => $reason,
                    'evidencia' => $evidence,
                ],
            ]);

            $this->assignments->close(
                $assignmentId,
                $observation,
                [
                    'motivo' => $reason,
                    'estado_fisico' => null,
                    'observaciones' => $observation,
                    'evidencia' => $evidence,
                ],
                Auth::id(),
                $auditId,
                // Este colaborador_id limita la devolucion a equipos propios y evita IDOR.
                (int) $user['colaborador_id']
            );

            flash('success', 'Solicitud de devolución registrada. El equipo queda pendiente de recepción física por un usuario autorizado.');
            $this->redirect('portal');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'portal');
        }
    }

    public function passwordForm(): void
    {
        $this->requireCollaborator();
        $this->render('portal/password', ['title' => 'Cambiar contraseña']);
    }

    public function changePassword(): void
    {
        try {
            $this->requireCollaborator();
            $this->csrf();

            $user = $this->users->find((int) Auth::id());
            $current = (string) ($_POST['password_actual'] ?? '');
            if (!$user || !$this->users->verifyPassword($user, $current)) {
                throw new \RuntimeException('La contraseña actual es incorrecta.');
            }

            $new = Validator::password((string) ($_POST['password_nueva'] ?? ''));
            if ($new !== (string) ($_POST['password_confirmacion'] ?? '')) {
                throw new \RuntimeException('La confirmación no coincide.');
            }

            $this->users->changePassword((int) Auth::id(), $new);
            $this->audit->create(Auth::id(), 'PORTAL', 'CAMBIAR_CONTRASENA', 'El colaborador cambió su contraseña.', 'INFO', [
                'entity' => 'usuarios',
                'entity_id' => Auth::id(),
                'after' => [
                    'password_actual' => $current,
                    'password_nueva' => $new,
                ],
            ]);
            flash('success', 'Contraseña actualizada correctamente.');
            $this->redirect('portal');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'portal/password');
        }
    }
}

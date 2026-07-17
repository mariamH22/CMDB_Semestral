<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\ModelFactory;
use App\Core\Sanitizer;
use App\Core\Validator;
use App\Models\AuditLog;
use App\Models\Collaborator;

final class CollaboratorsController extends Controller
{
    private Collaborator $collaborators;
    private AuditLog $audit;

    public function __construct(?ModelFactory $models = null)
    {
        $models ??= ModelFactory::default();
        $this->collaborators = $models->collaborators();
        $this->audit = $models->audit();
    }

    public function index(): void
    {
        $this->authorize('collaborators.view');
        $this->render('collaborators/index', [
            'title' => 'Colaboradores',
            'collaborators' => $this->collaborators->all()
        ]);
    }

    public function create(): void
    {
        $this->authorize('collaborators.manage');
        $this->render('collaborators/form', [
            'title' => 'Nuevo colaborador',
            'collaborator' => null,
            'locationHistory' => [],
            'locationHistoryReady' => $this->collaborators->locationHistoryReady(),
        ]);
    }

    public function store(): void
    {
        try {
            $this->authorize('collaborators.manage');
            $this->csrf();
            $photo = $this->uploadImage('foto', 'collaborators');
            $data = $this->data();
            $data['foto'] = $photo['path'] ?? null;
            $id = $this->collaborators->create($data);
            $auditId = $this->audit->create(Auth::id(), 'COLABORADORES', 'CREAR', "Colaborador #{$id} registrado.", 'INFO', [
                'entity' => 'colaboradores',
                'entity_id' => $id,
                'after' => $data,
            ]);
            if ((string) ($data['ubicacion'] ?? '') !== '') {
                $this->collaborators->recordLocationHistory(
                    $id,
                    null,
                    (string) $data['ubicacion'],
                    Auth::id(),
                    $auditId,
                    'Registro inicial de ubicación'
                );
            }
            flash('success', 'Colaborador registrado correctamente.');
            $this->redirect('collaborators');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'collaborators/create');
        }
    }

    public function edit(): void
    {
        $this->authorize('collaborators.manage');
        $collaborator = $this->collaborators->find((int) ($_GET['id'] ?? 0));
        if (!$collaborator) {
            flash('error', 'Colaborador no encontrado.');
            $this->redirect('collaborators');
        }

        $this->render('collaborators/form', [
            'title' => 'Editar colaborador',
            'collaborator' => $collaborator,
            'locationHistory' => $this->collaborators->locationHistory((int) $collaborator['id']),
            'locationHistoryReady' => $this->collaborators->locationHistoryReady(),
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            $this->authorize('collaborators.manage');
            $this->csrf();
            $photo = $this->uploadImage('foto', 'collaborators');
            $data = $this->data();
            $data['foto'] = $photo['path'] ?? null;
            $before = $this->collaborators->find($id);
            $this->collaborators->update($id, $data);
            $auditId = $this->audit->create(Auth::id(), 'COLABORADORES', 'ACTUALIZAR', "Colaborador #{$id} actualizado.", 'INFO', [
                'entity' => 'colaboradores',
                'entity_id' => $id,
                'before' => $before ?? [],
                'after' => $data,
            ]);
            if ($before !== null && (string) ($before['ubicacion'] ?? '') !== (string) ($data['ubicacion'] ?? '')) {
                $this->collaborators->recordLocationHistory(
                    $id,
                    $before['ubicacion'] ?? null,
                    (string) ($data['ubicacion'] ?? ''),
                    Auth::id(),
                    $auditId,
                    $this->locationReason()
                );
            }
            flash('success', 'Colaborador actualizado correctamente.');
            $this->redirect('collaborators');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'collaborators/edit?id=' . $id);
        }
    }

    private function data(): array
    {
        // La ubicación ya no es obligatoria: se normaliza como cadena vacía para ser compatible con esquemas nuevos y antiguos.
        $ubicacion = Validator::optional(Sanitizer::text($_POST['ubicacion'] ?? '', 150)) ?? '';
        return [
            'nombres' => Validator::required(Sanitizer::text($_POST['nombres'] ?? '', 100), 'Nombres'),
            'apellidos' => Validator::required(Sanitizer::text($_POST['apellidos'] ?? '', 100), 'Apellidos'),
            'identificacion' => Validator::required(Sanitizer::text($_POST['identificacion'] ?? '', 40), 'Identificación'),
            'departamento' => Validator::required(Sanitizer::text($_POST['departamento'] ?? '', 100), 'Departamento'),
            'ubicacion' => $ubicacion,
            'direccion' => Sanitizer::text($_POST['direccion'] ?? '', 255),
            'telefono' => Sanitizer::text($_POST['telefono'] ?? '', 30),
            'email' => Validator::email(Sanitizer::email($_POST['email'] ?? '')),
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];
    }

    private function locationReason(): string
    {
        $reason = Sanitizer::text($_POST['motivo_ubicacion'] ?? '', 500);

        return $reason !== '' ? $reason : 'Actualización de ubicación';
    }
}

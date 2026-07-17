<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\DigitalSignature;
use App\Core\ModelFactory;
use App\Core\Sanitizer;
use App\Core\Validator;
use App\Models\AuditLog;
use App\Models\User;

final class UsersController extends Controller
{
    private Database $db;
    private User $users;
    private AuditLog $audit;

    public function __construct(?ModelFactory $models = null)
    {
        $models ??= ModelFactory::default();
        $this->db = $models->db();
        $this->users = $models->users();
        $this->audit = $models->audit();
    }

    public function index(): void
    {
        $this->authorize('users.manage');
        $this->render('users/index', ['title' => 'Usuarios administradores', 'users' => $this->users->all()]);
    }

    public function create(): void
    {
        $this->authorize('users.manage');
        $this->render('users/form', [
            'title' => 'Registrar usuario',
            'user' => null,
            'collaborators' => $this->users->activeCollaborators(),
        ]);
    }

    public function store(): void
    {
        try {
            $this->authorize('users.manage');
            $this->csrf();
            $data = $this->data(true);
            $id = $this->users->create($data);
            $created = $this->users->find($id);
            if (!$created) {
                throw new \RuntimeException('No fue posible confirmar el usuario creado. Revise la conexión o intente nuevamente.');
            }

            $this->audit->create(Auth::id(), 'USUARIOS', 'CREAR', "Usuario #{$id} creado.", 'INFO', [
                'entity' => 'usuarios',
                'entity_id' => $id,
                'after' => $data,
            ]);
            flash('success', 'Usuario "' . $created['nombre_usuario'] . '" registrado correctamente.');
            $this->redirect('users');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'users/create');
        }
    }

    public function edit(): void
    {
        $this->authorize('users.manage');
        $user = $this->users->find((int) ($_GET['id'] ?? 0));
        if (!$user) {
            flash('error', 'Usuario no encontrado.');
            $this->redirect('users');
        }
        $this->render('users/form', [
            'title' => 'Editar usuario',
            'user' => $user,
            'collaborators' => $this->users->activeCollaborators(),
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            $this->authorize('users.manage');
            $this->csrf();

            if ($id === Auth::id() && !(int) ($_POST['activo'] ?? 0)) {
                throw new \RuntimeException('No puede dar de baja su propio usuario.');
            }

            $data = $this->data(false);
            $before = $this->users->find($id);
            $this->users->update($id, $data);
            $auditId = $this->audit->create(Auth::id(), 'USUARIOS', 'ACTUALIZAR', "Usuario #{$id} actualizado.", 'INFO', [
                'entity' => 'usuarios',
                'entity_id' => $id,
                'before' => $before ?? [],
                'after' => $data,
            ]);

            if ($before && ((string) $before['rol'] !== (string) $data['rol'] || (int) $before['activo'] !== (int) $data['activo'])) {
                DigitalSignature::signAction($this->db, Auth::id(), 'USUARIOS', 'ACTUALIZAR_PRIVILEGIOS', 'usuarios', $id, [
                    'rol_anterior' => $before['rol'],
                    'rol_nuevo' => $data['rol'],
                    'activo_anterior' => (int) $before['activo'],
                    'activo_nuevo' => (int) $data['activo'],
                ], $auditId);
            }

            flash('success', 'Usuario actualizado correctamente.');
            $this->redirect('users');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'users/edit?id=' . $id);
        }
    }

    public function unlock(): void
    {
        try {
            $this->authorize('users.manage');
            $this->csrf();
            $id = (int) ($_POST['id'] ?? 0);
            $before = $this->users->find($id);
            $this->users->unlock($id);
            $this->audit->create(Auth::id(), 'USUARIOS', 'DESBLOQUEAR', "Usuario #{$id} desbloqueado.", 'INFO', [
                'entity' => 'usuarios',
                'entity_id' => $id,
                'before' => $before ?? [],
                'after' => ['estado_cuenta' => 'ACTIVO', 'intentos_fallidos' => 0],
            ]);
            flash('success', 'Cuenta desbloqueada y contador de intentos reiniciado.');
            $this->redirect('users');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'users');
        }
    }

    public function toggleActive(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $this->authorize('users.manage');
            $this->csrf();

            if ($id === Auth::id()) {
                throw new \RuntimeException('No puede cambiar el estado activo de su propio usuario desde esta acción.');
            }

            $active = isset($_POST['activo']) ? 1 : 0;
            $before = $this->users->find($id);
            $this->users->setActive($id, $active);
            $auditId = $this->audit->create(Auth::id(), 'USUARIOS', $active ? 'REACTIVAR' : 'INACTIVAR', "Usuario #{$id} " . ($active ? 'reactivado' : 'inactivado') . '.', 'INFO', [
                'entity' => 'usuarios',
                'entity_id' => $id,
                'before' => $before ?? [],
                'after' => ['activo' => $active],
            ]);
            DigitalSignature::signAction($this->db, Auth::id(), 'USUARIOS', $active ? 'REACTIVAR' : 'INACTIVAR', 'usuarios', $id, [
                'activo_anterior' => $before ? (int) $before['activo'] : null,
                'activo_nuevo' => $active,
            ], $auditId);
            flash('success', $active ? 'Usuario reactivado correctamente.' : 'Usuario inactivado correctamente.');
            $this->redirect('users');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'users');
        }
    }

    private function data(bool $create): array
    {
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        if ($create || $password !== '') {
            Validator::password($password);

            if ($password !== $passwordConfirmation) {
                throw new \RuntimeException('La confirmación de contraseña no coincide.');
            }
        }

        return [
            'colaborador_id' => (int) ($_POST['colaborador_id'] ?? 0),
            'nombre_usuario' => Validator::required(Sanitizer::text($_POST['nombre_usuario'] ?? '', 50), 'Nombre de usuario'),
            'email' => Validator::email(Sanitizer::email($_POST['email'] ?? '')),
            'password' => $password,
            'rol' => in_array($_POST['rol'] ?? '', ['ADMIN', 'OPERADOR', 'COLABORADOR'], true) ? $_POST['rol'] : 'OPERADOR',
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];
    }
}

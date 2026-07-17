<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Database;
use App\Core\ModelFactory;
use App\Core\Sanitizer;
use App\Core\Validator;
use App\Models\AuditLog;
use App\Models\PasswordReset;
use App\Models\User;

final class AuthController extends Controller
{
    private Database $db;
    private User $users;
    private AuditLog $audit;
    private ModelFactory $models;
    // Mensaje unico para no revelar si fallo usuario, clave, baja logica o bloqueo.
    private const GENERIC_LOGIN_ERROR = 'Credenciales inválidas o cuenta no disponible.';

    public function __construct(?ModelFactory $models = null)
    {
        $models ??= ModelFactory::default();
        $this->models = $models;
        $this->db = $models->db();
        $this->users = $models->users();
        $this->audit = $models->audit();
    }

    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect(Auth::isCollaborator() ? 'portal' : 'dashboard');
        }

        $this->render('auth/login', ['title' => 'Iniciar sesión']);
    }

    public function login(): void
    {
        try {
            $this->csrf();

            $identifier = Sanitizer::email($_POST['identificador'] ?? '');
            $password = (string) ($_POST['password'] ?? '');
            Validator::required($identifier, 'Usuario o correo');
            Validator::required($password, 'Contraseña');

            // Límite temporal por identificador/IP para frenar intentos repetidos antes de consultar la cuenta.
            $rateLimitAttempts = (int) Config::get('security.login_rate_limit_attempts', 8);
            $rateLimitMinutes = (int) Config::get('security.login_rate_limit_minutes', 15);
            if ($this->audit->recentFailedLoginCount($identifier, $rateLimitMinutes) >= $rateLimitAttempts) {
                $this->audit->loginAttempt(null, $identifier, false, 'Límite temporal de intentos fallidos');
                flash('error', 'Demasiados intentos fallidos. Espere unos minutos e inténtelo nuevamente.');
                $this->redirect('login');
            }

            $user = $this->users->findByLogin($identifier);

            if (!$user) {
                $this->audit->loginAttempt(null, $identifier, false, 'Usuario inexistente');
                flash('error', self::GENERIC_LOGIN_ERROR);
                $this->redirect('login');
            }

            if (!(int) $user['activo']) {
                $this->audit->loginAttempt((int) $user['id'], $identifier, false, 'Usuario dado de baja');
                flash('error', self::GENERIC_LOGIN_ERROR);
                $this->redirect('login');
            }

            if ($user['estado_cuenta'] === 'BLOQUEADO') {
                $this->audit->loginAttempt((int) $user['id'], $identifier, false, 'Cuenta bloqueada');
                flash('error', self::GENERIC_LOGIN_ERROR);
                $this->redirect('login');
            }

            if (!$this->users->verifyPassword($user, $password)) {
                $updated = $this->users->recordFailure((int) $user['id']);
                $reason = ($updated['estado_cuenta'] ?? '') === 'BLOQUEADO'
                    ? 'Contraseña inválida. Cuenta bloqueada por intentos fallidos.'
                    : 'Contraseña inválida';

                $this->audit->loginAttempt((int) $user['id'], $identifier, false, $reason);
                flash('error', self::GENERIC_LOGIN_ERROR);
                $this->redirect('login');
            }

            $this->users->resetFailuresAndLogin((int) $user['id']);
            $this->audit->loginAttempt((int) $user['id'], $identifier, true, 'Inicio de sesión correcto');
            // La bitacora guarda el evento exitoso con usuario, entidad y resultado verificable.
            $this->audit->create((int) $user['id'], 'AUTENTICACION', 'LOGIN', 'Inicio de sesión exitoso.', 'INFO', [
                'entity' => 'usuarios',
                'entity_id' => (int) $user['id'],
                'result' => 'OK',
            ]);

            Auth::login($user);

            flash('success', 'Bienvenido/a al sistema CMDB.');
            $this->redirect($user['rol'] === 'COLABORADOR' ? 'portal' : 'dashboard');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'login');
        }
    }

    public function registerForm(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }

        // El registro público queda cerrado; las altas se hacen desde el módulo Usuarios.
        flash('error', 'El registro público está deshabilitado. Solicite la creación de usuario al administrador.');
        $this->redirect('login');
    }

    public function register(): void
    {
        // Mantiene la ruta existente sin permitir creación pública de colaboradores/usuarios.
        flash('error', 'El registro público está deshabilitado. Solicite la creación de usuario al administrador.');
        $this->redirect('login');
    }

    public function logout(): void
    {
        $this->csrf();

        if (Auth::check()) {
            $this->audit->create(Auth::id(), 'AUTENTICACION', 'LOGOUT', 'Cierre de sesión.', 'INFO', [
                'entity' => 'usuarios',
                'entity_id' => Auth::id(),
                'result' => 'OK',
            ]);
        }

        Auth::logout();
        flash('success', 'Sesión cerrada correctamente.');
        $this->redirect('');
    }

    public function forgotForm(): void
    {
        $this->render('auth/forgot', ['title' => 'Recuperar contraseña']);
    }

    public function forgot(): void
    {
        try {
            $this->csrf();
            $email = Validator::email(Sanitizer::email($_POST['email'] ?? ''));
            $user = $this->users->findByEmail($email);

            // Evita revelar si un correo no existe.
            if (!$user) {
                flash('success', 'Si el correo existe, se generó una solicitud de recuperación.');
                $this->redirect('forgot-password');
            }

            $token = bin2hex(random_bytes(20));
            $this->models->passwordResets()->create((int) $user['id'], $token);
            // AuditDataSanitizer enmascara tokens/contraseñas antes de persistir contexto sensible.
            $this->audit->create((int) $user['id'], 'AUTENTICACION', 'RECUPERACION_SOLICITADA', 'Solicitud de recuperación de contraseña.', 'INFO', [
                'entity' => 'usuarios',
                'entity_id' => (int) $user['id'],
                'after' => ['token' => $token],
            ]);

            // Por seguridad, el token no se muestra salvo que se habilite explicitamente para una demo local.
            if (Config::get('security.show_reset_demo_link', false)) {
                $_SESSION['reset_demo_link'] = url('reset-password?token=' . urlencode($token));
            }

            flash('success', 'Si el correo existe, se generó una solicitud de recuperación.');
            $this->redirect('forgot-password');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'forgot-password');
        }
    }

    public function resetForm(): void
    {
        $this->render('auth/reset', [
            'title' => 'Restablecer contraseña',
            'token' => (string) ($_GET['token'] ?? ''),
        ]);
    }

    public function reset(): void
    {
        try {
            $this->csrf();
            $token = (string) ($_POST['token'] ?? '');
            $password = Validator::password((string) ($_POST['password'] ?? ''));

            if ($password !== (string) ($_POST['password_confirmation'] ?? '')) {
                throw new \RuntimeException('La confirmación de contraseña no coincide.');
            }

            $reset = $this->models->passwordResets()->findValidByToken($token);
            if (!$reset) {
                throw new \RuntimeException('El enlace de recuperación es inválido o expiró.');
            }

            $this->users->changePassword((int) $reset['usuario_id'], $password);
            $this->users->unlock((int) $reset['usuario_id']);
            $this->models->passwordResets()->use((int) $reset['id']);
            // Se audita el restablecimiento, pero el sanitizador no deja la clave en claro.
            $this->audit->create((int) $reset['usuario_id'], 'AUTENTICACION', 'CONTRASENA_RESTABLECIDA', 'Contraseña restablecida mediante token.', 'INFO', [
                'entity' => 'usuarios',
                'entity_id' => (int) $reset['usuario_id'],
                'after' => ['token' => $token, 'password' => $password],
            ]);

            flash('success', 'Contraseña actualizada. Ya puede iniciar sesión.');
            $this->redirect('login');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'reset-password?token=' . urlencode((string) ($_POST['token'] ?? '')));
        }
    }
}

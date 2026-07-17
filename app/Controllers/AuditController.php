<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\ModelFactory;
use App\Core\Sanitizer;
use App\Core\ServiceContainer;
use App\Core\Validator;
use App\Models\AuditLog;
use App\Models\AuditTrailVerifier;
use App\Models\DigitalSignatureRecord;
use App\Models\RsaKey;
use App\Models\User;

final class AuditController extends Controller
{
    private Database $db;
    private AuditLog $audit;
    private DigitalSignatureRecord $signatures;
    private AuditTrailVerifier $trailVerifier;
    private RsaKey $keys;
    private User $users;

    public function __construct(?ModelFactory $models = null)
    {
        $models ??= ModelFactory::default();
        $this->db = $models->db();
        $this->audit = $models->audit();
        $this->signatures = $models->digitalSignatures();
        $this->trailVerifier = $models->auditTrailVerifier();
        $this->keys = $models->rsaKeys();
        $this->users = $models->users();
    }

    public function index(): void
    {
        $this->authorize('audit.view');

        $this->render('audit/index', [
            'title' => 'Bitácora de auditoría',
            'logs' => $this->audit->all(),
            'signatureSchemaReady' => $this->signatures->schemaReady(),
            'keyManagementConfigured' => ServiceContainer::keyManagement()->isConfigured(),
            'signatures' => $this->signatures->verifiedRecent(),
            'trailSummary' => $this->trailVerifier->verify(300),
        ]);
    }

    public function trail(): void
    {
        $this->authorize('audit.view');

        $this->render('audit/trail', [
            'title' => 'Verificador de auditoría',
            'summary' => $this->trailVerifier->verify(1000),
        ]);
    }

    public function keys(): void
    {
        $this->authorize('audit.view');

        $this->render('audit/keys', [
            'title' => 'Llaves RSA',
            'keys' => $this->keys->all(),
            'users' => $this->users->all(),
            'signatures' => $this->signatures->verifiedRecent(50),
            'signatureSchemaReady' => $this->signatures->schemaReady(),
            'keySchemaReady' => $this->keys->schemaReady(),
            'lifecycleReady' => $this->keys->supportsLifecycle(),
            'keyManagementConfigured' => ServiceContainer::keyManagement()->isConfigured(),
        ]);
    }

    public function generateKey(): void
    {
        try {
            $this->authorize('audit.view');
            $this->csrf();
            $this->reauthenticate();

            $userId = Validator::integerRange((int) ($_POST['usuario_id'] ?? 0), 1, PHP_INT_MAX, 'Usuario');
            $name = Sanitizer::text($_POST['nombre'] ?? '', 120) ?: 'Llave RSA de usuario';
            $keyId = $this->keys->generateForUser($userId, $name);
            $key = $this->keys->find($keyId);

            $this->audit->create(Auth::id(), 'SEGURIDAD', 'GENERAR_LLAVE_RSA', "Llave RSA #{$keyId} generada para usuario #{$userId}.", 'INFO', [
                'entity' => 'llaves_rsa',
                'entity_id' => $keyId,
                'after' => $key ?? ['usuario_id' => $userId, 'nombre' => $name],
            ]);
            flash('success', 'Llave RSA generada correctamente.');
            $this->redirect('audit/keys');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'audit/keys');
        }
    }

    public function rotateKey(): void
    {
        try {
            $this->authorize('audit.view');
            $this->csrf();
            $this->reauthenticate();

            $userId = Validator::integerRange((int) ($_POST['usuario_id'] ?? 0), 1, PHP_INT_MAX, 'Usuario');
            $name = Sanitizer::text($_POST['nombre'] ?? '', 120) ?: 'Llave RSA rotada';
            $before = $this->keys->activeForUser($userId);
            $keyId = $this->keys->rotateForUser($userId, $name);
            $after = $this->keys->find($keyId);

            $this->audit->create(Auth::id(), 'SEGURIDAD', 'ROTAR_LLAVE_RSA', "Llave RSA nueva #{$keyId} generada para usuario #{$userId}.", 'INFO', [
                'entity' => 'llaves_rsa',
                'entity_id' => $keyId,
                'before' => $before ?? [],
                'after' => $after ?? ['usuario_id' => $userId, 'nombre' => $name],
            ]);
            flash('success', 'Llave RSA rotada correctamente. La llave anterior queda reemplazada.');
            $this->redirect('audit/keys');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'audit/keys');
        }
    }

    public function revokeKey(): void
    {
        try {
            $this->authorize('audit.view');
            $this->csrf();
            $this->reauthenticate();

            $keyId = Validator::integerRange((int) ($_POST['llave_id'] ?? 0), 1, PHP_INT_MAX, 'Llave');
            $reason = Validator::required(Sanitizer::text($_POST['motivo'] ?? '', 255), 'Motivo de revocación');
            $before = $this->keys->find($keyId);
            $this->keys->revoke($keyId, $reason, Auth::id());
            $after = $this->keys->find($keyId);

            $this->audit->create(Auth::id(), 'SEGURIDAD', 'REVOCAR_LLAVE_RSA', "Llave RSA #{$keyId} revocada.", 'INFO', [
                'entity' => 'llaves_rsa',
                'entity_id' => $keyId,
                'reason' => $reason,
                'before' => $before ?? [],
                'after' => $after ?? ['estado' => 'REVOCADA'],
            ]);
            flash('success', 'Llave RSA revocada. No podrá firmar nuevas acciones.');
            $this->redirect('audit/keys');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'audit/keys');
        }
    }

    public function verifySignature(): void
    {
        try {
            $this->authorize('audit.view');
            $this->csrf();

            $signatureId = Validator::integerRange((int) ($_POST['firma_id'] ?? 0), 1, PHP_INT_MAX, 'Firma');
            $status = $this->signatures->verifyById($signatureId);

            $this->audit->create(Auth::id(), 'SEGURIDAD', 'VERIFICAR_FIRMA_RSA', "Firma RSA #{$signatureId} verificada con resultado {$status}.", 'INFO', [
                'entity' => 'firmas_digitales',
                'entity_id' => $signatureId,
                'result' => $status,
            ]);
            flash('success', "Resultado de verificación: {$status}.");
            $this->redirect('audit/keys');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'audit/keys');
        }
    }

    private function reauthenticate(): void
    {
        $user = $this->users->find((int) Auth::id());
        $password = (string) ($_POST['password_actual'] ?? '');

        if (!$user || !$this->users->verifyPassword($user, $password)) {
            throw new \RuntimeException('Debe confirmar la acción con su contraseña actual.');
        }
    }
}

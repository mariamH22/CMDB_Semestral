<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\DigitalSignature;
use App\Core\IntegritySigner;
use App\Core\InventoryImagePolicy;
use App\Core\InventoryStatus;
use App\Core\LicensePolicy;
use App\Core\ModelFactory;
use App\Core\QrCode;
use App\Core\Sanitizer;
use App\Core\ServiceContainer;
use App\Core\Validator;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Collaborator;
use App\Models\InventoryItem;
use App\Models\InventoryQr;
use App\Models\InventoryStateHistory;
use App\Models\LicenseAssignment;

final class InventoryController extends Controller
{
    private Database $db;
    private InventoryItem $inventory;
    private Category $categories;
    private Collaborator $collaborators;
    private AuditLog $audit;
    private InventoryQr $qr;
    private InventoryStateHistory $history;
    private LicenseAssignment $licenseAssignments;

    public function __construct(?ModelFactory $models = null)
    {
        $models ??= ModelFactory::default();
        $this->db = $models->db();
        $this->inventory = $models->inventory();
        $this->categories = $models->categories();
        $this->collaborators = $models->collaborators();
        $this->audit = $models->audit();
        $this->qr = $models->inventoryQr();
        $this->history = $models->inventoryStateHistory();
        $this->licenseAssignments = $models->licenseAssignments();
    }

    public function index(): void
    {
        $this->authorize('inventory.view');
        $filters = [
            'tipo' => $_GET['tipo'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'buscar' => Sanitizer::text($_GET['buscar'] ?? '', 100),
            'sin_asignar' => isset($_GET['sin_asignar']),
            'licencias' => isset($_GET['licencias']),
        ];

        $this->render('inventory/index', [
            'title' => 'Inventario CMDB',
            'items' => $this->inventory->all($filters),
            'filters' => $filters,
            'nearDepreciation' => $this->inventory->nearDepreciation(90),
        ]);
    }

    public function create(): void
    {
        $this->authorize('inventory.manage');
        $this->render('inventory/form', [
            'title' => 'Registrar activo o licencia',
            'item' => null,
            'categories' => $this->categories->all(true),
            'licenseKeyConfigured' => ServiceContainer::licenseKeyProtector()->isConfigured(),
        ]);
    }

    public function store(): void
    {
        $uploadedImages = [];
        $inventoryPersisted = false;
        try {
            $this->authorize('inventory.manage');
            $this->csrf();
            $data = $this->data();
            InventoryImagePolicy::assertNewHardwareImages($data, $_FILES);
            $mainImage = $this->uploadImage('imagen_principal', 'equipment');
            $extraImage = $this->uploadImage('imagen_adicional', 'equipment');
            $this->rememberUploadedImages($uploadedImages, $mainImage);
            $this->rememberUploadedImages($uploadedImages, $extraImage);
            $data['imagen_principal'] = $mainImage['path'] ?? null;
            $data['thumbnail'] = $mainImage['thumbnail'] ?? null;
            $data['new_image_path'] = $extraImage['path'] ?? null;
            // Inventario e imagen adicional se guardan juntos para no dejar registros a medias.
            $id = $this->db->transaction(function () use ($data): int {
                $id = $this->inventory->create($data);
                if (!empty($data['new_image_path'])) {
                    $this->inventory->addImage($id, $data['new_image_path'], false);
                }

                return $id;
            });
            $inventoryPersisted = true;
            $this->audit->create(Auth::id(), 'INVENTARIO', 'CREAR', "Activo #{$id} registrado. Estado HMAC: " . (IntegritySigner::isConfigured() ? 'firmado' : 'pendiente de configuración') . '.', 'INFO', [
                'entity' => 'inventario',
                'entity_id' => $id,
                'after' => $data,
            ]);
            flash(
                'success',
                IntegritySigner::isConfigured()
                    ? 'Activo registrado correctamente. Se calculó y guardó la firma digital de integridad.'
                    : 'Activo registrado correctamente. La firma HMAC queda pendiente porque falta configurar la clave de integridad.'
            );
            $this->redirect('inventory');
        } catch (\Throwable $exception) {
            if (!$inventoryPersisted) {
                $this->cleanupUploadedImages($uploadedImages);
            }
            $this->formError($exception, 'inventory/create');
        }
    }

    public function edit(): void
    {
        $this->authorize('inventory.manage');
        $item = $this->inventory->find((int) ($_GET['id'] ?? 0));
        if (!$item) {
            flash('error', 'Activo no encontrado.');
            $this->redirect('inventory');
        }

        $this->render('inventory/form', [
            'title' => 'Editar activo',
            'item' => $item,
            'categories' => $this->categories->all(true),
            'licenseKeyConfigured' => ServiceContainer::licenseKeyProtector()->isConfigured(),
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $uploadedImages = [];
        $inventoryPersisted = false;
        try {
            $this->authorize('inventory.manage');
            $this->csrf();
            $current = $this->inventory->find($id);
            if (!$current) {
                throw new \RuntimeException('Activo no encontrado.');
            }
            $data = $this->data($current);
            InventoryImagePolicy::assertExistingHardwareImages($data, $_FILES, $current);
            $mainImage = $this->uploadImage('imagen_principal', 'equipment');
            $extraImage = $this->uploadImage('imagen_adicional', 'equipment');
            $this->rememberUploadedImages($uploadedImages, $mainImage);
            $this->rememberUploadedImages($uploadedImages, $extraImage);
            $data['imagen_principal'] = $mainImage['path'] ?? null;
            $data['thumbnail'] = $mainImage['thumbnail'] ?? null;
            $data['new_image_path'] = $extraImage['path'] ?? null;
            if ($current && (string) $current['estado'] !== (string) $data['estado']) {
                throw new \RuntimeException('El estado debe cambiarse desde el flujo formal, no desde edición general.');
            }
            $this->db->transaction(function () use ($id, $data): void {
                $this->inventory->update($id, $data);
            });
            $inventoryPersisted = true;
            $auditId = $this->audit->create(Auth::id(), 'INVENTARIO', 'ACTUALIZAR', "Activo #{$id} actualizado. Estado HMAC: " . (IntegritySigner::isConfigured() ? 'firmado' : 'pendiente de configuración') . '.', 'INFO', [
                'entity' => 'inventario',
                'entity_id' => $id,
                'before' => $current ?? [],
                'after' => $data,
            ]);
            if ($current && (float) $current['costo'] !== (float) $data['costo']) {
                DigitalSignature::signAction($this->db, Auth::id(), 'INVENTARIO', 'ACTUALIZAR_COSTO', 'inventario', $id, [
                    'costo_anterior' => (float) $current['costo'],
                    'costo_nuevo' => (float) $data['costo'],
                ], $auditId);
            }
            flash(
                'success',
                IntegritySigner::isConfigured()
                    ? 'Activo actualizado. La firma digital fue recalculada.'
                    : 'Activo actualizado. La firma HMAC queda pendiente porque falta configurar la clave de integridad.'
            );
            $this->redirect('inventory/detail?id=' . $id);
        } catch (\Throwable $exception) {
            if (!$inventoryPersisted) {
                $this->cleanupUploadedImages($uploadedImages);
            }
            $this->formError($exception, 'inventory/edit?id=' . $id);
        }
    }

    public function detail(): void
    {
        $this->authorize('inventory.view');
        $item = $this->inventory->find((int) ($_GET['id'] ?? 0));
        if (!$item) {
            flash('error', 'Activo no encontrado.');
            $this->redirect('inventory');
        }

        $id = (int) $item['id'];
        $qr = $this->qr->activeForInventory($id);
        $licenseRows = (int) $item['es_licencia'] ? $this->licenseAssignments->activeByInventory($id) : [];
        $licenseUsed = (int) $item['es_licencia'] ? $this->licenseAssignments->usedQuantity($id) : 0;
        $integridadConfigurada = IntegritySigner::isConfigured();
        $licenseProtector = ServiceContainer::licenseKeyProtector();
        $licenseCipherText = $item['clave_licencia_cifrada'] ?? null;
        $licenseLegacyValue = $item['clave_licencia'] ?? null;
        $licensePlainText = (int) $item['es_licencia'] ? $licenseProtector->decrypt($licenseCipherText, $licenseLegacyValue) : null;

        $this->render('inventory/detail', [
            'title' => 'Detalle del activo',
            'item' => $item,
            'integrityConfigured' => $integridadConfigurada,
            'canRevealLicense' => Auth::can('inventory.reveal_license'),
            'licenseRevealed' => !empty($_SESSION['license_reveals'][$id]),
            'licenseKeyDisplay' => (!empty($_SESSION['license_reveals'][$id]) && Auth::can('inventory.reveal_license')) ? $licensePlainText : null,
            'licenseKeyMasked' => $licenseProtector->mask($licenseCipherText, $licenseLegacyValue),
            'licenseKeyConfigured' => $licenseProtector->isConfigured(),
            'licenseKeyEncrypted' => $licenseProtector->isEncryptedPayload($licenseCipherText) || $licenseProtector->isEncryptedPayload($licenseLegacyValue),
            'licenseKeyLegacyPlaintext' => $licenseProtector->isLegacyPlaintext($licenseLegacyValue, $licenseCipherText),
            'qr' => $qr,
            'qrSchemaReady' => $this->qr->schemaReady(),
            'history' => $this->history->forInventory($id),
            'historySchemaReady' => $this->history->schemaReady(),
            'licenseSchemaReady' => $this->licenseAssignments->schemaReady(),
            'licenseAssignments' => $licenseRows,
            'licenseUsed' => $licenseUsed,
            'licenseAvailable' => max(0, (int) $item['cantidad'] - $licenseUsed),
            'collaborators' => $this->collaborators->all(true),
            'imageWarning' => InventoryImagePolicy::legacyWarning($item),
        ]);
    }

    public function qrSvg(): void
    {
        $this->authorize('inventory.view');
        $id = (int) ($_GET['id'] ?? 0);
        $item = $this->inventory->find($id);
        if (!$item) {
            http_response_code(404);
            header('Content-Type: image/svg+xml; charset=UTF-8');
            echo QrCode::svg('CMDB');
            return;
        }

        try {
            $payload = $this->qrPayload($id);
        } catch (\Throwable) {
            http_response_code(404);
            header('Content-Type: image/svg+xml; charset=UTF-8');
            header('Cache-Control: no-store');
            echo QrCode::svg('CMDB');
            return;
        }

        header('Content-Type: image/svg+xml; charset=UTF-8');
        header('Cache-Control: no-store');
        echo QrCode::svg($payload);
    }

    public function qrDownload(): void
    {
        $this->authorize('inventory.view');
        $id = (int) ($_GET['id'] ?? 0);
        $item = $this->inventory->find($id);
        if (!$item) {
            flash('error', 'Activo no encontrado.');
            $this->redirect('inventory');
        }

        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $item['codigo_activo']);

        header('Content-Type: image/svg+xml; charset=UTF-8');
        header('Content-Disposition: attachment; filename="qr_' . ($filename ?: 'activo') . '.svg"');
        header('Cache-Control: no-store');
        $this->audit->create(Auth::id(), 'QR', 'DESCARGAR', "QR del activo #{$id} descargado.", 'INFO', [
            'entity' => 'inventario',
            'entity_id' => $id,
        ]);
        echo QrCode::svg($this->qrPayload($id));
        exit;
    }

    public function qrLookup(): void
    {
        $token = Sanitizer::text($_GET['t'] ?? $_GET['token'] ?? '', 64);
        $asset = $this->qr->findPublicAssetByToken($token);
        if (!$asset) {
            http_response_code(404);
            // Los QR invalidos tambien se registran para detectar intentos de enumeracion.
            $this->audit->create(null, 'QR', 'ACCESO_PUBLICO_INVALIDO', 'Consulta pública QR no válida.', 'ADVERTENCIA', [
                'result' => 'NO_VALIDO',
            ]);
        } else {
            $this->qr->recordAccess((int) ($asset['_qr_id'] ?? 0));
            // La consulta publica solo expone el payload autorizado, no todos los campos internos.
            $this->audit->create(null, 'QR', 'ACCESO_PUBLICO', "Consulta pública QR del activo #{$asset['_inventario_id']}.", 'INFO', [
                'entity' => 'inventario',
                'entity_id' => (int) $asset['_inventario_id'],
                'after' => [
                    'codigo_activo' => $asset['codigo_activo'] ?? null,
                    'publico_limitado' => true,
                ],
            ]);
        }

        $this->render('inventory/qr_lookup', [
            'title' => $asset ? 'Consulta QR de activo' : 'QR no válido',
            'asset' => $asset,
        ]);
    }

    public function qrRegenerate(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $this->authorize('inventory.manage');
            $this->csrf();

            $item = $this->inventory->find($id);
            if (!$item) {
                throw new \RuntimeException('Activo no encontrado.');
            }

            $reason = Sanitizer::text($_POST['motivo_qr'] ?? 'Regeneración manual de QR', 255);
            $qr = $this->qr->regenerateForInventory($id, Auth::id(), $reason);
            if (!$qr) {
                throw new \RuntimeException('No se pudo regenerar el QR.');
            }

            $this->audit->create(Auth::id(), 'QR', 'REGENERAR', "QR del activo #{$id} regenerado.", 'INFO', [
                'entity' => 'inventario',
                'entity_id' => $id,
                'reason' => $reason,
                'after' => [
                    'qr_id' => $qr['id'] ?? null,
                    'token_hash' => $qr['token_hash'] ?? null,
                ],
            ]);
            flash('success', 'QR regenerado. Los QR anteriores quedaron revocados.');
            $this->redirect('inventory/detail?id=' . $id);
        } catch (\Throwable $exception) {
            $this->formError($exception, 'inventory/detail?id=' . $id);
        }
    }

    public function qrGenerate(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $this->authorize('inventory.manage');
            $this->csrf();

            $item = $this->inventory->find($id);
            if (!$item) {
                throw new \RuntimeException('Activo no encontrado.');
            }

            $qr = $this->qr->ensureForInventoryByUser($id, Auth::id());
            if (!$qr) {
                throw new \RuntimeException('No se pudo generar el QR.');
            }

            $this->audit->create(Auth::id(), 'QR', 'GENERAR', "QR del activo #{$id} generado.", 'INFO', [
                'entity' => 'inventario',
                'entity_id' => $id,
                'after' => [
                    'qr_id' => $qr['id'] ?? null,
                    'token_hash' => $qr['token_hash'] ?? null,
                ],
            ]);
            flash('success', 'QR generado correctamente.');
            $this->redirect('inventory/detail?id=' . $id);
        } catch (\Throwable $exception) {
            $this->formError($exception, 'inventory/detail?id=' . $id);
        }
    }

    public function qrRevoke(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $this->authorize('inventory.manage');
            $this->csrf();

            $item = $this->inventory->find($id);
            if (!$item) {
                throw new \RuntimeException('Activo no encontrado.');
            }

            $reason = Sanitizer::text($_POST['motivo_qr'] ?? 'Revocación manual de QR', 255);
            if (!$this->qr->revokeForInventory($id, Auth::id(), $reason)) {
                throw new \RuntimeException('No hay un QR activo para revocar.');
            }

            $this->audit->create(Auth::id(), 'QR', 'REVOCAR', "QR del activo #{$id} revocado.", 'ADVERTENCIA', [
                'entity' => 'inventario',
                'entity_id' => $id,
                'reason' => $reason,
            ]);
            flash('success', 'QR revocado correctamente.');
            $this->redirect('inventory/detail?id=' . $id);
        } catch (\Throwable $exception) {
            $this->formError($exception, 'inventory/detail?id=' . $id);
        }
    }

    public function status(): void
    {
        try {
            $this->authorize('inventory.manage');
            $this->csrf();
            $id = (int) ($_POST['id'] ?? 0);
            $before = $this->inventory->find($id);
            if (!$before) {
                throw new \RuntimeException('Activo no encontrado.');
            }

            $status = $_POST['estado'] ?? '';
            // Estados permitidos para cambios manuales; ASIGNADO lo controla el flujo de asignaciones.
            $allowed = InventoryStatus::manualTransitions((string) $before['estado']);

            if (!in_array($status, $allowed, true)) {
                throw new \RuntimeException('Estado de inventario inválido.');
            }

            $meta = [
                'motivo' => 'Cambio manual de estado',
                'observacion' => Sanitizer::text($_POST['observacion_estado'] ?? '', 1000),
                'origen' => 'manual',
                'entidad_origen' => 'inventario',
                'entidad_id' => $id,
            ];

            $auditId = $this->audit->create(Auth::id(), 'INVENTARIO', 'CAMBIAR_ESTADO', "Activo #{$id} cambiado a {$status}.", 'INFO', [
                'entity' => 'inventario',
                'entity_id' => $id,
                'reason' => $meta['motivo'],
                'before' => [
                    'estado' => $before['estado'] ?? null,
                    'activo' => $before,
                ],
                'after' => [
                    'estado' => $status,
                    'meta' => $meta,
                ],
            ]);
            $this->inventory->setStatus($id, $status, $meta, Auth::id(), $auditId);
            flash('success', 'Estado del activo actualizado.');
            $this->redirect('inventory/detail?id=' . $id);
        } catch (\Throwable $exception) {
            $this->formError($exception, 'inventory');
        }
    }

    public function assignLicense(): void
    {
        $id = (int) ($_POST['inventario_id'] ?? 0);

        try {
            $this->authorize('inventory.manage');
            $this->csrf();

            // LicenseAssignment valida cupos, vencimiento y estado antes de registrar la asignacion.
            $this->licenseAssignments->assign(
                $id,
                Validator::integerRange((int) ($_POST['colaborador_id'] ?? 0), 1, PHP_INT_MAX, 'Colaborador'),
                Validator::integerRange((int) ($_POST['cantidad'] ?? 1), 1, 100000, 'Cantidad'),
                Validator::required(Sanitizer::text($_POST['fecha_asignacion'] ?? '', 10), 'Fecha de asignación'),
                Sanitizer::text($_POST['observaciones'] ?? '', 500),
                Auth::id()
            );

            $this->audit->create(Auth::id(), 'LICENCIAS', 'ASIGNAR_CUPO', "Cupo de licencia asignado desde activo #{$id}.", 'INFO', [
                'entity' => 'inventario',
                'entity_id' => $id,
                'after' => [
                    'colaborador_id' => (int) ($_POST['colaborador_id'] ?? 0),
                    'cantidad' => (int) ($_POST['cantidad'] ?? 1),
                    'fecha_asignacion' => $_POST['fecha_asignacion'] ?? '',
                ],
            ]);
            flash('success', 'Cupo de licencia asignado correctamente.');
            $this->redirect('inventory/detail?id=' . $id);
        } catch (\Throwable $exception) {
            $this->formError($exception, 'inventory/detail?id=' . $id);
        }
    }

    public function releaseLicense(): void
    {
        $inventoryId = (int) ($_POST['inventario_id'] ?? 0);

        try {
            $this->authorize('inventory.manage');
            $this->csrf();
            $licenseAssignmentId = (int) ($_POST['id'] ?? 0);
            $this->licenseAssignments->release($licenseAssignmentId, $inventoryId);
            $this->audit->create(Auth::id(), 'LICENCIAS', 'LIBERAR_CUPO', "Cupo de licencia #{$licenseAssignmentId} liberado.", 'INFO', [
                'entity' => 'licencia_asignaciones',
                'entity_id' => $licenseAssignmentId,
                'after' => ['estado' => 'LIBERADA', 'inventario_id' => $inventoryId],
            ]);
            flash('success', 'Cupo de licencia liberado.');
            $this->redirect('inventory/detail?id=' . $inventoryId);
        } catch (\Throwable $exception) {
            $this->formError($exception, 'inventory/detail?id=' . $inventoryId);
        }
    }

    public function revealLicense(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            // Accion sensible: requiere permiso especifico y token CSRF antes de mostrar la clave.
            $this->authorize('inventory.reveal_license');
            $this->csrf();

            $item = $this->inventory->find($id);
            if (!$item || !(int) $item['es_licencia']) {
                throw new \RuntimeException('La clave de licencia no está disponible.');
            }

            $plainText = ServiceContainer::licenseKeyProtector()->decrypt(
                $item['clave_licencia_cifrada'] ?? null,
                $item['clave_licencia'] ?? null
            );
            if ($plainText === null) {
                throw new \RuntimeException('La clave de licencia no puede descifrarse con la clave maestra actual.');
            }

            // La clave se revela solo durante la sesion actual y queda registrada en bitacora.
            $_SESSION['license_reveals'][$id] = true;
            $this->audit->create(Auth::id(), 'INVENTARIO', 'REVELAR_CLAVE_LICENCIA', "Clave de licencia del activo #{$id} revelada en pantalla.", 'INFO', [
                'entity' => 'inventario',
                'entity_id' => $id,
                'after' => ['clave_licencia' => '[REVELADA]', 'revelada_en_sesion' => true],
            ]);
            $this->redirect('inventory/detail?id=' . $id);
        } catch (\Throwable $exception) {
            $this->formError($exception, 'inventory/detail?id=' . $id);
        }
    }

    private function data(?array $current = null): array
    {
        $type = $_POST['tipo_activo'] ?? '';
        $status = $_POST['estado'] ?? '';

        if (!in_array($type, ['HARDWARE', 'SOFTWARE'], true)) {
            throw new \RuntimeException('Tipo de activo inválido.');
        }

        if ($current !== null) {
            $status = (string) ($current['estado'] ?? $status);
        } else {
            InventoryStatus::assertCanCreate($status);
        }

        $isLicense = isset($_POST['es_licencia']) ? 1 : 0;
        if ($isLicense && $type !== 'SOFTWARE') {
            throw new \RuntimeException('Las licencias únicamente se registran como software.');
        }

        $cost = Validator::positiveNumber(Sanitizer::decimal($_POST['costo'] ?? 0), 'Costo');
        $usefulLife = Validator::integerRange((int) ($_POST['vida_util_meses'] ?? 1), 1, 240, 'Vida útil');
        $licenseState = strtoupper(Sanitizer::text($_POST['estado_licencia'] ?? LicensePolicy::ACTIVA, 20));
        if ($isLicense && !in_array($licenseState, LicensePolicy::statuses(), true)) {
            throw new \RuntimeException('Estado de licencia inválido.');
        }
        $licenseKey = $this->prepareLicenseKeyData($isLicense, $current);

        $preserve = static fn (string $column): mixed => $current[$column] ?? null;

        return array_merge([
            'categoria_id' => (int) ($_POST['categoria_id'] ?? 0) ?: null,
            'codigo_activo' => Validator::required(Sanitizer::text($_POST['codigo_activo'] ?? '', 50), 'Código de activo'),
            'nombre' => Validator::required(Sanitizer::text($_POST['nombre'] ?? '', 150), 'Nombre'),
            'tipo_activo' => $type,
            'subcategoria' => Sanitizer::text($_POST['subcategoria'] ?? '', 100),
            'marca' => Sanitizer::text($_POST['marca'] ?? '', 100),
            'modelo' => Sanitizer::text($_POST['modelo'] ?? '', 100),
            'serie' => Validator::required(Sanitizer::text($_POST['serie'] ?? '', 100), 'Serie / clave de licencia'),
            'costo' => $cost,
            'fecha_ingreso' => Validator::date(Sanitizer::text($_POST['fecha_ingreso'] ?? '', 10), 'Fecha de ingreso'),
            'vida_util_meses' => $usefulLife,
            'estado' => $status,
            'es_licencia' => $isLicense,
            'clave_licencia' => $licenseKey['legacy'],
            'proveedor_licencia' => $isLicense ? Sanitizer::text($_POST['proveedor_licencia'] ?? '', 160) : null,
            'tipo_licencia' => $isLicense ? Sanitizer::text($_POST['tipo_licencia'] ?? '', 80) : null,
            'fecha_adquisicion_licencia' => $isLicense ? Validator::optionalDate(Sanitizer::text($_POST['fecha_adquisicion_licencia'] ?? '', 10), 'Fecha de adquisición de licencia') : null,
            'url_licencia' => $isLicense ? Validator::optionalUrl(Sanitizer::text($_POST['url_licencia'] ?? '', 255), 'URL de licencia') : null,
            'fecha_vencimiento_licencia' => $isLicense ? Validator::optionalDate(Sanitizer::text($_POST['fecha_vencimiento_licencia'] ?? '', 10), 'Fecha de vencimiento de licencia') : null,
            'observaciones_licencia' => $isLicense ? Sanitizer::text($_POST['observaciones_licencia'] ?? '', 1000) : null,
            'estado_licencia' => $isLicense ? $licenseState : null,
            'clave_licencia_cifrada' => $licenseKey['ciphertext'],
            'clave_licencia_hash' => $licenseKey['hash'],
            'clave_licencia_algoritmo' => $licenseKey['algorithm'],
            'clave_licencia_migrada_at' => $licenseKey['migrated_at'],
            'cantidad' => Validator::integerRange((int) ($_POST['cantidad'] ?? 1), 1, 100000, 'Cantidad'),
            'responsable_donacion' => $preserve('responsable_donacion'),
            'fecha_donacion' => $preserve('fecha_donacion'),
            'beneficiario_donacion' => $preserve('beneficiario_donacion'),
            'evidencia_donacion' => $preserve('evidencia_donacion'),
            'observacion_donacion' => $preserve('observacion_donacion'),
            'observacion_tecnica_descarte' => $preserve('observacion_tecnica_descarte'),
            'evaluador_descarte_id' => $preserve('evaluador_descarte_id'),
            'fecha_evaluacion_descarte' => $preserve('fecha_evaluacion_descarte'),
            'evidencia_descarte' => $preserve('evidencia_descarte'),
            'notas' => Sanitizer::text($_POST['notas'] ?? '', 1000),
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ], []);
    }

    private function prepareLicenseKeyData(int $isLicense, ?array $current): array
    {
        $empty = [
            'legacy' => null,
            'ciphertext' => null,
            'hash' => null,
            'algorithm' => null,
            'migrated_at' => null,
        ];

        if (!$isLicense) {
            return $empty;
        }

        $postedKey = trim(Sanitizer::text($_POST['clave_licencia'] ?? '', 255));
        if ($postedKey === '' && $current !== null) {
            return [
                'legacy' => $current['clave_licencia'] ?? null,
                'ciphertext' => $current['clave_licencia_cifrada'] ?? null,
                'hash' => $current['clave_licencia_hash'] ?? null,
                'algorithm' => $current['clave_licencia_algoritmo'] ?? null,
                'migrated_at' => $current['clave_licencia_migrada_at'] ?? null,
            ];
        }

        if ($postedKey === '') {
            return $empty;
        }

        if (!ServiceContainer::licenseKeyProtector()->isConfigured()) {
            throw new \RuntimeException('Para guardar la clave de licencia configure la clave maestra local o deje el campo "Clave de licencia" vacío. Los demás datos de la licencia sí pueden guardarse.');
        }

        $protected = ServiceContainer::licenseKeyProtector()->encryptForStorage($postedKey);
        if ($this->inventory->supportsEncryptedLicenseKeys()) {
            return [
                'legacy' => null,
                'ciphertext' => $protected['ciphertext'],
                'hash' => $protected['hash'],
                'algorithm' => $protected['algorithm'],
                'migrated_at' => date('Y-m-d H:i:s'),
            ];
        }

        if (strlen((string) $protected['ciphertext']) > 255) {
            throw new \RuntimeException('Aplique la migración de licencias cifradas antes de guardar esta clave.');
        }

        return [
            'legacy' => $protected['ciphertext'],
            'ciphertext' => null,
            'hash' => null,
            'algorithm' => $protected['algorithm'],
            'migrated_at' => null,
        ];
    }

    private function absoluteUrl(string $path): string
    {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $scheme . '://' . $host . url($path);
    }

    private function qrPayload(int $id): string
    {
        $qr = $this->qr->activeForInventory($id);
        if (!$qr || empty($qr['token'])) {
            throw new \RuntimeException('El QR seguro no está disponible para este activo.');
        }

        $path = 'qr?t=' . $qr['token'];

        $absolute = $this->absoluteUrl($path);

        return strlen($absolute) <= 106 ? $absolute : url($path);
    }

    private function rememberUploadedImages(array &$paths, ?array $image): void
    {
        if (!$image) {
            return;
        }

        foreach (['path', 'thumbnail'] as $key) {
            if (!empty($image[$key])) {
                $paths[] = (string) $image[$key];
            }
        }
    }

    private function cleanupUploadedImages(array $paths): void
    {
        $publicPath = realpath(dirname(__DIR__, 2) . '/public');
        if ($publicPath === false) {
            return;
        }

        foreach (array_unique($paths) as $path) {
            $relative = ltrim((string) $path, '/');
            if (!str_starts_with($relative, 'uploads/')) {
                continue;
            }

            $absolute = dirname(__DIR__, 2) . '/public/' . $relative;
            $real = realpath($absolute);
            if ($real !== false && str_starts_with($real, $publicPath . DIRECTORY_SEPARATOR) && is_file($real)) {
                @unlink($real);
            }
        }
    }
}

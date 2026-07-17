<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Core\Authorization;
use App\Core\QrLifecyclePolicy;
use App\Core\QrPublicPayload;
use App\Core\QrToken;

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$throws = static function (callable $callback): bool {
    try {
        $callback();
        return false;
    } catch (\RuntimeException) {
        return true;
    }
};

$token = QrToken::generate();
$assert(QrToken::isValid($token), 'Token válido debe tener formato seguro.');
$assert(QrToken::hash($token) !== $token, 'La base debe poder usar hash, no solo token plano.');

$payloadHash = QrToken::payloadHash(10, $token);
$assert(QrToken::verifyPayloadHash(10, $token, $payloadHash), 'Token válido debe verificar payload.');
$assert(!QrToken::isValid('inventory/detail?id=10'), 'No debe aceptar URL por ID como token.');
$assert(!QrToken::verifyPayloadHash(10, str_repeat('a', 64), $payloadHash), 'Token incorrecto no debe verificar.');

$activeQr = ['inventario_id' => 10, 'activo' => 1, 'revoked_at' => null, 'estado' => 'ACTIVO'];
$revokedQr = ['inventario_id' => 10, 'activo' => 0, 'revoked_at' => '2026-07-13 10:00:00', 'estado' => 'REVOCADO'];
QrLifecyclePolicy::assertActive($activeQr);
$assert($throws(fn () => QrLifecyclePolicy::assertActive($revokedQr)), 'Token revocado debe bloquearse.');

$newToken = QrToken::generate();
$newPayloadHash = QrToken::payloadHash(10, $newToken);
$assert(!QrToken::verifyPayloadHash(10, $token, $newPayloadHash), 'Token anterior no debe verificar luego de regeneración.');
$assert(QrToken::verifyPayloadHash(10, $newToken, $newPayloadHash), 'Token regenerado debe verificar.');
$assert(!QrToken::verifyPayloadHash(11, $newToken, $newPayloadHash), 'Token de otro activo no debe verificar.');
$assert($throws(fn () => QrLifecyclePolicy::assertBelongsToInventory(['inventario_id' => 11], 10)), 'Debe bloquear token de otro activo.');

$asset = [
    'codigo_activo' => 'ACT-010',
    'nombre' => 'Laptop de prueba',
    'categoria_nombre' => 'Equipo de Cómputo',
    'marca' => 'Lenovo',
    'estado' => 'DISPONIBLE',
    'costo' => 1200,
    'fecha_ingreso' => '2026-01-15',
    'colaborador_nombre' => 'Persona Privada',
    'clave_licencia' => 'SECRETO',
    'token' => $token,
    'bitacora' => 'auditoria',
];
$publicPayload = QrPublicPayload::fromAsset($asset);
$assert(QrPublicPayload::hasOnlyPublicFields($publicPayload), 'Payload público debe limitar campos.');
$assert((float) $publicPayload['costo'] === 1200.0, 'Payload público debe incluir precio.');
$assert($publicPayload['fecha_ingreso'] === '2026-01-15', 'Payload público debe incluir fecha de adquisición.');
$assert(!array_key_exists('colaborador_nombre', $publicPayload), 'Payload público no debe incluir colaborador.');
$assert(!array_key_exists('clave_licencia', $publicPayload), 'Payload público no debe incluir claves.');
$assert(!array_key_exists('token', $publicPayload), 'Payload público no debe incluir token.');

$qrView = (string) file_get_contents(dirname(__DIR__) . '/app/Views/inventory/qr_lookup.php');
$assert(str_contains($qrView, '<strong>Precio</strong>'), 'Vista QR debe mostrar etiqueta Precio.');
$assert(str_contains($qrView, '<strong>Fecha de adquisición</strong>'), 'Vista QR debe mostrar fecha de adquisición.');

$assert(Authorization::can('ADMIN', 'inventory.view'), 'Rol interno autorizado debe ver detalle privado autenticado.');
$assert(!Authorization::can('COLABORADOR', 'inventory.view'), 'Colaborador no debe ver detalle privado de inventario.');
$assert(!Authorization::can(null, 'inventory.view'), 'Visitante público no debe ver detalle privado.');

echo "OK Phase5BQrSecurityTest\n";

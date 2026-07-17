<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Core\Security\AuditTrailService;
use App\Core\ServiceContainer;

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

ServiceContainer::reset();
$trail = ServiceContainer::auditTrail();

$makeRow = static function (int $id, ?string $previousHash, array $after = [], ?int $signatureId = null) use ($trail): array {
    $event = [
        'payload_version' => 1,
        'usuario_id' => 1,
        'modulo' => 'PRUEBA',
        'accion' => 'EVENTO',
        'entidad' => 'tests',
        'entidad_id' => $id,
        'created_at' => '2026-07-13 10:00:0' . $id,
        'ip' => 'CLI',
        'user_agent' => 'Phase3AuditTrailTest',
        'resultado' => 'OK',
        'motivo' => null,
        'descripcion' => 'Evento de prueba',
        'datos_anteriores' => [],
        'datos_posteriores' => $trail->sanitize($after),
        'firma_id' => $signatureId,
        'fingerprint' => $signatureId ? str_repeat('a', 64) : null,
        'correlation_id' => str_pad((string) $id, 32, '0', STR_PAD_LEFT),
    ];
    $hash = $trail->hash($previousHash, $trail->payload($event));

    return [
        'id' => $id,
        'usuario_id' => $event['usuario_id'],
        'modulo' => $event['modulo'],
        'accion' => $event['accion'],
        'entidad' => $event['entidad'],
        'entidad_id' => $event['entidad_id'],
        'created_at' => $event['created_at'],
        'ip' => $event['ip'],
        'user_agent' => $event['user_agent'],
        'resultado' => $event['resultado'],
        'motivo' => $event['motivo'],
        'descripcion' => $event['descripcion'],
        'datos_anteriores_json' => '{}',
        'datos_posteriores_json' => json_encode($event['datos_posteriores'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'firma_id' => $event['firma_id'],
        'fingerprint' => $event['fingerprint'],
        'correlation_id' => $event['correlation_id'],
        'payload_version' => 1,
        'previous_hash' => $previousHash,
        'record_hash' => $hash,
    ];
};

$row1 = $makeRow(1, null, ['estado' => 'CREADO']);
$row2 = $makeRow(2, $row1['record_hash'], ['estado' => 'ACTUALIZADO']);
$valid = $trail->verifyRows([$row1, $row2]);
$assert($valid[0]['status'] === AuditTrailService::STATUS_VALID, 'El primer evento de una cadena válida debe verificar.');
$assert($valid[1]['status'] === AuditTrailService::STATUS_VALID, 'El segundo evento de una cadena válida debe verificar.');

$alteredPayload = $row2;
$alteredPayload['datos_posteriores_json'] = '{"estado":"ALTERADO"}';
$altered = $trail->verifyRows([$row1, $alteredPayload]);
$assert($altered[1]['status'] === AuditTrailService::STATUS_PAYLOAD_ALTERED, 'Debe detectar payload alterado.');

$brokenPrevious = $row2;
$brokenPrevious['previous_hash'] = str_repeat('b', 64);
$broken = $trail->verifyRows([$row1, $brokenPrevious]);
$assert($broken[1]['status'] === AuditTrailService::STATUS_CHAIN_BROKEN, 'Debe detectar previous_hash alterado o cadena rota.');

$signedRow = $makeRow(3, null, ['estado' => 'FIRMADO'], 99);
$signatureInvalid = $trail->verifyRows([$signedRow], static fn (): string => 'INVALIDA');
$assert($signatureInvalid[0]['status'] === AuditTrailService::STATUS_SIGNATURE_INVALID, 'Debe detectar firma inválida.');

$notVerifiable = $trail->verifyRows([$signedRow], static fn (): ?string => null);
$assert($notVerifiable[0]['status'] === AuditTrailService::STATUS_NOT_VERIFIABLE, 'Debe detectar evento no verificable.');

$sensitive = $trail->sanitize([
    'password' => 'Admin123*',
    'token' => 'abc',
    'private_key' => '-----BEGIN PRIVATE KEY-----',
    'clave_licencia' => 'AAAA-BBBB-CCCC-DDDD',
    'normal' => 'visible',
    'nested' => ['password_actual' => 'Secreta123*'],
]);
$assert($sensitive['password'] === '[REDACTED]', 'Debe excluir contraseñas.');
$assert($sensitive['token'] === '[REDACTED]', 'Debe excluir tokens.');
$assert($sensitive['private_key'] === '[REDACTED]', 'Debe excluir llaves privadas.');
$assert(str_starts_with($sensitive['clave_licencia'], '[MASKED]'), 'Debe enmascarar claves de licencia completas.');
$assert($sensitive['normal'] === 'visible', 'Debe conservar campos no sensibles.');
$assert($sensitive['nested']['password_actual'] === '[REDACTED]', 'Debe sanear datos sensibles recursivos.');

$concurrentA = $makeRow(4, null, ['lote' => 'A']);
$concurrentB = $makeRow(5, null, ['lote' => 'B']);
$concurrent = $trail->verifyRows([$concurrentA, $concurrentB]);
$assert($concurrent[1]['status'] === AuditTrailService::STATUS_CHAIN_BROKEN, 'Debe detectar escritura concurrente simulada con el mismo previous_hash.');

echo "OK Phase3AuditTrailTest\n";

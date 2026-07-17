<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Core\Config;
use App\Core\Security\SignatureVerificationService;
use App\Core\ServiceContainer;

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$removeDirectory = static function (string $directory) use (&$removeDirectory): void {
    if (!is_dir($directory)) {
        return;
    }

    foreach (scandir($directory) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $path = $directory . DIRECTORY_SEPARATOR . $entry;
        if (is_dir($path)) {
            $removeDirectory($path);
        } else {
            @unlink($path);
        }
    }

    @rmdir($directory);
};

$tempStore = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cmdb_phase2b_keys_' . bin2hex(random_bytes(6));

Config::load([
    'security' => [
        'integrity_key' => bin2hex(random_bytes(32)),
        'integrity_legacy_key' => null,
        'key_store_path' => $tempStore,
        'key_encryption_key' => bin2hex(random_bytes(32)),
    ],
]);
ServiceContainer::reset();

$keyManagement = ServiceContainer::keyManagement();
$factory = ServiceContainer::signedPayloadFactory();
$verification = ServiceContainer::signatureVerification();

$keyA = $keyManagement->generateForUser(11, 'Llave A Fase 2B', 2048);
$keyB = $keyManagement->generateForUser(11, 'Llave B Fase 2B', 2048);
$assert(is_array($keyA) && is_array($keyB), 'Debe generar llaves RSA de prueba.');

$dataOne = ['z' => 3, 'a' => ['dos' => 2, 'uno' => 1]];
$dataTwo = ['a' => ['uno' => 1, 'dos' => 2], 'z' => 3];
$payloadOne = $factory->build(11, 'APROBAR', 'inventario', 99, $dataOne, 123, (string) $keyA['fingerprint'], 'corr-test', '2026-07-13T10:00:00+00:00');
$payloadTwo = $factory->build(11, 'APROBAR', 'inventario', 99, $dataTwo, 123, (string) $keyA['fingerprint'], 'corr-test', '2026-07-13T10:00:00+00:00');
$assert($factory->hash($payloadOne) === $factory->hash($payloadTwo), 'La serialización canónica debe ser indiferente al orden de claves.');

$privateA = $keyManagement->loadPrivateKey((string) $keyA['key_store_reference']);
$privateB = $keyManagement->loadPrivateKey((string) $keyB['key_store_reference']);
$assert(is_string($privateA) && is_string($privateB), 'Debe recuperar llaves privadas cifradas desde el almacén temporal.');

$payloadHash = $factory->hash($payloadOne);
$signatureA = ServiceContainer::digitalSignature()->sign($payloadHash, $privateA);
$assert(is_string($signatureA) && $signatureA !== '', 'Debe firmar el hash canónico con RSA.');

$assert(
    $verification->verify($payloadHash, $signatureA, (string) $keyA['public_key'], 'ACTIVA') === SignatureVerificationService::VALID,
    'La firma válida con llave activa debe verificar como VALIDA.'
);

$assert(
    $verification->verify(hash('sha256', $payloadHash . 'alterado'), $signatureA, (string) $keyA['public_key'], 'ACTIVA') === SignatureVerificationService::INVALID,
    'Un payload alterado debe verificar como INVALIDA.'
);

$assert(
    $verification->verify($payloadHash, $signatureA, (string) $keyB['public_key'], 'ACTIVA') === SignatureVerificationService::INVALID,
    'Una llave pública equivocada debe rechazar la firma.'
);

$assert(
    $verification->verify($payloadHash, $signatureA, (string) $keyA['public_key'], 'REVOCADA') === SignatureVerificationService::REVOKED_KEY,
    'Una firma criptográficamente válida con llave revocada debe marcar LLAVE_REVOCADA.'
);

$signatureOld = ServiceContainer::digitalSignature()->sign($payloadHash, $privateA);
$payloadNew = $factory->build(11, 'ROTACION', 'usuarios', 11, ['estado' => 'nuevo'], 124, (string) $keyB['fingerprint'], 'corr-rot', '2026-07-13T11:00:00+00:00');
$signatureNew = ServiceContainer::digitalSignature()->sign($factory->hash($payloadNew), $privateB);
$assert(
    $verification->verify($payloadHash, (string) $signatureOld, (string) $keyA['public_key'], 'REEMPLAZADA') === SignatureVerificationService::VALID,
    'Una firma histórica con llave reemplazada debe seguir verificando.'
);
$assert(
    $verification->verify($factory->hash($payloadNew), (string) $signatureNew, (string) $keyB['public_key'], 'ACTIVA') === SignatureVerificationService::VALID,
    'La nueva llave rotada debe firmar verificaciones futuras.'
);

$assert(
    $verification->verify($payloadHash, 'firma-no-base64', (string) $keyA['public_key'], 'ACTIVA') === SignatureVerificationService::NOT_VERIFIABLE,
    'Una firma mal formada debe quedar como NO_VERIFICABLE.'
);

Config::load([
    'security' => [
        'key_store_path' => null,
        'key_encryption_key' => null,
    ],
]);
ServiceContainer::reset();
$assert(ServiceContainer::keyManagement()->generateForUser(22, 'Sin almacén') === null, 'Sin almacén configurado no debe generar llaves.');

$removeDirectory($tempStore);

echo "OK Phase2BSignatureTest\n";

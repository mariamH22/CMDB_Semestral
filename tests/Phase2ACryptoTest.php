<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Core\Config;
use App\Core\ServiceContainer;
use App\Core\Security\FileKeyStore;

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

$tempStore = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cmdb_phase2a_keys_' . bin2hex(random_bytes(6));

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
$assert($keyManagement->isConfigured(), 'El gestor de llaves debe estar configurado con directorio temporal y clave de cifrado.');

$key = $keyManagement->generateForUser(7, 'Llave de prueba Fase 2A', 2048);
$assert(is_array($key), 'Debe generar llave RSA por usuario.');
$assert((int) $key['usuario_id'] === 7, 'La llave generada debe quedar asociada al usuario solicitado.');
$assert((string) $key['estado'] === 'ACTIVA', 'La llave generada debe quedar ACTIVA.');
$assert((string) $key['algoritmo'] === 'RSA-SHA256', 'La llave debe declarar RSA-SHA256.');
$assert((int) $key['bits'] >= 2048, 'La llave debe ser de 2048 bits o superior.');
$assert((bool) preg_match('/\A[a-f0-9]{64}\z/', (string) $key['fingerprint']), 'El fingerprint debe ser SHA-256 hexadecimal.');
$assert(str_contains((string) $key['public_key'], 'BEGIN PUBLIC KEY'), 'Debe devolver llave publica PEM.');

$storedPath = $tempStore . DIRECTORY_SEPARATOR . $key['key_store_reference'];
$assert(is_file($storedPath), 'La llave privada cifrada debe escribirse en el almacen temporal.');
$storedContent = (string) file_get_contents($storedPath);
$assert(!str_contains($storedContent, 'BEGIN PRIVATE KEY'), 'El almacen no debe guardar la llave privada en texto plano.');
$assert(str_contains($storedContent, 'aes-256-gcm'), 'El almacen debe guardar un sobre cifrado AES-256-GCM.');

$privateKey = $keyManagement->loadPrivateKey((string) $key['key_store_reference']);
$assert($privateKey !== null && str_contains($privateKey, 'PRIVATE KEY'), 'Debe recuperar y descifrar la llave privada.');

$payload = hash('sha256', 'fase-2a-payload');
$signature = ServiceContainer::digitalSignature()->sign($payload, $privateKey);
$assert(is_string($signature) && $signature !== '', 'Debe firmar payload con RSA.');
$assert(ServiceContainer::signatureVerifier()->verify($payload, $signature, (string) $key['public_key']) === true, 'Debe verificar la firma RSA.');
$assert(ServiceContainer::signatureVerifier()->verify($payload . 'x', $signature, (string) $key['public_key']) === false, 'Debe rechazar payload alterado.');

Config::load([
    'security' => [
        'key_store_path' => null,
        'key_encryption_key' => null,
    ],
]);
ServiceContainer::reset();
$assert(!ServiceContainer::keyManagement()->isConfigured(), 'Sin almacen ni clave no debe configurarse el gestor.');
$assert(ServiceContainer::keyManagement()->generateForUser(7) === null, 'Sin configuracion no debe generar llaves desprotegidas.');

$publicStore = new FileKeyStore(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'rsa-test');
$assert(!$publicStore->isConfigured(), 'No debe permitir un almacen de llaves dentro de public/.');

$repoStore = new FileKeyStore(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'security' . DIRECTORY_SEPARATOR . 'rsa-test');
$assert(!$repoStore->isConfigured(), 'No debe permitir un almacen de llaves dentro del proyecto versionado.');

$hasher = ServiceContainer::passwordHasher();
$hash = $hasher->hash('Admin123*');
$assert($hash !== 'Admin123*', 'La contrasena no debe guardarse en texto plano.');
$assert($hasher->verify('Admin123*', $hash), 'El hash debe verificar la contrasena correcta.');
$assert(!$hasher->verify('Otra123*', $hash), 'El hash debe rechazar una contrasena incorrecta.');

$removeDirectory($tempStore);

echo "OK Phase2ACryptoTest\n";

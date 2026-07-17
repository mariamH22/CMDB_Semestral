<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;
use App\Core\ServiceContainer;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
require_once dirname(__DIR__, 2) . '/app/Core/helpers.php';

Config::load(require dirname(__DIR__, 2) . '/app/Config/config.php');

$apply = in_array('--apply', $argv, true);
$protector = ServiceContainer::licenseKeyProtector();
if (!$protector->isConfigured()) {
    fwrite(STDERR, "ERROR: configure CMDB_LICENSE_KEY_ENCRYPTION_KEY o security.license_key_encryption_key antes de migrar.\n");
    exit(1);
}

$db = Database::instance();
foreach (['clave_licencia_cifrada', 'clave_licencia_hash', 'clave_licencia_algoritmo', 'clave_licencia_migrada_at'] as $column) {
    if (!$db->columnExists('inventario', $column)) {
        fwrite(STDERR, "ERROR: aplique primero la migracion 2026_07_13_0008_licencias_portal_cifrado.sql.\n");
        exit(1);
    }
}

$rows = $db->fetchAll(
    "SELECT id, codigo_activo, clave_licencia
     FROM inventario
     WHERE es_licencia = 1
       AND clave_licencia IS NOT NULL
       AND clave_licencia <> ''
       AND (clave_licencia_cifrada IS NULL OR clave_licencia_cifrada = '')
     ORDER BY id"
);

echo ($apply ? "APLICANDO" : "SIMULANDO") . " migracion de " . count($rows) . " clave(s) de licencia.\n";

foreach ($rows as $row) {
    $legacy = (string) $row['clave_licencia'];
    $plain = $protector->isEncryptedPayload($legacy) ? $protector->decrypt(null, $legacy) : $legacy;
    if ($plain === null || trim($plain) === '') {
        echo "OMITIDA {$row['codigo_activo']}: no se pudo leer la clave actual.\n";
        continue;
    }

    $protected = $protector->encryptForStorage($plain);
    echo "OK {$row['codigo_activo']}: preparada para cifrado con {$protected['algorithm']}.\n";

    if (!$apply) {
        continue;
    }

    $db->execute(
        "UPDATE inventario
         SET clave_licencia = NULL,
             clave_licencia_cifrada = :ciphertext,
             clave_licencia_hash = :hash,
             clave_licencia_algoritmo = :algorithm,
             clave_licencia_migrada_at = NOW()
         WHERE id = :id",
        [
            'id' => (int) $row['id'],
            'ciphertext' => $protected['ciphertext'],
            'hash' => $protected['hash'],
            'algorithm' => $protected['algorithm'],
        ]
    );
}

if (!$apply) {
    echo "No se modifico la base. Ejecute con --apply solo despues de hacer backup.\n";
}

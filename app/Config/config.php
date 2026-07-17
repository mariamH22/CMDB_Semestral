<?php
declare(strict_types=1);

$localConfigPath = __DIR__ . '/config.local.php';
$localConfig = [];
if (is_readable($localConfigPath)) {
$local = require $localConfigPath;
    if (is_array($local)) {
        $localConfig = $local;
    }
}

$localAppConfig = is_array($localConfig['app'] ?? null) ? $localConfig['app'] : [];
$localDbConfig = is_array($localConfig['db'] ?? null) ? $localConfig['db'] : [];
$localSecurityConfig = is_array($localConfig['security'] ?? null) ? $localConfig['security'] : [];

$envValue = static function (string $name): ?string {
    $value = getenv($name);
    if ($value === false || trim((string) $value) === '') {
        return null;
    }

    return (string) $value;
};

$localValue = static function (array $section, string $key): ?string {
    if (!array_key_exists($key, $section)) {
        return null;
    }

    $value = trim((string) $section[$key]);
    return $value === '' ? null : $value;
};

$localPassword = array_key_exists('password', $localDbConfig)
    ? (string) $localDbConfig['password']
    : null;
$envPassword = getenv('DB_PASSWORD');

$envIntegrityKey = getenv('CMDB_INTEGRITY_KEY') ?: null;
$legacyIntegrityKey = getenv('CMDB_LEGACY_HMAC_KEY') ?: null;
$envKeyStorePath = getenv('CMDB_KEY_STORE_PATH') ?: null;
$envKeyEncryptionKey = getenv('CMDB_KEY_ENCRYPTION_KEY') ?: null;
$envLicenseKeyEncryptionKey = getenv('CMDB_LICENSE_KEY_ENCRYPTION_KEY') ?: null;
$localIntegrityKey = $localSecurityConfig['integrity_key'] ?? null;
$localKeyStorePath = $localSecurityConfig['key_store_path'] ?? null;
$localKeyEncryptionKey = $localSecurityConfig['key_encryption_key'] ?? null;
$localLicenseKeyEncryptionKey = $localSecurityConfig['license_key_encryption_key'] ?? null;
$configuredIntegrityKey = $localIntegrityKey ?: $envIntegrityKey ?: $legacyIntegrityKey ?: null;
$configuredKeyStorePath = $localKeyStorePath ?: $envKeyStorePath ?: null;
$configuredKeyEncryptionKey = $localKeyEncryptionKey ?: $envKeyEncryptionKey ?: null;
$configuredLicenseKeyEncryptionKey = $localLicenseKeyEncryptionKey ?: $envLicenseKeyEncryptionKey ?: null;

$normalizeSecret = static function (?string $value): ?string {
    if ($value === null) {
        return null;
    }

    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }

    if (str_starts_with(strtolower($value), 'cambiar_')) {
        return null;
    }

    return $value;
};

/*
|--------------------------------------------------------------------------
| Configuración local
|--------------------------------------------------------------------------
| Cambia los valores de db si tu WampServer tiene otra contraseña o puerto.
| APP_BASE_URL es opcional. Si se deja vacío, el sistema detecta la carpeta
| automáticamente desde public/index.php.
*/
$autoBasePath = isset($_SERVER['SCRIPT_NAME'])
    ? rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/')
    : '/CMDB_Semestral/public';
if ($autoBasePath === '' || $autoBasePath === '.') {
    $autoBasePath = '/CMDB_Semestral/public';
}

return [
    'app' => [
        'name' => 'CMDB Integral',
        'version' => '1.0.0',
        'timezone' => $localValue($localAppConfig, 'timezone') ?: $envValue('APP_TIMEZONE') ?: 'America/Panama',
        'base_path' => $localValue($localAppConfig, 'base_path') ?: $envValue('APP_BASE_URL') ?: $autoBasePath,
        'environment' => $localValue($localAppConfig, 'environment') ?: $envValue('APP_ENV') ?: 'development',
    ],
    'db' => [
        'host' => $localValue($localDbConfig, 'host') ?: $envValue('DB_HOST') ?: 'localhost',
        'database' => $localValue($localDbConfig, 'database') ?: $envValue('DB_NAME') ?: 'cmdb_integral',
        'user' => $localValue($localDbConfig, 'user') ?: $envValue('DB_USER') ?: 'root',
        'password' => $localPassword ?? ($envPassword === false ? '' : (string) $envPassword),
        'charset' => $localValue($localDbConfig, 'charset') ?: $envValue('DB_CHARSET') ?: 'utf8mb4',
    ],
    'security' => [
        // Cambiar en producción. Se usa para las firmas HMAC de integridad de activos.
        'integrity_key' => $normalizeSecret($configuredIntegrityKey),
        'integrity_legacy_key' => $normalizeSecret($legacyIntegrityKey),
        'key_store_path' => $normalizeSecret($configuredKeyStorePath),
        'key_encryption_key' => $normalizeSecret($configuredKeyEncryptionKey),
        'license_key_encryption_key' => $normalizeSecret($configuredLicenseKeyEncryptionKey),
        'max_login_attempts' => 3,
        'login_rate_limit_attempts' => 8,
        'login_rate_limit_minutes' => 15,
        'password_min_length' => 8,
        'password_max_length' => 64,
        'reset_minutes' => 30,
        'show_reset_demo_link' => getenv('CMDB_SHOW_RESET_LINK') === '1',
    ],
];

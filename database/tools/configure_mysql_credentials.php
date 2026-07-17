<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

use App\Core\Config;

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
require_once dirname(__DIR__, 2) . '/app/Core/helpers.php';

$root = dirname(__DIR__, 2);
$configPath = $root . '/app/Config/config.local.php';

$prompt = static function (string $label, string $default = ''): string {
    $suffix = $default !== '' ? " [{$default}]" : '';
    echo "{$label}{$suffix}: ";
    $value = fgets(STDIN);
    $value = $value === false ? '' : trim($value);

    return $value === '' ? $default : $value;
};

$promptSecret = static function (string $label): string {
    echo "{$label}: ";
    $canHide = PHP_OS_FAMILY !== 'Windows' && function_exists('shell_exec');
    if ($canHide) {
        shell_exec('stty -echo 2>/dev/null');
    }

    $value = fgets(STDIN);

    if ($canHide) {
        shell_exec('stty echo 2>/dev/null');
    }
    echo PHP_EOL;

    return $value === false ? '' : rtrim($value, "\r\n");
};

$mask = static function (string $value): string {
    return $value === '' ? '(vacia)' : str_repeat('*', min(12, max(4, strlen($value))));
};

echo "Configuracion local de MySQL para CMDB Integral\n";
echo "La contrasena no se mostrara en pantalla ni se imprimira en la salida.\n\n";

$host = $prompt('Host MySQL', 'localhost');
$database = $prompt('Base de datos', 'cmdb_integral');
$user = $prompt('Usuario MySQL', 'laravel_user');
$password = $promptSecret('Contrasena MySQL');
$charset = $prompt('Charset', 'utf8mb4');

$dsn = "mysql:host={$host};dbname={$database};charset={$charset}";

try {
    $pdo = new PDO(
        $dsn,
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    $row = $pdo->query('SELECT DATABASE() AS db, USER() AS client_user, CURRENT_USER() AS authenticated_user')->fetch();
} catch (Throwable $exception) {
    fwrite(STDERR, "[ERROR] No se pudo conectar con esas credenciales: {$exception->getMessage()}\n");
    fwrite(STDERR, "[INFO] No se guardo {$configPath}.\n");
    exit(1);
}

$localConfig = [];
if (is_readable($configPath)) {
    $existing = require $configPath;
    if (is_array($existing)) {
        $localConfig = $existing;
    }
}

$localConfig['db'] = [
    'host' => $host,
    'database' => $database,
    'user' => $user,
    'password' => $password,
    'charset' => $charset,
];

$content = "<?php\nreturn " . var_export($localConfig, true) . ";\n";
if (file_put_contents($configPath, $content) === false) {
    fwrite(STDERR, "[ERROR] No se pudo escribir {$configPath}.\n");
    exit(1);
}
@chmod($configPath, 0600);

echo "[OK] Conexion PDO establecida con {$database}.\n";
echo "[OK] Usuario autenticado: " . ($row['authenticated_user'] ?? $user) . "\n";
echo "[OK] Archivo local creado: {$configPath}\n";
echo "[OK] Password guardado: " . $mask($password) . "\n\n";

Config::load(require $root . '/app/Config/config.php');

echo "Ejecutando verificador de entorno y esquema...\n";
$command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/database/tools/verify_environment.php');
passthru($command, $status);
exit((int) $status);

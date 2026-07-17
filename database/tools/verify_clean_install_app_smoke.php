<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;
use App\Core\InventoryStatus;
use App\Core\ModelFactory;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
require_once dirname(__DIR__, 2) . '/app/Core/helpers.php';

$root = dirname(__DIR__, 2);
$installFile = $argv[1] ?? 'database/install/cmdb_integral_full_install.sql';
$installPath = str_starts_with($installFile, '/')
    ? $installFile
    : $root . '/' . $installFile;

if (!is_readable($installPath)) {
    fwrite(STDERR, "[ERROR] No se puede leer {$installFile}.\n");
    exit(1);
}

$baseConfig = require $root . '/app/Config/config.php';
if (!is_array($baseConfig)) {
    fwrite(STDERR, "[ERROR] app/Config/config.php no devolvio una configuracion valida.\n");
    exit(1);
}
Config::load($baseConfig);

$sourceDatabase = (string) Config::get('db.database', 'cmdb_integral');
$host = (string) Config::get('db.host', 'localhost');
$user = (string) Config::get('db.user', 'root');
$password = (string) Config::get('db.password', '');
$charset = (string) Config::get('db.charset', 'utf8mb4');
$tempDatabase = 'cmdb_clean_smoke_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));

$quoteIdentifier = static function (string $identifier): string {
    return '`' . str_replace('`', '``', $identifier) . '`';
};

$quoteOptionValue = static function (string $value): string {
    return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
};

$run = static function (array $command, ?string $stdinFile = null): array {
    $descriptorSpec = [
        0 => $stdinFile ? ['file', $stdinFile, 'r'] : ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptorSpec, $pipes);
    if (!is_resource($process)) {
        return [1, '', 'No fue posible iniciar el proceso.'];
    }

    if (!$stdinFile) {
        fclose($pipes[0]);
    }

    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    return [proc_close($process), (string) $stdout, (string) $stderr];
};

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$countTable = static function (Database $db, string $table): int {
    $row = $db->fetch("SELECT COUNT(*) AS total FROM {$table}");

    return (int) ($row['total'] ?? 0);
};

$defaultsFile = tempnam(sys_get_temp_dir(), 'cmdb_mysql_');
$tempSqlFile = tempnam(sys_get_temp_dir(), 'cmdb_clean_install_');
if ($defaultsFile === false || $tempSqlFile === false) {
    fwrite(STDERR, "[ERROR] No se pudieron crear archivos temporales.\n");
    exit(1);
}

$cleanup = static function () use (&$pdo, $tempDatabase, $quoteIdentifier, $defaultsFile, $tempSqlFile): void {
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $pdo->exec('DROP DATABASE IF EXISTS ' . $quoteIdentifier($tempDatabase));
        } catch (Throwable) {
        }
    }
    @unlink($defaultsFile);
    @unlink($tempSqlFile);
};
register_shutdown_function($cleanup);

file_put_contents($defaultsFile, implode(PHP_EOL, [
    '[client]',
    'host=' . $quoteOptionValue($host),
    'user=' . $quoteOptionValue($user),
    'password=' . $quoteOptionValue($password),
    'default-character-set=' . $quoteOptionValue($charset),
    '',
]));
chmod($defaultsFile, 0600);

$pdo = new PDO(
    "mysql:host={$host};charset={$charset}",
    $user,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

$pdo->exec('DROP DATABASE IF EXISTS ' . $quoteIdentifier($tempDatabase));

$sql = (string) file_get_contents($installPath);
$sql = preg_replace('/CREATE\s+DATABASE(\s+IF\s+NOT\s+EXISTS)?\s+`?' . preg_quote($sourceDatabase, '/') . '`?/i', 'CREATE DATABASE IF NOT EXISTS ' . $quoteIdentifier($tempDatabase), $sql) ?? $sql;
$sql = preg_replace('/USE\s+`?' . preg_quote($sourceDatabase, '/') . '`?\s*;/i', 'USE ' . $quoteIdentifier($tempDatabase) . ';', $sql) ?? $sql;
file_put_contents($tempSqlFile, $sql);

echo "[INFO] Importando {$installFile} en base temporal {$tempDatabase}.\n";
[$status, $stdout, $stderr] = $run(['mysql', '--defaults-extra-file=' . $defaultsFile], $tempSqlFile);
if ($stdout !== '') {
    echo $stdout;
}
if ($status !== 0) {
    fwrite(STDERR, "[ERROR] El instalador no importa en una base limpia.\n{$stderr}\n");
    exit(1);
}
if (trim($stderr) !== '') {
    echo trim($stderr) . PHP_EOL;
}
echo "[OK] Instalador importado en MySQL real.\n";

$appConfig = $baseConfig;
$appConfig['db']['database'] = $tempDatabase;
Config::load($appConfig);

try {
    $models = ModelFactory::default();
    $db = $models->db();

    $categories = $models->categories()->all(true);
    $assert(count($categories) > 0, 'El instalador no dejo categorias activas para los formularios.');
    echo "[OK] Categorias activas consultadas por modelo: " . count($categories) . ".\n";

    $activeAssets = $models->inventory()->all();
    $allAssets = $models->inventory()->all(['include_inactive' => true]);
    $assert(count($activeAssets) > 0, 'El inventario operativo quedo vacio.');
    $assert(count($allAssets) >= count($activeAssets), 'El inventario historico no incluye el operativo.');
    echo "[OK] Inventario operativo por modelo: " . count($activeAssets) . " activo(s), " . count($allAssets) . " historico(s).\n";

    $assignments = $models->assignments()->all(true);
    $assert(count($assignments) > 0, 'No hay asignaciones activas semilla para validar el portal.');
    echo "[OK] Asignaciones activas consultadas por modelo: " . count($assignments) . ".\n";

    $dashboard = $models->reports()->dashboard([]);
    foreach (['assets', 'assignments', 'categories', 'available', 'donations', 'returns', 'stateHistory'] as $key) {
        $assert(array_key_exists($key, $dashboard), "El dashboard no devolvio la clave {$key}.");
    }
    echo "[OK] Dashboard de reportes responde con inventario, asignaciones, donaciones e historial.\n";

    $donations = $models->reports()->donations([]);
    $assert(count($donations) > 0, 'El reporte historico de donaciones quedo vacio.');
    foreach ($donations as $donation) {
        $assert((string) ($donation['estado'] ?? '') === InventoryStatus::DONADO, 'El reporte de donaciones incluyo un estado distinto a DONADO.');
        $assert((int) ($donation['activo'] ?? 1) === 0, 'Una donacion sigue marcada como activo operativo.');
    }
    echo "[OK] Donaciones historicas visibles fuera del inventario operativo: " . count($donations) . ".\n";

    $assert($models->assignments()->supportsFormalReturns(), 'El flujo formal de devoluciones no esta soportado por el esquema limpio.');
    echo "[OK] Esquema de devoluciones formales disponible.\n";

    $assert($models->licenseAssignments()->schemaReady(), 'El flujo de cupos de licencias no esta soportado por el esquema limpio.');
    echo "[OK] Esquema de asignaciones de licencias disponible.\n";

    $assert($models->inventoryQr()->schemaReady(), 'La tabla inventario_qr no existe en la instalacion limpia.');
    $qrAsset = null;
    foreach ($activeAssets as $asset) {
        if ((int) ($asset['id'] ?? 0) > 0 && (float) ($asset['costo'] ?? 0) > 0 && !empty($asset['fecha_ingreso'])) {
            $qrAsset = $asset;
            break;
        }
    }
    $assert($qrAsset !== null, 'No hay activo operativo con precio y fecha para validar QR publico.');
    $qr = $models->inventoryQr()->ensureForInventoryByUser((int) $qrAsset['id'], null);
    $assert(is_array($qr) && !empty($qr['token']), 'No fue posible crear o recuperar QR para un activo limpio.');
    $publicPayload = $models->inventoryQr()->findPublicAssetByToken((string) $qr['token'], (int) $qrAsset['id']);
    $assert(is_array($publicPayload), 'No fue posible consultar el QR publico creado.');
    $assert(array_key_exists('costo', $publicPayload), 'El QR publico no incluye precio/costo.');
    $assert(array_key_exists('fecha_ingreso', $publicPayload), 'El QR publico no incluye fecha de adquisicion.');
    $assert((float) $publicPayload['costo'] > 0, 'El QR publico devuelve precio/costo vacio.');
    $assert(!empty($publicPayload['fecha_ingreso']), 'El QR publico devuelve fecha de adquisicion vacia.');
    echo "[OK] QR publico consultado con precio y fecha de adquisicion.\n";

    foreach ([
        'necesidades' => 'necesidades',
        'necesidades_historial' => 'historial de solicitudes',
        'presupuestos' => 'presupuestos',
        'licencia_asignaciones' => 'licencias cifradas/asignaciones',
        'inventario_imagenes' => 'galeria de inventario',
    ] as $table => $label) {
        $assert($db->tableExists($table), "Falta tabla de {$label}: {$table}.");
    }

    $assert($countTable($db, 'necesidades') > 0, 'No hay solicitudes semilla para necesidades.');
    $assert($countTable($db, 'presupuestos') > 0, 'No hay presupuestos semilla.');
    echo "[OK] Tablas avanzadas presentes: necesidades, historial, presupuestos, licencias e imagenes.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "[ERROR] Smoke de aplicacion fallido: {$exception->getMessage()}\n");
    exit(1);
}

echo "[OK] {$installFile} crea una base limpia utilizable por los modelos reales de la aplicacion.\n";

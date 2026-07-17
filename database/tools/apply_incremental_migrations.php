<?php
declare(strict_types=1);

use App\Core\Config;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
require_once dirname(__DIR__, 2) . '/app/Core/helpers.php';

$root = dirname(__DIR__, 2);
Config::load(require $root . '/app/Config/config.php');

$database = (string) Config::get('db.database', 'cmdb_integral');
$host = (string) Config::get('db.host', 'localhost');
$user = (string) Config::get('db.user', 'root');
$password = (string) Config::get('db.password', '');
$charset = (string) Config::get('db.charset', 'utf8mb4');

$migrations = [
    'database/migrations/2026_07_13_0003_ubicacion_historial_solicitudes.sql',
    'database/migrations/2026_07_13_0004_contratos_llaves_cripto.sql',
    'database/migrations/2026_07_13_0005_firma_rsa_ciclo_vida.sql',
    'database/migrations/2026_07_13_0006_audit_trail_criptografico.sql',
    'database/migrations/2026_07_13_0007_ciclo_vida_activo_formal.sql',
    'database/migrations/2026_07_13_0008_licencias_portal_cifrado.sql',
    'database/migrations/2026_07_13_0009_qr_seguro_activo.sql',
    'database/migrations/2026_07_13_0010_solicitudes_historial_formal.sql',
    'database/migrations/2026_07_13_0011_presupuesto_anual_quinquenal.sql',
    'database/migrations/2026_07_13_0012_imagenes_ubicaciones.sql',
    'database/migrations/2026_07_13_0013_asignador_validaciones.sql',
    'database/migrations/2026_07_14_0002_devolucion_recepcion_independiente.sql',
    'database/migrations/2026_07_14_0003_donacion_retira_activo.sql',
];

$quoteOptionValue = static function (string $value): string {
    return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
};

$run = static function (array $command, ?string $stdinFile = null, ?string $stdoutFile = null): array {
    $descriptorSpec = [
        0 => $stdinFile ? ['file', $stdinFile, 'r'] : ['pipe', 'r'],
        1 => $stdoutFile ? ['file', $stdoutFile, 'w'] : ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptorSpec, $pipes);
    if (!is_resource($process)) {
        return [1, '', 'No fue posible iniciar el proceso.'];
    }

    if (!$stdinFile) {
        fclose($pipes[0]);
    }

    $stdout = $stdoutFile ? '' : stream_get_contents($pipes[1]);
    if (!$stdoutFile) {
        fclose($pipes[1]);
    }
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    return [proc_close($process), (string) $stdout, (string) $stderr];
};

$assertCommandAvailable = static function (string $binary) use ($run): void {
    [$status] = $run([$binary, '--version']);
    if ($status !== 0) {
        fwrite(STDERR, "[ERROR] No se encontro el comando {$binary}.\n");
        exit(1);
    }
};

$assertCommandAvailable('mysql');
$assertCommandAvailable('mysqldump');

$defaultsFile = tempnam(sys_get_temp_dir(), 'cmdb_mysql_');
if ($defaultsFile === false) {
    fwrite(STDERR, "[ERROR] No se pudo crear archivo temporal de credenciales.\n");
    exit(1);
}

$defaults = [
    '[client]',
    'host=' . $quoteOptionValue($host),
    'user=' . $quoteOptionValue($user),
    'password=' . $quoteOptionValue($password),
    'default-character-set=' . $quoteOptionValue($charset),
    '',
];
file_put_contents($defaultsFile, implode(PHP_EOL, $defaults));
chmod($defaultsFile, 0600);

$cleanup = static function () use ($defaultsFile): void {
    if (is_file($defaultsFile)) {
        @unlink($defaultsFile);
    }
};
register_shutdown_function($cleanup);

$backupCandidates = [
    $root . '/storage/backups',
    $root . '/database/backups',
    sys_get_temp_dir(),
];
$backupDir = null;
foreach ($backupCandidates as $candidate) {
    if (!is_dir($candidate)) {
        @mkdir($candidate, 0770, true);
    }
    if (is_dir($candidate) && is_writable($candidate)) {
        $backupDir = $candidate;
        break;
    }
}

if ($backupDir === null) {
    fwrite(STDERR, "[ERROR] No se encontro una carpeta escribible para el respaldo.\n");
    exit(1);
}

$backupFile = $backupDir . '/' . $database . '_' . date('Ymd_His') . '_pre_migrations.sql';
echo "[INFO] Creando respaldo: {$backupFile}\n";
[$dumpStatus, , $dumpError] = $run(
    [
        'mysqldump',
        '--defaults-extra-file=' . $defaultsFile,
        '--single-transaction',
        '--routines',
        '--triggers',
        '--events',
        $database,
    ],
    null,
    $backupFile
);

if ($dumpStatus !== 0) {
    fwrite(STDERR, "[ERROR] Fallo el respaldo con mysqldump.\n{$dumpError}\n");
    exit(1);
}
echo "[OK] Respaldo creado.\n";

foreach ($migrations as $relativePath) {
    $path = $root . '/' . $relativePath;
    if (!is_readable($path)) {
        fwrite(STDERR, "[ERROR] No se puede leer {$relativePath}.\n");
        exit(1);
    }

    echo "[INFO] Aplicando {$relativePath}\n";
    [$status, $stdout, $stderr] = $run(
        ['mysql', '--defaults-extra-file=' . $defaultsFile, '--database=' . $database],
        $path
    );

    if ($stdout !== '') {
        echo $stdout;
    }

    if ($status !== 0) {
        fwrite(STDERR, "[ERROR] Fallo {$relativePath}.\n{$stderr}\n");
        fwrite(STDERR, "[INFO] Respaldo disponible en {$backupFile}\n");
        exit(1);
    }

    if (trim($stderr) !== '') {
        echo trim($stderr) . PHP_EOL;
    }
    echo "[OK] {$relativePath}\n";
}

echo "[INFO] Ejecutando verificador final de entorno y esquema.\n";
[$verifyStatus, $verifyOutput, $verifyError] = $run([PHP_BINARY, $root . '/database/tools/verify_environment.php']);
echo $verifyOutput;
if ($verifyError !== '') {
    fwrite(STDERR, $verifyError);
}

if ($verifyStatus !== 0) {
    fwrite(STDERR, "[ERROR] El verificador final aun reporta problemas.\n");
    fwrite(STDERR, "[INFO] Respaldo disponible en {$backupFile}\n");
    exit($verifyStatus);
}

echo "[OK] Migraciones aplicadas y esquema real verificado.\n";

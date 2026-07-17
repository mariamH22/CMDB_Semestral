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
$installFile = $argv[1] ?? 'database/install/cmdb_integral_full_install.sql';
$installPath = str_starts_with($installFile, '/')
    ? $installFile
    : $root . '/' . $installFile;

if (!is_readable($installPath)) {
    fwrite(STDERR, "[ERROR] No se puede leer {$installFile}.\n");
    exit(1);
}

Config::load(require $root . '/app/Config/config.php');

$sourceDatabase = (string) Config::get('db.database', 'cmdb_integral');
$host = (string) Config::get('db.host', 'localhost');
$user = (string) Config::get('db.user', 'root');
$password = (string) Config::get('db.password', '');
$charset = (string) Config::get('db.charset', 'utf8mb4');
$tempDatabase = 'cmdb_install_verify_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));

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

$defaultsFile = tempnam(sys_get_temp_dir(), 'cmdb_mysql_');
$tempSqlFile = tempnam(sys_get_temp_dir(), 'cmdb_install_');
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

$fetchTables = static function (PDO $pdo, string $database): array {
    $statement = $pdo->prepare(
        "SELECT TABLE_NAME
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = :db AND TABLE_TYPE = 'BASE TABLE'
         ORDER BY TABLE_NAME"
    );
    $statement->execute(['db' => $database]);

    return array_map(static fn (array $row): string => (string) $row['TABLE_NAME'], $statement->fetchAll());
};

$fetchColumns = static function (PDO $pdo, string $database): array {
    $statement = $pdo->prepare(
        "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = :db
         ORDER BY TABLE_NAME, ORDINAL_POSITION"
    );
    $statement->execute(['db' => $database]);

    $columns = [];
    foreach ($statement->fetchAll() as $row) {
        $key = $row['TABLE_NAME'] . '.' . $row['COLUMN_NAME'];
        $columns[$key] = [
            'type' => (string) $row['COLUMN_TYPE'],
            'nullable' => (string) $row['IS_NULLABLE'],
            'default' => $row['COLUMN_DEFAULT'],
            'extra' => (string) $row['EXTRA'],
        ];
    }

    return $columns;
};

$fetchIndexes = static function (PDO $pdo, string $database): array {
    $statement = $pdo->prepare(
        "SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, SEQ_IN_INDEX, COLUMN_NAME
         FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = :db
         ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX"
    );
    $statement->execute(['db' => $database]);

    $indexes = [];
    foreach ($statement->fetchAll() as $row) {
        $key = $row['TABLE_NAME'] . '.' . $row['INDEX_NAME'];
        $indexes[$key]['unique'] = (int) $row['NON_UNIQUE'] === 0;
        $indexes[$key]['columns'][] = (string) $row['COLUMN_NAME'];
    }

    ksort($indexes);

    return $indexes;
};

$errors = [];
$sourceTables = $fetchTables($pdo, $sourceDatabase);
$tempTables = $fetchTables($pdo, $tempDatabase);

foreach (array_diff($sourceTables, $tempTables) as $table) {
    $errors[] = "Falta tabla en instalador: {$table}";
}

$sourceColumns = $fetchColumns($pdo, $sourceDatabase);
$tempColumns = $fetchColumns($pdo, $tempDatabase);
foreach ($sourceColumns as $column => $definition) {
    if (!array_key_exists($column, $tempColumns)) {
        $errors[] = "Falta columna en instalador: {$column}";
        continue;
    }

    if ($definition['type'] !== $tempColumns[$column]['type']) {
        $errors[] = "Tipo distinto en {$column}: real={$definition['type']} instalador={$tempColumns[$column]['type']}";
    }
}

$sourceIndexes = $fetchIndexes($pdo, $sourceDatabase);
$tempIndexes = $fetchIndexes($pdo, $tempDatabase);
foreach ($sourceIndexes as $index => $definition) {
    if (!array_key_exists($index, $tempIndexes)) {
        $errors[] = "Falta indice en instalador: {$index}";
        continue;
    }

    if ($definition !== $tempIndexes[$index]) {
        $errors[] = "Indice distinto en {$index}";
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        echo "[ERROR] {$error}\n";
    }
    echo "[ERROR] Instalador desalineado: " . count($errors) . " diferencia(s).\n";
    exit(1);
}

echo "[OK] {$installFile} importa en MySQL real y coincide con el esquema de {$sourceDatabase}.\n";

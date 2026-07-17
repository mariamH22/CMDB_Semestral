<?php
declare(strict_types=1);

use App\Core\Config;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
require_once dirname(__DIR__, 2) . '/app/Core/helpers.php';

Config::load(require dirname(__DIR__, 2) . '/app/Config/config.php');

$errors = [];
$ok = static function (string $message): void {
    echo "[OK] {$message}\n";
};
$fail = static function (string $message) use (&$errors): void {
    $errors[] = $message;
    echo "[ERROR] {$message}\n";
};

foreach (['pdo_mysql', 'mbstring', 'gd', 'fileinfo', 'openssl', 'sodium'] as $extension) {
    extension_loaded($extension)
        ? $ok("Extension PHP {$extension} cargada.")
        : $fail("Falta extension PHP {$extension}.");
}

$host = (string) Config::get('db.host', 'localhost');
$database = (string) Config::get('db.database', 'cmdb_integral');
$user = (string) Config::get('db.user', 'root');
$password = (string) Config::get('db.password', '');
$charset = (string) Config::get('db.charset', 'utf8mb4');
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
    $ok("Conexion PDO a {$database} establecida.");
} catch (Throwable $exception) {
    $fail('No se pudo conectar a la base real: ' . $exception->getMessage());
    exit(1);
}

$tableExists = static function (PDO $pdo, string $table): bool {
    $statement = $pdo->prepare(
        'SELECT COUNT(*) AS total
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :table'
    );
    $statement->execute(['table' => $table]);
    $row = $statement->fetch();

    return (int) ($row['total'] ?? 0) > 0;
};

$columnExists = static function (PDO $pdo, string $table, string $column): bool {
    $statement = $pdo->prepare(
        'SELECT COUNT(*) AS total
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :table
           AND COLUMN_NAME = :column'
    );
    $statement->execute(['table' => $table, 'column' => $column]);
    $row = $statement->fetch();

    return (int) ($row['total'] ?? 0) > 0;
};

$enumHasValue = static function (PDO $pdo, string $table, string $column, string $value): bool {
    $statement = $pdo->prepare(
        'SELECT COLUMN_TYPE
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :table
           AND COLUMN_NAME = :column
         LIMIT 1'
    );
    $statement->execute(['table' => $table, 'column' => $column]);
    $row = $statement->fetch();

    return str_contains((string) ($row['COLUMN_TYPE'] ?? ''), "'" . str_replace("'", "''", $value) . "'");
};

$columnAllowsNull = static function (PDO $pdo, string $table, string $column): bool {
    $statement = $pdo->prepare(
        'SELECT IS_NULLABLE
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = :table
           AND COLUMN_NAME = :column
         LIMIT 1'
    );
    $statement->execute(['table' => $table, 'column' => $column]);
    $row = $statement->fetch();

    return (string) ($row['IS_NULLABLE'] ?? '') === 'YES';
};

$requiredTables = [
    'colaboradores',
    'usuarios',
    'categorias',
    'inventario',
    'inventario_imagenes',
    'asignaciones',
    'necesidades',
    'necesidades_historial',
    'bitacora',
    'llaves_rsa',
    'firmas_digitales',
    'inventario_qr',
    'presupuestos',
    'presupuesto_detalles',
    'inventario_estado_historial',
    'devoluciones',
    'revisiones_tecnicas',
    'licencia_asignaciones',
    'ubicaciones_historial',
];

foreach ($requiredTables as $table) {
    $tableExists($pdo, $table)
        ? $ok("Tabla {$table} existe.")
        : $fail("Falta tabla {$table}.");
}

$requiredColumns = [
    'inventario' => [
        'estado',
        'valor_donacion',
        'autorizador_donacion_id',
        'responsable_descarte_id',
        'motivo_descarte',
        'clave_licencia_cifrada',
        'clave_licencia_hash',
        'estado_licencia',
    ],
    'inventario_qr' => [
        'token_hash',
        'estado',
        'created_by',
        'revoked_by',
        'revoked_reason',
        'regenerated_from_id',
        'last_accessed_at',
        'access_count',
    ],
    'devoluciones' => [
        'solicitado_por',
        'recibido_por',
        'evidencia',
        'fecha_recepcion',
        'accesorios_recibidos',
        'observacion_recepcion',
        'firma_id',
    ],
    'revisiones_tecnicas' => [
        'diagnostico',
        'opinion_tecnica',
        'recomendacion',
        'aprobador_id',
        'firma_id',
    ],
    'necesidades' => [
        'justificacion',
        'costo_unitario_estimado',
        'cantidad',
        'anio_objetivo',
        'usuario_procesador_id',
        'respuesta_administrativa',
        'audit_id',
        'firma_id',
    ],
    'necesidades_historial' => [
        'respuesta_administrativa',
        'firma_id',
        'audit_id',
    ],
    'bitacora' => [
        'entidad',
        'entidad_id',
        'user_agent',
        'resultado',
        'correlation_id',
        'previous_hash',
        'record_hash',
        'firma_id',
        'payload_version',
    ],
    'llaves_rsa' => [
        'key_store_reference',
        'private_key_encrypted',
        'algoritmo',
        'bits',
        'replaced_at',
        'revocation_reason',
        'revoked_by',
    ],
    'firmas_digitales' => [
        'fingerprint',
        'payload_version',
        'audit_id',
        'correlation_id',
        'payload_json',
        'resultado_inicial',
        'resultado_verificacion',
        'verified_at',
    ],
    'presupuestos' => [
        'presupuesto_base',
        'inflacion_anual',
        'crecimiento_anual',
        'total_quinquenal',
        'registros_sin_costo',
        'supuestos',
        'filtros_json',
    ],
    'presupuesto_detalles' => [
        'costo_base',
        'year_index',
        'factor_proyeccion',
        'tiene_costo',
        'motivo_sin_costo',
        'prioridad',
        'estado_solicitud',
    ],
    'asignaciones' => [
        'usuario_asignador_id',
        'audit_id',
        'firma_id',
    ],
    'ubicaciones_historial' => [
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'usuario_id',
        'audit_id',
    ],
    'inventario_estado_historial' => [
        'entidad_origen',
        'entidad_id',
        'audit_id',
    ],
];

foreach ($requiredColumns as $table => $columns) {
    foreach ($columns as $column) {
        $columnExists($pdo, $table, $column)
            ? $ok("Columna {$table}.{$column} existe.")
            : $fail("Falta columna {$table}.{$column}.");
    }
}

$requiredEnumValues = [
    ['inventario', 'estado', 'DEVOLUCION_REGISTRADA'],
    ['inventario', 'estado', 'REVISION_TECNICA'],
    ['inventario', 'estado', 'EN_REPARACION'],
    ['necesidades', 'estado', 'EN_ESPERA'],
    ['necesidades', 'estado', 'EN_TRAMITE'],
    ['necesidades', 'estado', 'APROBADA'],
    ['necesidades', 'estado', 'RECHAZADA'],
    ['llaves_rsa', 'estado', 'REEMPLAZADA'],
    ['inventario_qr', 'estado', 'REVOCADO'],
    ['revisiones_tecnicas', 'resultado', 'DONADO'],
];

foreach ($requiredEnumValues as [$table, $column, $value]) {
    $enumHasValue($pdo, $table, $column, $value)
        ? $ok("ENUM {$table}.{$column} contiene {$value}.")
        : $fail("ENUM {$table}.{$column} no contiene {$value}.");
}

$columnAllowsNull($pdo, 'devoluciones', 'estado_fisico')
    ? $ok('Columna devoluciones.estado_fisico permite NULL hasta la recepcion fisica.')
    : $fail('Columna devoluciones.estado_fisico debe permitir NULL hasta la recepcion fisica.');

$statement = $pdo->query("SELECT COUNT(*) AS total FROM inventario WHERE estado = 'DONADO' AND activo = 1");
$donatedActive = (int) (($statement ? $statement->fetch() : [])['total'] ?? 0);
$donatedActive === 0
    ? $ok('No hay activos DONADO dentro del inventario operativo activo.')
    : $fail("Existen {$donatedActive} activos DONADO con activo = 1.");

if ($errors !== []) {
    echo "\nVerificacion fallida con " . count($errors) . " problema(s).\n";
    exit(1);
}

echo "\nVerificacion completa: entorno y esquema principal listos.\n";

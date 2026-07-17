<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$files = [
    'database/cmdb_integral.sql',
    'database/install/fresh_install.sql',
    'database/install/cmdb_integral_full_install.sql',
];

$requiredFragments = [
    'CREATE TABLE necesidades_historial',
    "estado ENUM('DISPONIBLE','ASIGNADO','DEVOLUCION_REGISTRADA','REVISION_TECNICA','EN_REPARACION','DANADO','DESCARTE','DONADO','MANTENIMIENTO')",
    "estado ENUM('EN_ESPERA','EN_TRAMITE','APROBADA','RECHAZADA','PENDIENTE','EN_REVISION','ATENDIDA','CANCELADA')",
    'clave_licencia_cifrada LONGTEXT NULL',
    'estado_licencia ENUM',
    'token_hash CHAR(64) NULL',
    "estado ENUM('ACTIVO','REVOCADO')",
    'key_store_reference VARCHAR(255) NULL',
    "estado ENUM('ACTIVA','REVOCADA','REEMPLAZADA','ROTADA')",
    'payload_json LONGTEXT NULL',
    'record_hash CHAR(64) NULL',
    'presupuesto_base DECIMAL(14,2)',
    'factor_proyeccion DECIMAL(16,8)',
    'evidencia VARCHAR(255) NULL',
    "estado_fisico ENUM('BUENO','REGULAR','DANADO','INCOMPLETO') NULL",
    'fecha_recepcion DATETIME NULL',
    'accesorios_recibidos TEXT NULL',
    'observacion_recepcion TEXT NULL',
    'diagnostico TEXT NULL',
    'opinion_tecnica TEXT NULL',
    'recomendacion TEXT NULL',
    'aprobador_id INT UNSIGNED NULL',
    'entidad_origen VARCHAR(80) NULL',
    'CREATE UNIQUE INDEX idx_qr_token_hash ON inventario_qr(token_hash)',
    'CREATE INDEX idx_necesidades_historial_audit ON necesidades_historial(audit_id)',
    'CREATE INDEX idx_presupuesto_detalles_filtros ON presupuesto_detalles',
    'CONSTRAINT fk_necesidad_historial_necesidad FOREIGN KEY (necesidad_id)',
    'CONSTRAINT fk_necesidad_historial_usuario FOREIGN KEY (usuario_id)',
];

$forbiddenFragments = [
    'fk_necesidad_historial_formal_necesidad',
    'fk_necesidad_historial_formal_usuario',
];

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

foreach ($files as $file) {
    $path = $root . '/' . $file;
    $sql = (string) file_get_contents($path);
    $assert($sql !== '', "{$file} debe existir y no estar vacio.");

    foreach ($requiredFragments as $fragment) {
        $assert(str_contains($sql, $fragment), "{$file} no contiene {$fragment}.");
    }

    foreach ($forbiddenFragments as $fragment) {
        $assert(!str_contains($sql, $fragment), "{$file} conserva fragmento obsoleto {$fragment}.");
    }
}

echo "OK InstallSchemaSqlTest\n";

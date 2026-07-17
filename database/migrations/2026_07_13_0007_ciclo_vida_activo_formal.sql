-- ============================================================================
-- CMDB Integral - Ciclo de vida formal del activo
-- Fecha: 2026-07-13
--
-- IMPORTANTE:
-- 1. No ejecutar automaticamente en una base real.
-- 2. Respaldar la base antes de aplicar.
-- 3. Este archivo no cambia credenciales ni configuracion de Ubuntu/Nginx.
-- 4. MANTENIMIENTO se conserva como valor legado; EN_REPARACION es el estado formal.
-- 5. Compatible con MySQL/MariaDB sin alteraciones condicionales nativas.
-- ============================================================================

USE cmdb_integral;

ALTER TABLE inventario
    MODIFY COLUMN estado ENUM(
        'DISPONIBLE',
        'ASIGNADO',
        'DEVOLUCION_REGISTRADA',
        'REVISION_TECNICA',
        'EN_REPARACION',
        'DANADO',
        'DESCARTE',
        'DONADO',
        'MANTENIMIENTO'
    ) NOT NULL DEFAULT 'DISPONIBLE';

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'inventario'
      AND COLUMN_NAME = 'valor_donacion'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE inventario ADD COLUMN valor_donacion DECIMAL(12,2) NULL AFTER fecha_donacion',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'inventario'
      AND COLUMN_NAME = 'autorizador_donacion_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE inventario ADD COLUMN autorizador_donacion_id INT UNSIGNED NULL AFTER valor_donacion',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'inventario'
      AND COLUMN_NAME = 'responsable_descarte_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE inventario ADD COLUMN responsable_descarte_id INT UNSIGNED NULL AFTER evaluador_descarte_id',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'inventario'
      AND COLUMN_NAME = 'motivo_descarte'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE inventario ADD COLUMN motivo_descarte VARCHAR(255) NULL AFTER responsable_descarte_id',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'devoluciones'
      AND COLUMN_NAME = 'evidencia'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE devoluciones ADD COLUMN evidencia VARCHAR(255) NULL AFTER observaciones',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'devoluciones'
      AND COLUMN_NAME = 'fecha_recepcion'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE devoluciones ADD COLUMN fecha_recepcion DATETIME NULL AFTER evidencia',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

ALTER TABLE devoluciones
    MODIFY COLUMN fecha_recepcion DATETIME NULL;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'devoluciones'
      AND COLUMN_NAME = 'accesorios_recibidos'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE devoluciones ADD COLUMN accesorios_recibidos TEXT NULL AFTER fecha_recepcion',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'devoluciones'
      AND COLUMN_NAME = 'observacion_recepcion'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE devoluciones ADD COLUMN observacion_recepcion TEXT NULL AFTER accesorios_recibidos',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'devoluciones'
      AND COLUMN_NAME = 'firma_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE devoluciones ADD COLUMN firma_id BIGINT UNSIGNED NULL AFTER observacion_recepcion',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

ALTER TABLE revisiones_tecnicas
    MODIFY COLUMN resultado ENUM('DISPONIBLE','EN_REPARACION','DANADO','DESCARTE','DONADO','MANTENIMIENTO') NOT NULL;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'revisiones_tecnicas'
      AND COLUMN_NAME = 'diagnostico'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE revisiones_tecnicas ADD COLUMN diagnostico TEXT NULL AFTER resultado',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'revisiones_tecnicas'
      AND COLUMN_NAME = 'opinion_tecnica'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE revisiones_tecnicas ADD COLUMN opinion_tecnica TEXT NULL AFTER diagnostico',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'revisiones_tecnicas'
      AND COLUMN_NAME = 'recomendacion'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE revisiones_tecnicas ADD COLUMN recomendacion TEXT NULL AFTER opinion_tecnica',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'revisiones_tecnicas'
      AND COLUMN_NAME = 'aprobador_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE revisiones_tecnicas ADD COLUMN aprobador_id INT UNSIGNED NULL AFTER evidencia',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'inventario_estado_historial'
      AND COLUMN_NAME = 'entidad_origen'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE inventario_estado_historial ADD COLUMN entidad_origen VARCHAR(80) NULL AFTER firma_id',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'inventario_estado_historial'
      AND COLUMN_NAME = 'entidad_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE inventario_estado_historial ADD COLUMN entidad_id BIGINT UNSIGNED NULL AFTER entidad_origen',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'inventario_estado_historial'
      AND COLUMN_NAME = 'audit_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE inventario_estado_historial ADD COLUMN audit_id BIGINT UNSIGNED NULL AFTER entidad_id',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'inventario_estado_historial'
      AND INDEX_NAME = 'idx_historial_estado_origen'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0,
    'CREATE INDEX idx_historial_estado_origen ON inventario_estado_historial(entidad_origen, entidad_id)',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'devoluciones'
      AND INDEX_NAME = 'idx_devoluciones_firma'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0,
    'CREATE INDEX idx_devoluciones_firma ON devoluciones(firma_id)',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'revisiones_tecnicas'
      AND INDEX_NAME = 'idx_revision_aprobador'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0,
    'CREATE INDEX idx_revision_aprobador ON revisiones_tecnicas(aprobador_id)',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

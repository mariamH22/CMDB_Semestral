-- ============================================================================
-- CMDB Integral - Licencias, portal y cifrado de claves
-- Fecha: 2026-07-13
--
-- IMPORTANTE:
-- 1. No ejecutar automaticamente en una base real.
-- 2. Respaldar la base antes de aplicar.
-- 3. Este archivo no cambia credenciales ni configuracion de Ubuntu/Nginx.
-- 4. La columna clave_licencia se conserva como legado para migracion gradual.
-- ============================================================================

USE cmdb_integral;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario' AND COLUMN_NAME = 'tipo_licencia'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario ADD COLUMN tipo_licencia VARCHAR(80) NULL AFTER proveedor_licencia', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario' AND COLUMN_NAME = 'fecha_adquisicion_licencia'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario ADD COLUMN fecha_adquisicion_licencia DATE NULL AFTER tipo_licencia', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario' AND COLUMN_NAME = 'estado_licencia'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario ADD COLUMN estado_licencia ENUM(''ACTIVA'',''INACTIVA'',''VENCIDA'') NOT NULL DEFAULT ''ACTIVA'' AFTER fecha_vencimiento_licencia', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario' AND COLUMN_NAME = 'clave_licencia_cifrada'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario ADD COLUMN clave_licencia_cifrada LONGTEXT NULL AFTER clave_licencia', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario' AND COLUMN_NAME = 'clave_licencia_hash'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario ADD COLUMN clave_licencia_hash CHAR(64) NULL AFTER clave_licencia_cifrada', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario' AND COLUMN_NAME = 'clave_licencia_algoritmo'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario ADD COLUMN clave_licencia_algoritmo VARCHAR(40) NULL AFTER clave_licencia_hash', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario' AND COLUMN_NAME = 'clave_licencia_migrada_at'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario ADD COLUMN clave_licencia_migrada_at DATETIME NULL AFTER clave_licencia_algoritmo', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario' AND INDEX_NAME = 'idx_inventario_licencia_estado'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_inventario_licencia_estado ON inventario(es_licencia, estado_licencia)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario' AND INDEX_NAME = 'idx_inventario_licencia_vencimiento'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_inventario_licencia_vencimiento ON inventario(fecha_vencimiento_licencia)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'licencia_asignaciones' AND INDEX_NAME = 'idx_licencia_asignaciones_colaborador'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_licencia_asignaciones_colaborador ON licencia_asignaciones(colaborador_id, estado)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

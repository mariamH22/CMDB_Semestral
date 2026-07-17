-- ============================================================================
-- CMDB Integral - Audit Trail con deteccion criptografica de alteraciones
-- Fecha: 2026-07-13
--
-- IMPORTANTE:
-- 1. No ejecutar automaticamente en una base real.
-- 2. Respaldar la base antes de aplicar.
-- 3. Este archivo no cambia credenciales ni configuracion de Ubuntu/Nginx.
-- 4. La bitacora no es inmutable; ofrece trazabilidad con deteccion criptografica
--    de alteraciones.
-- ============================================================================

USE cmdb_integral;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND COLUMN_NAME = 'user_agent'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE bitacora ADD COLUMN user_agent VARCHAR(255) NULL AFTER ip', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND COLUMN_NAME = 'entidad'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE bitacora ADD COLUMN entidad VARCHAR(80) NULL AFTER accion', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND COLUMN_NAME = 'entidad_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE bitacora ADD COLUMN entidad_id BIGINT UNSIGNED NULL AFTER entidad', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND COLUMN_NAME = 'resultado'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE bitacora ADD COLUMN resultado VARCHAR(40) NOT NULL DEFAULT ''OK'' AFTER nivel', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND COLUMN_NAME = 'motivo'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE bitacora ADD COLUMN motivo VARCHAR(255) NULL AFTER resultado', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND COLUMN_NAME = 'datos_anteriores_json'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE bitacora ADD COLUMN datos_anteriores_json LONGTEXT NULL AFTER motivo', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND COLUMN_NAME = 'datos_posteriores_json'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE bitacora ADD COLUMN datos_posteriores_json LONGTEXT NULL AFTER datos_anteriores_json', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND COLUMN_NAME = 'correlation_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE bitacora ADD COLUMN correlation_id CHAR(32) NULL AFTER datos_posteriores_json', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND COLUMN_NAME = 'previous_hash'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE bitacora ADD COLUMN previous_hash CHAR(64) NULL AFTER correlation_id', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND COLUMN_NAME = 'record_hash'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE bitacora ADD COLUMN record_hash CHAR(64) NULL AFTER previous_hash', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND COLUMN_NAME = 'firma_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE bitacora ADD COLUMN firma_id BIGINT UNSIGNED NULL AFTER record_hash', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND COLUMN_NAME = 'fingerprint'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE bitacora ADD COLUMN fingerprint CHAR(64) NULL AFTER firma_id', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND COLUMN_NAME = 'payload_version'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE bitacora ADD COLUMN payload_version SMALLINT UNSIGNED NOT NULL DEFAULT 1 AFTER fingerprint', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND INDEX_NAME = 'idx_bitacora_correlation'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_bitacora_correlation ON bitacora(correlation_id)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND INDEX_NAME = 'idx_bitacora_entidad'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_bitacora_entidad ON bitacora(entidad, entidad_id)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND INDEX_NAME = 'idx_bitacora_hash'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_bitacora_hash ON bitacora(record_hash)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bitacora' AND INDEX_NAME = 'idx_bitacora_firma'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_bitacora_firma ON bitacora(firma_id)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

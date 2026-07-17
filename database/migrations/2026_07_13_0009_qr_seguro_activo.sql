-- ============================================================================
-- CMDB Integral - QR seguro por activo
-- Fecha: 2026-07-13
--
-- IMPORTANTE:
-- 1. No ejecutar automaticamente en una base real.
-- 2. Respaldar la base antes de aplicar.
-- 3. Este archivo no cambia credenciales ni configuracion de Ubuntu/Nginx.
-- 4. El token visible sigue viajando solo dentro del QR/URL; la busqueda usa
--    token_hash cuando esta columna existe.
-- ============================================================================

USE cmdb_integral;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario_qr' AND COLUMN_NAME = 'token_hash'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario_qr ADD COLUMN token_hash CHAR(64) NULL AFTER token', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario_qr' AND COLUMN_NAME = 'estado'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario_qr ADD COLUMN estado ENUM(''ACTIVO'',''REVOCADO'') NOT NULL DEFAULT ''ACTIVO'' AFTER activo', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario_qr' AND COLUMN_NAME = 'created_by'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario_qr ADD COLUMN created_by INT UNSIGNED NULL AFTER estado', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario_qr' AND COLUMN_NAME = 'revoked_by'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario_qr ADD COLUMN revoked_by INT UNSIGNED NULL AFTER revoked_at', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario_qr' AND COLUMN_NAME = 'revoked_reason'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario_qr ADD COLUMN revoked_reason VARCHAR(255) NULL AFTER revoked_by', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario_qr' AND COLUMN_NAME = 'regenerated_from_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario_qr ADD COLUMN regenerated_from_id BIGINT UNSIGNED NULL AFTER revoked_reason', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario_qr' AND COLUMN_NAME = 'last_accessed_at'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario_qr ADD COLUMN last_accessed_at DATETIME NULL AFTER regenerated_from_id', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario_qr' AND COLUMN_NAME = 'access_count'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE inventario_qr ADD COLUMN access_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER last_accessed_at', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

UPDATE inventario_qr
SET token_hash = SHA2(CONCAT('cmdb-qr-token-v1:', LOWER(token)), 256)
WHERE token_hash IS NULL
  AND token IS NOT NULL
  AND token <> '';

UPDATE inventario_qr
SET estado = CASE
    WHEN activo = 1 AND revoked_at IS NULL THEN 'ACTIVO'
    ELSE 'REVOCADO'
END;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario_qr' AND INDEX_NAME = 'idx_qr_token_hash'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE UNIQUE INDEX idx_qr_token_hash ON inventario_qr(token_hash)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario_qr' AND INDEX_NAME = 'idx_qr_estado'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_qr_estado ON inventario_qr(inventario_id, estado, activo)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario_qr' AND INDEX_NAME = 'idx_qr_acceso'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_qr_acceso ON inventario_qr(last_accessed_at)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventario_qr' AND INDEX_NAME = 'idx_qr_regenerado_desde'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_qr_regenerado_desde ON inventario_qr(regenerated_from_id)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

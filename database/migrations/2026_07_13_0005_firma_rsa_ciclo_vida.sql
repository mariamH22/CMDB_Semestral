-- ============================================================================
-- CMDB Integral - Firma RSA y ciclo de vida de llaves
-- Fecha: 2026-07-13
--
-- IMPORTANTE:
-- 1. No ejecutar automaticamente en una base real.
-- 2. Respaldar la base antes de aplicar.
-- 3. Este archivo no cambia credenciales ni configuracion de Ubuntu/Nginx.
-- 4. Las llaves privadas permanecen cifradas fuera del repositorio.
-- ============================================================================

USE cmdb_integral;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'llaves_rsa'
      AND COLUMN_NAME = 'revocation_reason'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE llaves_rsa ADD COLUMN revocation_reason VARCHAR(255) NULL AFTER revoked_at',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'llaves_rsa'
      AND COLUMN_NAME = 'revoked_by'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE llaves_rsa ADD COLUMN revoked_by INT UNSIGNED NULL AFTER revocation_reason',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'firmas_digitales'
      AND COLUMN_NAME = 'fingerprint'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE firmas_digitales ADD COLUMN fingerprint CHAR(64) NULL AFTER algoritmo',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'firmas_digitales'
      AND COLUMN_NAME = 'payload_version'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE firmas_digitales ADD COLUMN payload_version SMALLINT UNSIGNED NOT NULL DEFAULT 1 AFTER fingerprint',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'firmas_digitales'
      AND COLUMN_NAME = 'audit_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE firmas_digitales ADD COLUMN audit_id BIGINT UNSIGNED NULL AFTER payload_version',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'firmas_digitales'
      AND COLUMN_NAME = 'correlation_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE firmas_digitales ADD COLUMN correlation_id CHAR(32) NULL AFTER audit_id',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'firmas_digitales'
      AND COLUMN_NAME = 'payload_json'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE firmas_digitales ADD COLUMN payload_json LONGTEXT NULL AFTER correlation_id',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'firmas_digitales'
      AND COLUMN_NAME = 'resultado_inicial'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE firmas_digitales ADD COLUMN resultado_inicial ENUM(''VALIDA'',''INVALIDA'',''LLAVE_REVOCADA'',''NO_VERIFICABLE'',''ERROR'') NOT NULL DEFAULT ''NO_VERIFICABLE'' AFTER payload_json',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'firmas_digitales'
      AND COLUMN_NAME = 'resultado_verificacion'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE firmas_digitales ADD COLUMN resultado_verificacion ENUM(''VALIDA'',''INVALIDA'',''LLAVE_REVOCADA'',''NO_VERIFICABLE'',''ERROR'') NULL AFTER resultado_inicial',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'firmas_digitales'
      AND COLUMN_NAME = 'verified_at'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE firmas_digitales ADD COLUMN verified_at DATETIME NULL AFTER resultado_verificacion',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'firmas_digitales'
      AND INDEX_NAME = 'idx_firmas_digitales_audit_id'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0,
    'CREATE INDEX idx_firmas_digitales_audit_id ON firmas_digitales(audit_id)',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'firmas_digitales'
      AND INDEX_NAME = 'idx_firmas_digitales_resultado'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0,
    'CREATE INDEX idx_firmas_digitales_resultado ON firmas_digitales(resultado_verificacion)',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'firmas_digitales'
      AND INDEX_NAME = 'idx_firmas_digitales_fingerprint'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0,
    'CREATE INDEX idx_firmas_digitales_fingerprint ON firmas_digitales(fingerprint)',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

-- ============================================================================
-- CMDB Integral - Contratos criptograficos y almacen cifrado de llaves
-- Fecha: 2026-07-13
--
-- IMPORTANTE:
-- 1. No ejecutar automaticamente en una base real.
-- 2. Respaldar la base antes de aplicar.
-- 3. No guarda llaves privadas en texto plano.
-- 4. El archivo privado cifrado vive fuera de public/ y fuera de Git.
-- ============================================================================

USE cmdb_integral;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'llaves_rsa'
      AND COLUMN_NAME = 'key_store_reference'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE llaves_rsa ADD COLUMN key_store_reference VARCHAR(255) NULL AFTER private_key_path',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'llaves_rsa'
      AND COLUMN_NAME = 'private_key_encrypted'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE llaves_rsa ADD COLUMN private_key_encrypted TINYINT(1) NOT NULL DEFAULT 1 AFTER key_store_reference',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'llaves_rsa'
      AND COLUMN_NAME = 'algoritmo'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE llaves_rsa ADD COLUMN algoritmo VARCHAR(40) NOT NULL DEFAULT ''RSA-SHA256'' AFTER private_key_encrypted',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'llaves_rsa'
      AND COLUMN_NAME = 'bits'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE llaves_rsa ADD COLUMN bits SMALLINT UNSIGNED NOT NULL DEFAULT 3072 AFTER algoritmo',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'llaves_rsa'
      AND COLUMN_NAME = 'replaced_at'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE llaves_rsa ADD COLUMN replaced_at DATETIME NULL AFTER revoked_at',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

-- Se conserva ROTADA como valor legado por compatibilidad con instalaciones anteriores,
-- y se agrega REEMPLAZADA como estado formal usado por las nuevas fases.
ALTER TABLE llaves_rsa
    MODIFY COLUMN estado ENUM('ACTIVA','REVOCADA','REEMPLAZADA','ROTADA') NOT NULL DEFAULT 'ACTIVA';

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'llaves_rsa'
      AND INDEX_NAME = 'idx_llaves_rsa_usuario_estado'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0,
    'CREATE INDEX idx_llaves_rsa_usuario_estado ON llaves_rsa(usuario_id, estado)',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'llaves_rsa'
      AND INDEX_NAME = 'idx_llaves_rsa_fingerprint'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0,
    'CREATE INDEX idx_llaves_rsa_fingerprint ON llaves_rsa(fingerprint)',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

-- ============================================================================
-- CMDB Integral - Asignador formal y validaciones finales
-- Fecha: 2026-07-13
--
-- IMPORTANTE:
-- 1. No ejecutar automaticamente en una base real.
-- 2. Respaldar la base antes de aplicar.
-- 3. Este archivo no cambia credenciales ni configuracion de Ubuntu/Nginx.
-- ============================================================================

USE cmdb_integral;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'asignaciones'
      AND COLUMN_NAME = 'usuario_asignador_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE asignaciones ADD COLUMN usuario_asignador_id INT UNSIGNED NULL AFTER colaborador_id',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'asignaciones'
      AND COLUMN_NAME = 'audit_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE asignaciones ADD COLUMN audit_id BIGINT UNSIGNED NULL AFTER usuario_asignador_id',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'asignaciones'
      AND COLUMN_NAME = 'firma_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE asignaciones ADD COLUMN firma_id BIGINT UNSIGNED NULL AFTER audit_id',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'asignaciones'
      AND INDEX_NAME = 'idx_asignaciones_usuario_asignador'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0,
    'CREATE INDEX idx_asignaciones_usuario_asignador ON asignaciones(usuario_asignador_id)',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'asignaciones'
      AND INDEX_NAME = 'idx_asignaciones_audit'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0,
    'CREATE INDEX idx_asignaciones_audit ON asignaciones(audit_id)',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'asignaciones'
      AND INDEX_NAME = 'idx_asignaciones_firma'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0,
    'CREATE INDEX idx_asignaciones_firma ON asignaciones(firma_id)',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

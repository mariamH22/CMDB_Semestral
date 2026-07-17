-- ============================================================================
-- CMDB Integral - Fase 7A: imagenes e historial de ubicaciones
-- Fecha: 2026-07-13
--
-- Aplicar sobre bases existentes con respaldo previo.
-- No modifica configuracion de Apache, WampServer, Ubuntu ni Nginx.
-- ============================================================================

USE cmdb_integral;

ALTER TABLE colaboradores
    MODIFY COLUMN ubicacion VARCHAR(150) NULL;

CREATE TABLE IF NOT EXISTS ubicaciones_historial (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT UNSIGNED NOT NULL,
    ubicacion_anterior VARCHAR(150) NULL,
    ubicacion_nueva VARCHAR(150) NULL,
    tipo ENUM('OFICINA','EDIFICIO','CASA','SEDE','DIRECCION','OTRO') NULL,
    fecha_inicio DATE NULL,
    fecha_fin DATE NULL,
    motivo TEXT NULL,
    usuario_id INT UNSIGNED NULL,
    audit_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ubicacion_historial_colaborador
        FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
    CONSTRAINT fk_ubicacion_historial_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ubicaciones_historial' AND COLUMN_NAME = 'fecha_inicio'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE ubicaciones_historial ADD COLUMN fecha_inicio DATE NULL', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ubicaciones_historial' AND COLUMN_NAME = 'fecha_fin'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE ubicaciones_historial ADD COLUMN fecha_fin DATE NULL AFTER fecha_inicio', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ubicaciones_historial' AND COLUMN_NAME = 'motivo'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE ubicaciones_historial ADD COLUMN motivo TEXT NULL AFTER fecha_fin', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ubicaciones_historial' AND COLUMN_NAME = 'usuario_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE ubicaciones_historial ADD COLUMN usuario_id INT UNSIGNED NULL AFTER motivo', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ubicaciones_historial' AND COLUMN_NAME = 'audit_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE ubicaciones_historial ADD COLUMN audit_id BIGINT UNSIGNED NULL AFTER usuario_id', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ubicaciones_historial' AND INDEX_NAME = 'idx_ubicaciones_historial_colaborador'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_ubicaciones_historial_colaborador ON ubicaciones_historial(colaborador_id, created_at)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ubicaciones_historial' AND INDEX_NAME = 'idx_ubicaciones_historial_audit'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_ubicaciones_historial_audit ON ubicaciones_historial(audit_id)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

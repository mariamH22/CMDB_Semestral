-- ============================================================================
-- CMDB Integral - Solicitudes e historial formal
-- Fecha: 2026-07-13
--
-- IMPORTANTE:
-- 1. No ejecutar automaticamente en una base real.
-- 2. Respaldar la base antes de aplicar.
-- 3. Este archivo no cambia credenciales ni configuracion de Ubuntu/Nginx.
-- 4. Los estados legacy se conservan en el ENUM durante la transicion.
-- ============================================================================

USE cmdb_integral;

ALTER TABLE necesidades
    MODIFY COLUMN estado ENUM(
        'EN_ESPERA',
        'EN_TRAMITE',
        'APROBADA',
        'RECHAZADA',
        'PENDIENTE',
        'EN_REVISION',
        'ATENDIDA',
        'CANCELADA'
    ) NOT NULL DEFAULT 'EN_ESPERA';

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades' AND COLUMN_NAME = 'justificacion'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE necesidades ADD COLUMN justificacion TEXT NULL AFTER descripcion', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades' AND COLUMN_NAME = 'costo_unitario_estimado'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE necesidades ADD COLUMN costo_unitario_estimado DECIMAL(12,2) NULL AFTER costo_estimado', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades' AND COLUMN_NAME = 'cantidad'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE necesidades ADD COLUMN cantidad INT UNSIGNED NOT NULL DEFAULT 1 AFTER costo_unitario_estimado', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades' AND COLUMN_NAME = 'anio_objetivo'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE necesidades ADD COLUMN anio_objetivo SMALLINT UNSIGNED NULL AFTER cantidad', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades' AND COLUMN_NAME = 'usuario_procesador_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE necesidades ADD COLUMN usuario_procesador_id INT UNSIGNED NULL AFTER comentario_resolucion', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades' AND COLUMN_NAME = 'respuesta_administrativa'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE necesidades ADD COLUMN respuesta_administrativa TEXT NULL AFTER usuario_procesador_id', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades' AND COLUMN_NAME = 'fecha_procesamiento'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE necesidades ADD COLUMN fecha_procesamiento DATETIME NULL AFTER respuesta_administrativa', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades' AND COLUMN_NAME = 'audit_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE necesidades ADD COLUMN audit_id BIGINT UNSIGNED NULL AFTER fecha_procesamiento', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades' AND COLUMN_NAME = 'firma_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE necesidades ADD COLUMN firma_id BIGINT UNSIGNED NULL AFTER audit_id', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

UPDATE necesidades
SET estado = CASE estado
    WHEN 'PENDIENTE' THEN 'EN_ESPERA'
    WHEN 'EN_REVISION' THEN 'EN_TRAMITE'
    WHEN 'ATENDIDA' THEN 'APROBADA'
    WHEN 'CANCELADA' THEN 'RECHAZADA'
    ELSE estado
END;

UPDATE necesidades
SET justificacion = descripcion
WHERE justificacion IS NULL;

UPDATE necesidades
SET costo_unitario_estimado = CASE
        WHEN cantidad > 0 AND costo_estimado IS NOT NULL THEN ROUND(costo_estimado / cantidad, 2)
        ELSE costo_estimado
    END
WHERE costo_unitario_estimado IS NULL;

CREATE TABLE IF NOT EXISTS necesidades_historial (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    necesidad_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    estado_anterior VARCHAR(40) NULL,
    estado_nuevo VARCHAR(40) NOT NULL,
    observacion TEXT NULL,
    respuesta_administrativa TEXT NULL,
    firma_id BIGINT UNSIGNED NULL,
    audit_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_necesidad_historial_necesidad
        FOREIGN KEY (necesidad_id) REFERENCES necesidades(id) ON DELETE CASCADE,
    CONSTRAINT fk_necesidad_historial_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades_historial' AND COLUMN_NAME = 'respuesta_administrativa'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE necesidades_historial ADD COLUMN respuesta_administrativa TEXT NULL AFTER observacion', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades_historial' AND COLUMN_NAME = 'firma_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE necesidades_historial ADD COLUMN firma_id BIGINT UNSIGNED NULL AFTER respuesta_administrativa', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades_historial' AND COLUMN_NAME = 'audit_id'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0, 'ALTER TABLE necesidades_historial ADD COLUMN audit_id BIGINT UNSIGNED NULL AFTER firma_id', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades' AND INDEX_NAME = 'idx_necesidades_estado_formal'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_necesidades_estado_formal ON necesidades(estado, prioridad)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades' AND INDEX_NAME = 'idx_necesidades_procesador'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_necesidades_procesador ON necesidades(usuario_procesador_id, fecha_procesamiento)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades_historial' AND INDEX_NAME = 'idx_necesidades_historial_firma'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_necesidades_historial_firma ON necesidades_historial(firma_id)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_index_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'necesidades_historial' AND INDEX_NAME = 'idx_necesidades_historial_audit'
);
SET @cmdb_sql := IF(@cmdb_index_exists = 0, 'CREATE INDEX idx_necesidades_historial_audit ON necesidades_historial(audit_id)', 'SELECT 1');
PREPARE cmdb_stmt FROM @cmdb_sql; EXECUTE cmdb_stmt; DEALLOCATE PREPARE cmdb_stmt;

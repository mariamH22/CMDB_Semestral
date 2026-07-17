-- ============================================================================
-- CMDB Integral - Migración complementaria (requisitos funcionales)
-- Fecha: 2026-07-13
--
-- Aplicar sobre instalaciones existentes ya creadas con migración 0001.
-- ============================================================================

USE cmdb_integral;

-- 1) Requisito: ubicación puede ser opcional.
-- Mantener compatibilidad: sólo se relaja la restricción, no se pierde ningún dato.
ALTER TABLE colaboradores
    MODIFY COLUMN ubicacion VARCHAR(150) NULL;

-- 2) Historial formal de cambios de ubicación (incluye usuario responsable).
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
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ubicacion_historial_colaborador
        FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
    CONSTRAINT fk_ubicacion_historial_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 3) Formalizar estado de solicitudes con historial de transición.
CREATE TABLE IF NOT EXISTS necesidades_historial (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    necesidad_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    estado_anterior VARCHAR(40) NULL,
    estado_nuevo VARCHAR(40) NOT NULL,
    observacion TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_necesidad_historial_necesidad
        FOREIGN KEY (necesidad_id) REFERENCES necesidades(id) ON DELETE CASCADE,
    CONSTRAINT fk_necesidad_historial_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 4) Mejorar solicitudes con cantidad y año objetivo sin romper integraciones.
SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'necesidades'
      AND COLUMN_NAME = 'cantidad'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE necesidades ADD COLUMN cantidad INT UNSIGNED NOT NULL DEFAULT 1',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

SET @cmdb_column_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'necesidades'
      AND COLUMN_NAME = 'anio_objetivo'
);
SET @cmdb_sql := IF(@cmdb_column_exists = 0,
    'ALTER TABLE necesidades ADD COLUMN anio_objetivo SMALLINT UNSIGNED NULL AFTER cantidad',
    'SELECT 1'
);
PREPARE cmdb_stmt FROM @cmdb_sql;
EXECUTE cmdb_stmt;
DEALLOCATE PREPARE cmdb_stmt;

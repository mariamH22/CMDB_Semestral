-- ============================================================================
-- CMDB Integral - Migracion incremental de funcionalidades pendientes
-- Fecha: 2026-07-13
--
-- IMPORTANTE:
-- 1. No ejecutar automaticamente en una base real.
-- 2. Revisar, respaldar la base y ejecutar manualmente en phpMyAdmin/MySQL.
-- 3. Este archivo no cambia credenciales ni configuracion de Ubuntu/Nginx.
-- 4. Las llaves privadas RSA deben vivir fuera del repositorio.
-- ============================================================================

USE cmdb_integral;

-- --------------------------------------------------------------------------
-- Seguridad: gestion de llaves RSA y firmas digitales de acciones sensibles.
-- La ruta de la llave privada se guarda como referencia; la llave no va en DB.
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS llaves_rsa (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NULL,
    nombre VARCHAR(120) NOT NULL,
    public_key TEXT NOT NULL,
    private_key_path VARCHAR(255) NULL,
    fingerprint CHAR(64) NOT NULL UNIQUE,
    estado ENUM('ACTIVA','REVOCADA','ROTADA') NOT NULL DEFAULT 'ACTIVA',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at DATETIME NULL,
    CONSTRAINT fk_llave_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS firmas_digitales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    llave_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    modulo VARCHAR(80) NOT NULL,
    accion VARCHAR(80) NOT NULL,
    entidad VARCHAR(80) NOT NULL,
    entidad_id BIGINT UNSIGNED NULL,
    payload_hash CHAR(64) NOT NULL,
    firma TEXT NOT NULL,
    algoritmo VARCHAR(40) NOT NULL DEFAULT 'RSA-SHA256',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_firma_llave FOREIGN KEY (llave_id) REFERENCES llaves_rsa(id),
    CONSTRAINT fk_firma_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------------------------
-- QR por activo: token opaco para identificar un activo sin exponer secretos.
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inventario_qr (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inventario_id INT UNSIGNED NOT NULL,
    token CHAR(64) NOT NULL UNIQUE,
    payload_hash CHAR(64) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at DATETIME NULL,
    CONSTRAINT fk_qr_inventario FOREIGN KEY (inventario_id) REFERENCES inventario(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------------------------
-- Presupuesto anual/quinquenal basado en solicitudes, activos y categorias.
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS presupuestos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(160) NOT NULL,
    tipo ENUM('ANUAL','QUINQUENAL') NOT NULL,
    anio_inicio SMALLINT UNSIGNED NOT NULL,
    anio_fin SMALLINT UNSIGNED NOT NULL,
    total_estimado DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    estado ENUM('BORRADOR','APROBADO','CERRADO') NOT NULL DEFAULT 'BORRADOR',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_presupuesto_usuario FOREIGN KEY (created_by) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS presupuesto_detalles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    presupuesto_id INT UNSIGNED NOT NULL,
    categoria_id INT UNSIGNED NULL,
    necesidad_id INT UNSIGNED NULL,
    tipo_necesidad ENUM('EQUIPO','SOFTWARE','LICENCIA','RENOVACION','MANTENIMIENTO') NOT NULL,
    descripcion VARCHAR(500) NOT NULL,
    cantidad INT UNSIGNED NOT NULL DEFAULT 1,
    costo_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    anio SMALLINT UNSIGNED NOT NULL,
    CONSTRAINT fk_presupuesto_detalle_presupuesto FOREIGN KEY (presupuesto_id) REFERENCES presupuestos(id) ON DELETE CASCADE,
    CONSTRAINT fk_presupuesto_detalle_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    CONSTRAINT fk_presupuesto_detalle_necesidad FOREIGN KEY (necesidad_id) REFERENCES necesidades(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------------------------
-- Historial formal de estados de inventario.
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inventario_estado_historial (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inventario_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    estado_anterior VARCHAR(40) NULL,
    estado_nuevo VARCHAR(40) NOT NULL,
    motivo VARCHAR(160) NULL,
    observacion TEXT NULL,
    firma_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historial_estado_inventario FOREIGN KEY (inventario_id) REFERENCES inventario(id) ON DELETE CASCADE,
    CONSTRAINT fk_historial_estado_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_historial_estado_firma FOREIGN KEY (firma_id) REFERENCES firmas_digitales(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------------------------
-- Devolucion formal y revision tecnica antes de volver a disponible.
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS devoluciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asignacion_id INT UNSIGNED NOT NULL,
    inventario_id INT UNSIGNED NOT NULL,
    solicitado_por INT UNSIGNED NULL,
    recibido_por INT UNSIGNED NULL,
    motivo VARCHAR(160) NOT NULL,
    estado_fisico ENUM('BUENO','REGULAR','DANADO','INCOMPLETO') NULL,
    observaciones TEXT NULL,
    estado ENUM('PENDIENTE_REVISION','EN_REVISION','APROBADA','RECHAZADA') NOT NULL DEFAULT 'PENDIENTE_REVISION',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_devolucion_asignacion FOREIGN KEY (asignacion_id) REFERENCES asignaciones(id),
    CONSTRAINT fk_devolucion_inventario FOREIGN KEY (inventario_id) REFERENCES inventario(id),
    CONSTRAINT fk_devolucion_solicitado FOREIGN KEY (solicitado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_devolucion_recibido FOREIGN KEY (recibido_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS revisiones_tecnicas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    devolucion_id BIGINT UNSIGNED NOT NULL,
    inventario_id INT UNSIGNED NOT NULL,
    tecnico_id INT UNSIGNED NULL,
    resultado ENUM('DISPONIBLE','MANTENIMIENTO','DANADO','DESCARTE') NOT NULL,
    observacion_tecnica TEXT NOT NULL,
    evidencia VARCHAR(255) NULL,
    firma_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_revision_devolucion FOREIGN KEY (devolucion_id) REFERENCES devoluciones(id) ON DELETE CASCADE,
    CONSTRAINT fk_revision_inventario FOREIGN KEY (inventario_id) REFERENCES inventario(id),
    CONSTRAINT fk_revision_tecnico FOREIGN KEY (tecnico_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_revision_firma FOREIGN KEY (firma_id) REFERENCES firmas_digitales(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------------------------
-- Licencias: cupos y campos formales de proveedor/vencimiento.
-- --------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS licencia_asignaciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inventario_id INT UNSIGNED NOT NULL,
    colaborador_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    cantidad INT UNSIGNED NOT NULL DEFAULT 1,
    fecha_asignacion DATE NOT NULL,
    fecha_fin DATE NULL,
    estado ENUM('ACTIVA','LIBERADA','VENCIDA') NOT NULL DEFAULT 'ACTIVA',
    observaciones TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_licencia_asignacion_inventario FOREIGN KEY (inventario_id) REFERENCES inventario(id),
    CONSTRAINT fk_licencia_asignacion_colaborador FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id),
    CONSTRAINT fk_licencia_asignacion_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------------------------
-- Campos nuevos en tablas existentes.
-- Ejecutar este bloque solo una vez sobre bases antiguas que aun no tengan estos campos.
-- --------------------------------------------------------------------------
ALTER TABLE inventario
    ADD COLUMN proveedor_licencia VARCHAR(160) NULL AFTER clave_licencia,
    ADD COLUMN url_licencia VARCHAR(255) NULL AFTER proveedor_licencia,
    ADD COLUMN fecha_vencimiento_licencia DATE NULL AFTER url_licencia,
    ADD COLUMN observaciones_licencia TEXT NULL AFTER fecha_vencimiento_licencia,
    ADD COLUMN beneficiario_donacion VARCHAR(160) NULL AFTER responsable_donacion,
    ADD COLUMN evidencia_donacion VARCHAR(255) NULL AFTER beneficiario_donacion,
    ADD COLUMN observacion_donacion TEXT NULL AFTER evidencia_donacion,
    ADD COLUMN observacion_tecnica_descarte TEXT NULL AFTER observacion_donacion,
    ADD COLUMN evaluador_descarte_id INT UNSIGNED NULL AFTER observacion_tecnica_descarte,
    ADD COLUMN fecha_evaluacion_descarte DATE NULL AFTER evaluador_descarte_id,
    ADD COLUMN evidencia_descarte VARCHAR(255) NULL AFTER fecha_evaluacion_descarte,
    ADD CONSTRAINT fk_inventario_evaluador_descarte FOREIGN KEY (evaluador_descarte_id) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE necesidades
    ADD COLUMN costo_estimado DECIMAL(12,2) NULL AFTER prioridad;

CREATE INDEX idx_qr_inventario ON inventario_qr(inventario_id);
CREATE INDEX idx_historial_inventario ON inventario_estado_historial(inventario_id, created_at);
CREATE INDEX idx_devoluciones_estado ON devoluciones(estado);
CREATE INDEX idx_licencia_asignaciones_estado ON licencia_asignaciones(inventario_id, estado);

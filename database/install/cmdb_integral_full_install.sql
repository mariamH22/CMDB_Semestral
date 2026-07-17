-- ============================================================================
-- CMDB INTEGRAL - INSTALACIÓN COMPLETA (estructura + datos semilla + datos demo)
-- Proyecto Semestral Desarrollo Web VII - I Semestre 2026
-- IMPORTANTE:
--   1) Ejecutar solo en base nueva o vacía.
--   2) No usar para actualizar una base existente.
--   3) Respaldar antes de cualquier ejecución en entornos reales.
-- ============================================================================
CREATE DATABASE IF NOT EXISTS cmdb_integral CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cmdb_integral;

CREATE TABLE colaboradores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    identificacion VARCHAR(40) NOT NULL UNIQUE,
    departamento VARCHAR(100) NOT NULL,
    ubicacion VARCHAR(150) NULL,
    direccion VARCHAR(255) NULL,
    telefono VARCHAR(30) NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    foto VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT UNSIGNED NULL,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('ADMIN','OPERADOR','COLABORADOR') NOT NULL DEFAULT 'OPERADOR',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    estado_cuenta ENUM('ACTIVO','BLOQUEADO') NOT NULL DEFAULT 'ACTIVO',
    intentos_fallidos TINYINT UNSIGNED NOT NULL DEFAULT 0,
    ultimo_login_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_colaborador FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id)
) ENGINE=InnoDB;

CREATE TABLE categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    tipo ENUM('HARDWARE','SOFTWARE') NOT NULL,
    descripcion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE inventario (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT UNSIGNED NULL,
    codigo_activo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    tipo_activo ENUM('HARDWARE','SOFTWARE') NOT NULL,
    subcategoria VARCHAR(100) NULL,
    marca VARCHAR(100) NULL,
    modelo VARCHAR(100) NULL,
    serie VARCHAR(100) NOT NULL UNIQUE,
    costo DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    fecha_ingreso DATE NOT NULL,
    vida_util_meses SMALLINT UNSIGNED NOT NULL DEFAULT 36,
    estado ENUM('DISPONIBLE','ASIGNADO','DEVOLUCION_REGISTRADA','REVISION_TECNICA','EN_REPARACION','DANADO','DESCARTE','DONADO','MANTENIMIENTO') NOT NULL DEFAULT 'DISPONIBLE',
    imagen_principal VARCHAR(255) NULL,
    thumbnail VARCHAR(255) NULL,
    es_licencia TINYINT(1) NOT NULL DEFAULT 0,
    clave_licencia VARCHAR(255) NULL,
    clave_licencia_cifrada LONGTEXT NULL,
    clave_licencia_hash CHAR(64) NULL,
    clave_licencia_algoritmo VARCHAR(40) NULL,
    clave_licencia_migrada_at DATETIME NULL,
    proveedor_licencia VARCHAR(160) NULL,
    tipo_licencia VARCHAR(80) NULL,
    fecha_adquisicion_licencia DATE NULL,
    url_licencia VARCHAR(255) NULL,
    fecha_vencimiento_licencia DATE NULL,
    estado_licencia ENUM('ACTIVA','INACTIVA','VENCIDA') NOT NULL DEFAULT 'ACTIVA',
    observaciones_licencia TEXT NULL,
    cantidad INT UNSIGNED NOT NULL DEFAULT 1,
    responsable_donacion VARCHAR(120) NULL,
    beneficiario_donacion VARCHAR(160) NULL,
    evidencia_donacion VARCHAR(255) NULL,
    observacion_donacion TEXT NULL,
    fecha_donacion DATE NULL,
    valor_donacion DECIMAL(12,2) NULL,
    autorizador_donacion_id INT UNSIGNED NULL,
    observacion_tecnica_descarte TEXT NULL,
    evaluador_descarte_id INT UNSIGNED NULL,
    responsable_descarte_id INT UNSIGNED NULL,
    motivo_descarte VARCHAR(255) NULL,
    fecha_evaluacion_descarte DATE NULL,
    evidencia_descarte VARCHAR(255) NULL,
    notas TEXT NULL,
    firma_integridad CHAR(64) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_inventario_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    CONSTRAINT fk_inventario_evaluador_descarte FOREIGN KEY (evaluador_descarte_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE inventario_imagenes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inventario_id INT UNSIGNED NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    es_principal TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_imagen_inventario FOREIGN KEY (inventario_id) REFERENCES inventario(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE asignaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inventario_id INT UNSIGNED NOT NULL,
    colaborador_id INT UNSIGNED NOT NULL,
    usuario_asignador_id INT UNSIGNED NULL,
    audit_id BIGINT UNSIGNED NULL,
    firma_id BIGINT UNSIGNED NULL,
    fecha_asignacion DATE NOT NULL,
    fecha_devolucion DATE NULL,
    ip_asignada VARCHAR(50) NULL,
    observaciones TEXT NULL,
    estado ENUM('ACTIVA','DEVUELTA') NOT NULL DEFAULT 'ACTIVA',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_asignacion_inventario FOREIGN KEY (inventario_id) REFERENCES inventario(id),
    CONSTRAINT fk_asignacion_colaborador FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id),
    CONSTRAINT fk_asignacion_usuario FOREIGN KEY (usuario_asignador_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE necesidades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    colaborador_id INT UNSIGNED NOT NULL,
    categoria_id INT UNSIGNED NULL,
    tipo_necesidad ENUM('EQUIPO','SOFTWARE','LICENCIA') NOT NULL,
    descripcion VARCHAR(500) NOT NULL,
    justificacion TEXT NULL,
    prioridad ENUM('BAJA','MEDIA','ALTA') NOT NULL DEFAULT 'MEDIA',
    costo_estimado DECIMAL(12,2) NULL,
    costo_unitario_estimado DECIMAL(12,2) NULL,
    cantidad INT UNSIGNED NOT NULL DEFAULT 1,
    anio_objetivo SMALLINT UNSIGNED NULL,
    estado ENUM('EN_ESPERA','EN_TRAMITE','APROBADA','RECHAZADA','PENDIENTE','EN_REVISION','ATENDIDA','CANCELADA') NOT NULL DEFAULT 'EN_ESPERA',
    comentario_resolucion VARCHAR(500) NULL,
    usuario_procesador_id INT UNSIGNED NULL,
    respuesta_administrativa TEXT NULL,
    fecha_procesamiento DATETIME NULL,
    audit_id BIGINT UNSIGNED NULL,
    firma_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_necesidad_colaborador FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id),
    CONSTRAINT fk_necesidad_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) ENGINE=InnoDB;

CREATE TABLE bitacora (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NULL,
    modulo VARCHAR(80) NOT NULL,
    accion VARCHAR(80) NOT NULL,
    entidad VARCHAR(80) NULL,
    entidad_id BIGINT UNSIGNED NULL,
    descripcion VARCHAR(500) NOT NULL,
    ip VARCHAR(50) NOT NULL,
    user_agent VARCHAR(255) NULL,
    nivel ENUM('INFO','ADVERTENCIA','ERROR') NOT NULL DEFAULT 'INFO',
    resultado VARCHAR(40) NOT NULL DEFAULT 'OK',
    motivo VARCHAR(255) NULL,
    datos_anteriores_json LONGTEXT NULL,
    datos_posteriores_json LONGTEXT NULL,
    correlation_id CHAR(32) NULL,
    previous_hash CHAR(64) NULL,
    record_hash CHAR(64) NULL,
    firma_id BIGINT UNSIGNED NULL,
    fingerprint CHAR(64) NULL,
    payload_version SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bitacora_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE intentos_login (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NULL,
    identificador VARCHAR(150) NOT NULL,
    ip VARCHAR(50) NOT NULL,
    exitoso TINYINT(1) NOT NULL,
    motivo VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_intentos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE accesos_portal_colaborador (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    ip VARCHAR(50) NOT NULL,
    accessed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_portal_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ubicaciones_historial (
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
    CONSTRAINT fk_ubicacion_historial_colaborador FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE,
    CONSTRAINT fk_ubicacion_historial_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expira_at DATETIME NOT NULL,
    usado TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reset_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE noticias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NULL,
    titulo VARCHAR(180) NOT NULL,
    resumen VARCHAR(300) NOT NULL,
    contenido TEXT NOT NULL,
    imagen VARCHAR(255) NULL,
    publicada TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_noticia_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE llaves_rsa (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NULL,
    nombre VARCHAR(120) NOT NULL,
    public_key TEXT NOT NULL,
    private_key_path VARCHAR(255) NULL,
    key_store_reference VARCHAR(255) NULL,
    private_key_encrypted TINYINT(1) NOT NULL DEFAULT 1,
    algoritmo VARCHAR(40) NOT NULL DEFAULT 'RSA-SHA256',
    bits SMALLINT UNSIGNED NOT NULL DEFAULT 3072,
    fingerprint CHAR(64) NOT NULL UNIQUE,
    estado ENUM('ACTIVA','REVOCADA','REEMPLAZADA','ROTADA') NOT NULL DEFAULT 'ACTIVA',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at DATETIME NULL,
    revocation_reason VARCHAR(255) NULL,
    revoked_by INT UNSIGNED NULL,
    replaced_at DATETIME NULL,
    CONSTRAINT fk_llave_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE firmas_digitales (
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
    fingerprint CHAR(64) NULL,
    payload_version SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    audit_id BIGINT UNSIGNED NULL,
    correlation_id CHAR(32) NULL,
    payload_json LONGTEXT NULL,
    resultado_inicial ENUM('VALIDA','INVALIDA','LLAVE_REVOCADA','NO_VERIFICABLE','ERROR') NOT NULL DEFAULT 'NO_VERIFICABLE',
    resultado_verificacion ENUM('VALIDA','INVALIDA','LLAVE_REVOCADA','NO_VERIFICABLE','ERROR') NULL,
    verified_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_firma_llave FOREIGN KEY (llave_id) REFERENCES llaves_rsa(id),
    CONSTRAINT fk_firma_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE inventario_qr (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inventario_id INT UNSIGNED NOT NULL,
    token CHAR(64) NOT NULL UNIQUE,
    token_hash CHAR(64) NULL,
    payload_hash CHAR(64) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    estado ENUM('ACTIVO','REVOCADO') NOT NULL DEFAULT 'ACTIVO',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at DATETIME NULL,
    revoked_by INT UNSIGNED NULL,
    revoked_reason VARCHAR(255) NULL,
    regenerated_from_id BIGINT UNSIGNED NULL,
    last_accessed_at DATETIME NULL,
    access_count INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_qr_inventario FOREIGN KEY (inventario_id) REFERENCES inventario(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE presupuestos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(160) NOT NULL,
    tipo ENUM('ANUAL','QUINQUENAL') NOT NULL,
    anio_inicio SMALLINT UNSIGNED NOT NULL,
    anio_fin SMALLINT UNSIGNED NOT NULL,
    total_estimado DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    presupuesto_base DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    inflacion_anual DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    crecimiento_anual DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    total_quinquenal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    registros_sin_costo INT UNSIGNED NOT NULL DEFAULT 0,
    supuestos TEXT NULL,
    filtros_json TEXT NULL,
    estado ENUM('BORRADOR','APROBADO','CERRADO') NOT NULL DEFAULT 'BORRADOR',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_presupuesto_usuario FOREIGN KEY (created_by) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE presupuesto_detalles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    presupuesto_id INT UNSIGNED NOT NULL,
    categoria_id INT UNSIGNED NULL,
    necesidad_id INT UNSIGNED NULL,
    tipo_necesidad ENUM('EQUIPO','SOFTWARE','LICENCIA','RENOVACION','MANTENIMIENTO') NOT NULL,
    descripcion VARCHAR(500) NOT NULL,
    cantidad INT UNSIGNED NOT NULL DEFAULT 1,
    costo_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    costo_base DECIMAL(12,2) NULL,
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    anio SMALLINT UNSIGNED NOT NULL,
    year_index TINYINT UNSIGNED NOT NULL DEFAULT 0,
    factor_proyeccion DECIMAL(16,8) NOT NULL DEFAULT 1.00000000,
    inflacion_anual DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    crecimiento_anual DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    tiene_costo TINYINT(1) NOT NULL DEFAULT 1,
    motivo_sin_costo VARCHAR(255) NULL,
    prioridad ENUM('BAJA','MEDIA','ALTA') NULL,
    estado_solicitud VARCHAR(40) NULL,
    CONSTRAINT fk_presupuesto_detalle_presupuesto FOREIGN KEY (presupuesto_id) REFERENCES presupuestos(id) ON DELETE CASCADE,
    CONSTRAINT fk_presupuesto_detalle_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    CONSTRAINT fk_presupuesto_detalle_necesidad FOREIGN KEY (necesidad_id) REFERENCES necesidades(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE inventario_estado_historial (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inventario_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    estado_anterior VARCHAR(40) NULL,
    estado_nuevo VARCHAR(40) NOT NULL,
    motivo VARCHAR(160) NULL,
    observacion TEXT NULL,
    firma_id BIGINT UNSIGNED NULL,
    entidad_origen VARCHAR(80) NULL,
    entidad_id BIGINT UNSIGNED NULL,
    audit_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historial_estado_inventario FOREIGN KEY (inventario_id) REFERENCES inventario(id) ON DELETE CASCADE,
    CONSTRAINT fk_historial_estado_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_historial_estado_firma FOREIGN KEY (firma_id) REFERENCES firmas_digitales(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE devoluciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asignacion_id INT UNSIGNED NOT NULL,
    inventario_id INT UNSIGNED NOT NULL,
    solicitado_por INT UNSIGNED NULL,
    recibido_por INT UNSIGNED NULL,
    motivo VARCHAR(160) NOT NULL,
    estado_fisico ENUM('BUENO','REGULAR','DANADO','INCOMPLETO') NULL,
    observaciones TEXT NULL,
    evidencia VARCHAR(255) NULL,
    fecha_recepcion DATETIME NULL,
    accesorios_recibidos TEXT NULL,
    observacion_recepcion TEXT NULL,
    firma_id BIGINT UNSIGNED NULL,
    estado ENUM('PENDIENTE_REVISION','EN_REVISION','APROBADA','RECHAZADA') NOT NULL DEFAULT 'PENDIENTE_REVISION',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_devolucion_asignacion FOREIGN KEY (asignacion_id) REFERENCES asignaciones(id),
    CONSTRAINT fk_devolucion_inventario FOREIGN KEY (inventario_id) REFERENCES inventario(id),
    CONSTRAINT fk_devolucion_solicitado FOREIGN KEY (solicitado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_devolucion_recibido FOREIGN KEY (recibido_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE revisiones_tecnicas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    devolucion_id BIGINT UNSIGNED NOT NULL,
    inventario_id INT UNSIGNED NOT NULL,
    tecnico_id INT UNSIGNED NULL,
    resultado ENUM('DISPONIBLE','EN_REPARACION','DANADO','DESCARTE','DONADO','MANTENIMIENTO') NOT NULL,
    diagnostico TEXT NULL,
    opinion_tecnica TEXT NULL,
    recomendacion TEXT NULL,
    observacion_tecnica TEXT NOT NULL,
    evidencia VARCHAR(255) NULL,
    aprobador_id INT UNSIGNED NULL,
    firma_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_revision_devolucion FOREIGN KEY (devolucion_id) REFERENCES devoluciones(id) ON DELETE CASCADE,
    CONSTRAINT fk_revision_inventario FOREIGN KEY (inventario_id) REFERENCES inventario(id),
    CONSTRAINT fk_revision_tecnico FOREIGN KEY (tecnico_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_revision_firma FOREIGN KEY (firma_id) REFERENCES firmas_digitales(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE licencia_asignaciones (
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

CREATE TABLE necesidades_historial (
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
    CONSTRAINT fk_necesidad_historial_necesidad FOREIGN KEY (necesidad_id) REFERENCES necesidades(id) ON DELETE CASCADE,
    CONSTRAINT fk_necesidad_historial_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_inventario_estado ON inventario(estado);
CREATE INDEX idx_inventario_tipo ON inventario(tipo_activo);
CREATE INDEX idx_inventario_licencia_estado ON inventario(es_licencia, estado_licencia);
CREATE INDEX idx_inventario_licencia_vencimiento ON inventario(fecha_vencimiento_licencia);
CREATE INDEX idx_asignaciones_estado ON asignaciones(estado);
CREATE INDEX idx_asignaciones_usuario_asignador ON asignaciones(usuario_asignador_id);
CREATE INDEX idx_asignaciones_audit ON asignaciones(audit_id);
CREATE INDEX idx_asignaciones_firma ON asignaciones(firma_id);
CREATE INDEX idx_necesidades_estado ON necesidades(estado);
CREATE INDEX idx_necesidades_estado_formal ON necesidades(estado, prioridad);
CREATE INDEX idx_necesidades_procesador ON necesidades(usuario_procesador_id, fecha_procesamiento);
CREATE INDEX idx_bitacora_created ON bitacora(created_at);
CREATE INDEX idx_bitacora_correlation ON bitacora(correlation_id);
CREATE INDEX idx_bitacora_entidad ON bitacora(entidad, entidad_id);
CREATE INDEX idx_bitacora_hash ON bitacora(record_hash);
CREATE INDEX idx_bitacora_firma ON bitacora(firma_id);
CREATE INDEX idx_llaves_rsa_usuario_estado ON llaves_rsa(usuario_id, estado);
CREATE INDEX idx_llaves_rsa_fingerprint ON llaves_rsa(fingerprint);
CREATE INDEX idx_firmas_digitales_audit_id ON firmas_digitales(audit_id);
CREATE INDEX idx_firmas_digitales_resultado ON firmas_digitales(resultado_verificacion);
CREATE INDEX idx_firmas_digitales_fingerprint ON firmas_digitales(fingerprint);
CREATE INDEX idx_qr_inventario ON inventario_qr(inventario_id);
CREATE UNIQUE INDEX idx_qr_token_hash ON inventario_qr(token_hash);
CREATE INDEX idx_qr_estado ON inventario_qr(inventario_id, estado, activo);
CREATE INDEX idx_qr_acceso ON inventario_qr(last_accessed_at);
CREATE INDEX idx_qr_regenerado_desde ON inventario_qr(regenerated_from_id);
CREATE INDEX idx_historial_inventario ON inventario_estado_historial(inventario_id, created_at);
CREATE INDEX idx_historial_estado_origen ON inventario_estado_historial(entidad_origen, entidad_id);
CREATE INDEX idx_devoluciones_estado ON devoluciones(estado);
CREATE INDEX idx_devoluciones_firma ON devoluciones(firma_id);
CREATE INDEX idx_revision_aprobador ON revisiones_tecnicas(aprobador_id);
CREATE INDEX idx_licencia_asignaciones_estado ON licencia_asignaciones(inventario_id, estado);
CREATE INDEX idx_licencia_asignaciones_colaborador ON licencia_asignaciones(colaborador_id, estado);
CREATE INDEX idx_ubicaciones_historial_colaborador ON ubicaciones_historial(colaborador_id, created_at);
CREATE INDEX idx_ubicaciones_historial_audit ON ubicaciones_historial(audit_id);
CREATE INDEX idx_necesidades_historial_firma ON necesidades_historial(firma_id);
CREATE INDEX idx_necesidades_historial_audit ON necesidades_historial(audit_id);
CREATE INDEX idx_presupuesto_detalles_anio ON presupuesto_detalles(presupuesto_id, anio);
CREATE INDEX idx_presupuesto_detalles_costo ON presupuesto_detalles(presupuesto_id, tiene_costo);
CREATE INDEX idx_presupuesto_detalles_filtros ON presupuesto_detalles(anio, categoria_id, tipo_necesidad, prioridad, estado_solicitud);

-- Datos semilla: colaboradores
INSERT INTO colaboradores
(nombres, apellidos, identificacion, departamento, ubicacion, direccion, telefono, email, foto, activo)
VALUES
('Sofía', 'Martínez', '8-999-1001', 'Tecnología', 'Edificio 303 - Oficina 12', 'Campus Central', '6000-1001', 'sofia.martinez@cmdb.local', NULL, 1),
('Carlos', 'Gómez', '8-999-1002', 'Finanzas', 'Edificio 201 - Oficina 5', 'Campus Central', '6000-1002', 'carlos.gomez@cmdb.local', NULL, 1),
('Laura', 'Vega', '8-999-1003', 'Recursos Humanos', 'Casa 257', 'Vía principal', '6000-1003', 'laura.vega@cmdb.local', NULL, 1);

-- Datos semilla: usuarios. Las contraseñas solo se guardan como Hash BCRYPT.
INSERT INTO usuarios (colaborador_id, nombre_usuario, email, password_hash, rol, activo, estado_cuenta, intentos_fallidos)
VALUES
(NULL, 'admin', 'admin@cmdb.local', '$2y$12$BoeoKWLXHLR6uOw43upx/.UE7v5jP4X2hhUhrABFMq/gER8XYQ.cS', 'ADMIN', 1, 'ACTIVO', 0),
(NULL, 'operador', 'operador@cmdb.local', '$2y$12$Q9DAchW14gC1HJzLGrrfJezVjS7HOlaoHFCHyyJer.4c8XeVenht2', 'OPERADOR', 1, 'ACTIVO', 0),
(1, 'sofia', 'sofia.martinez@cmdb.local', '$2y$12$/SgfYbw4agT2YOEDLGPsouUxXXisi8fP.8htY6OuwZ/HI27IYY2eG', 'COLABORADOR', 1, 'ACTIVO', 0);

-- Categorías obligatorias del CMDB
INSERT INTO categorias (nombre, tipo, descripcion, activo) VALUES
('Hardware', 'HARDWARE', 'Dispositivos físicos generales.', 1),
('Software', 'SOFTWARE', 'Aplicaciones y sistemas operativos.', 1),
('Equipo de Red', 'HARDWARE', 'Switches, routers, puntos de acceso y firewalls.', 1),
('Equipo de Cómputo', 'HARDWARE', 'Laptop, desktop, monitor y periféricos.', 1),
('Equipo de Telefonía', 'HARDWARE', 'Teléfonos IP, móviles y accesorios.', 1),
('Licencias de Software', 'SOFTWARE', 'Licencias no asignadas y renovaciones.', 1);

-- Activos con firma HMAC (serie + tipo + estado + fecha ingreso)
INSERT INTO inventario
(categoria_id, codigo_activo, nombre, tipo_activo, subcategoria, marca, modelo, serie, costo, fecha_ingreso, vida_util_meses, estado,
 es_licencia, clave_licencia, cantidad, responsable_donacion, fecha_donacion, notas, firma_integridad, activo)
VALUES
(4, 'ACT-0001', 'Laptop de Desarrollo', 'HARDWARE', 'Laptop', 'Acer', 'TravelMate P2', 'LAP-ACER-001', 950.00, '2024-02-15', 36, 'ASIGNADO', 0, NULL, 1, NULL, NULL, 'Equipo de desarrollo entregado a Tecnología.', '7fe2943324ec954e94daf52cf442a26902f80a58e41bf8116ae3486777d44a76', 1),
(5, 'ACT-0002', 'Teléfono IP de Inventario', 'HARDWARE', 'Teléfono IP', 'Yealink', 'T31P', 'TEL-IP-001', 78.00, '2024-09-01', 48, 'DISPONIBLE', 0, NULL, 1, NULL, NULL, 'Disponible para nueva asignación.', 'e4f6886414f7902a74cb7248e52bb5ea81fb01586c0a1496382d7b88c718c37b', 1),
(6, 'LIC-0001', 'Microsoft 365 Business Standard', 'SOFTWARE', 'Licencia', 'Microsoft', 'M365', 'LIC-O365-001', 125.00, '2025-01-10', 12, 'DISPONIBLE', 1, NULL, 1, NULL, NULL, 'Licencia disponible sin asignar; clave operativa no incluida en datos semilla.', 'ae9348370b4d4ffa12627ace15cdb68fbcbd4332bd5194f07cb33cb9584eac1f', 1),
(2, 'SW-0001', 'Antivirus Corporativo', 'SOFTWARE', 'Seguridad', 'Bitdefender', 'GravityZone', 'SW-ANT-001', 420.00, '2023-10-20', 12, 'DISPONIBLE', 0, NULL, 20, NULL, NULL, 'Suscripción anual de antivirus.', '669e4a38db58bde25b0701f5b8a342761785b43565e5bce23afdbc02807beac8', 1),
(3, 'NET-0001', 'Switch de Acceso', 'HARDWARE', 'Switch', 'Cisco', 'CBS250', 'SWT-CISCO-001', 520.00, '2022-08-01', 60, 'MANTENIMIENTO', 0, NULL, 1, NULL, NULL, 'Equipo de red en mantenimiento preventivo.', 'd23152cc48bab2d0c638d8190acc2c165e42349511dad2b74b5e1e4d0f565865', 1);

INSERT INTO asignaciones (inventario_id, colaborador_id, fecha_asignacion, ip_asignada, observaciones, estado)
VALUES (1, 1, '2024-03-01', '192.168.10.25', 'Laptop con cargador y maletín.', 'ACTIVA');

INSERT INTO necesidades (colaborador_id, categoria_id, tipo_necesidad, descripcion, prioridad, estado)
VALUES
(2, 5, 'EQUIPO', 'Se requiere un teléfono IP para el nuevo puesto de Finanzas.', 'MEDIA', 'PENDIENTE'),
(3, 6, 'LICENCIA', 'Licencia de software de ofimática para colaboradora nueva.', 'ALTA', 'EN_REVISION');

INSERT INTO noticias (usuario_id, titulo, resumen, contenido, publicada)
VALUES
(1, 'Por qué una CMDB evita pérdidas de activos', 'Una CMDB mantiene trazabilidad de responsables, estados y ubicación de los equipos.', 'Registrar cada activo, custodio y estado permite conocer qué equipo tiene cada colaborador y reduce el riesgo de pérdida, duplicidad o compras innecesarias.', 1),
(1, 'Importancia de renovar equipos cerca de depreciación', 'La depreciación permite planificar mantenimiento, renovación y presupuesto tecnológico.', 'El módulo de alertas identifica equipos cuya vida útil se acerca a su fecha límite para tomar decisiones preventivas y mantener la continuidad operativa.', 1);

-- Datos realistas adicionales para demostración
INSERT INTO colaboradores
(nombres, apellidos, identificacion, departamento, ubicacion, direccion, telefono, email, foto, activo)
VALUES
('Ana', 'Rodríguez', '8-999-1004', 'Comunicaciones', 'Edificio 102 - Oficina 8', 'Campus Central', '6000-1004', 'ana.rodriguez@cmdb.local', NULL, 1),
('Miguel', 'Ríos', '8-999-1005', 'Infraestructura', 'Edificio 303 - Data Center', 'Campus Central', '6000-1005', 'miguel.rios@cmdb.local', NULL, 1),
('Patricia', 'Castillo', '8-999-1006', 'Compras', 'Edificio 201 - Oficina 11', 'Campus Central', '6000-1006', 'patricia.castillo@cmdb.local', NULL, 1),
('Roberto', 'Núñez', '8-999-1007', 'Mesa de Ayuda', 'Edificio 303 - Piso 1', 'Campus Central', '6000-1007', 'roberto.nunez@cmdb.local', NULL, 1),
('Valeria', 'Torres', '8-999-1008', 'Dirección Académica', 'Edificio 101 - Dirección', 'Campus Central', '6000-1008', 'valeria.torres@cmdb.local', NULL, 1),
('Javier', 'Chen', '8-999-1009', 'Laboratorios', 'Edificio 405 - Laboratorio 2', 'Campus Central', '6000-1009', 'javier.chen@cmdb.local', NULL, 1),
('Daniela', 'Morales', '8-999-1010', 'Biblioteca', 'Biblioteca Central - Atención', 'Campus Central', '6000-1010', 'daniela.morales@cmdb.local', NULL, 1),
('Fernando', 'Batista', '8-999-1011', 'Investigación', 'Edificio 501 - Oficina 4', 'Campus Central', '6000-1011', 'fernando.batista@cmdb.local', NULL, 1);

INSERT INTO usuarios (colaborador_id, nombre_usuario, email, password_hash, rol, activo, estado_cuenta, intentos_fallidos)
VALUES
((SELECT id FROM colaboradores WHERE email = 'ana.rodriguez@cmdb.local'), 'ana.rodriguez', 'ana.rodriguez@cmdb.local', '$2y$12$/SgfYbw4agT2YOEDLGPsouUxXXisi8fP.8htY6OuwZ/HI27IYY2eG', 'COLABORADOR', 1, 'ACTIVO', 0),
((SELECT id FROM colaboradores WHERE email = 'miguel.rios@cmdb.local'), 'miguel.rios', 'miguel.rios@cmdb.local', '$2y$12$/SgfYbw4agT2YOEDLGPsouUxXXisi8fP.8htY6OuwZ/HI27IYY2eG', 'COLABORADOR', 1, 'ACTIVO', 0),
((SELECT id FROM colaboradores WHERE email = 'roberto.nunez@cmdb.local'), 'soporte', 'soporte@cmdb.local', '$2y$12$Q9DAchW14gC1HJzLGrrfJezVjS7HOlaoHFCHyyJer.4c8XeVenht2', 'OPERADOR', 1, 'ACTIVO', 0);

INSERT INTO categorias (nombre, tipo, descripcion, activo) VALUES
('Servidores', 'HARDWARE', 'Servidores físicos y equipos de centro de datos.', 1),
('Periféricos', 'HARDWARE', 'Monitores, UPS, teclados, impresoras y accesorios.', 1),
('Seguridad Informática', 'SOFTWARE', 'Herramientas de protección, antivirus y monitoreo.', 1),
('Sistemas Operativos', 'SOFTWARE', 'Sistemas operativos de escritorio y servidor.', 1),
('Herramientas de Diseño', 'SOFTWARE', 'Software de diseño, edición y producción multimedia.', 1);

INSERT INTO inventario
(categoria_id, codigo_activo, nombre, tipo_activo, subcategoria, marca, modelo, serie, costo, fecha_ingreso, vida_util_meses, estado,
 imagen_principal, thumbnail, es_licencia, clave_licencia, proveedor_licencia, url_licencia, fecha_vencimiento_licencia,
 observaciones_licencia, cantidad, responsable_donacion, beneficiario_donacion, evidencia_donacion, observacion_donacion,
 fecha_donacion, observacion_tecnica_descarte, evaluador_descarte_id, fecha_evaluacion_descarte, evidencia_descarte,
 notas, firma_integridad, activo)
VALUES
((SELECT id FROM categorias WHERE nombre = 'Equipo de Cómputo'), 'ACT-0003', 'Laptop Administrativa Lenovo ThinkPad T14', 'HARDWARE', 'Laptop', 'Lenovo', 'ThinkPad T14 Gen 3', 'LAP-LEN-T14-002', 1180.00, '2024-05-18', 36, 'ASIGNADO', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Asignada a Finanzas para gestión presupuestaria y reportes.', 'cc7cf4567ccc9f8e036473f95d72f09addf553be3da4b9f6f5624ab884c800ab', 1),
((SELECT id FROM categorias WHERE nombre = 'Equipo de Cómputo'), 'ACT-0004', 'Desktop HP EliteDesk 800', 'HARDWARE', 'Desktop', 'HP', 'EliteDesk 800 G6', 'DESK-HP-800-003', 740.00, '2025-02-12', 48, 'DISPONIBLE', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Equipo preparado para nuevo puesto administrativo.', '870b944173188fc88cd45e36c61ff1f4d1c4711c45a491bc21e79fe7bd983fa1', 1),
((SELECT id FROM categorias WHERE nombre = 'Periféricos'), 'ACT-0005', 'Monitor Dell 24 pulgadas', 'HARDWARE', 'Monitor', 'Dell', 'P2422H', 'MON-DELL-2422-004', 210.00, '2023-11-03', 48, 'ASIGNADO', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Monitor externo para puesto de Recursos Humanos.', 'ec61a3a884b24ff968f755bbae6f95599bce52ee125b898157802cd94709dfba', 1),
((SELECT id FROM categorias WHERE nombre = 'Equipo de Red'), 'NET-0002', 'Firewall perimetral Fortinet', 'HARDWARE', 'Firewall', 'Fortinet', 'FortiGate 60F', 'FW-FGT-60F-002', 1320.00, '2022-06-20', 60, 'MANTENIMIENTO', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'En mantenimiento por actualización de firmware y revisión de reglas.', 'c88378ec600d5858aa6ca1cd0bc6c4d6d7e939e9afd3be81e2ce141b89dc84be', 1),
((SELECT id FROM categorias WHERE nombre = 'Equipo de Red'), 'NET-0003', 'Punto de acceso WiFi 6', 'HARDWARE', 'Access Point', 'Ubiquiti', 'UniFi U6 Lite', 'AP-UBQ-U6-003', 145.00, '2025-03-15', 48, 'DISPONIBLE', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Disponible para ampliar cobertura en Biblioteca.', '27e3d57152a0ee29fca6c802fe663fe7aef64645287293c6c5d09027a002e0f0', 1),
((SELECT id FROM categorias WHERE nombre = 'Equipo de Telefonía'), 'TEL-0003', 'Teléfono IP Cisco 8841', 'HARDWARE', 'Teléfono IP', 'Cisco', '8841', 'TEL-CISCO-8841-003', 165.00, '2024-10-05', 48, 'ASIGNADO', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Teléfono asignado a Comunicaciones.', '8d802deb7e88ff6bdfee3a9633055bd1502ca2686f33ef693531d0e1de012ade', 1),
((SELECT id FROM categorias WHERE nombre = 'Servidores'), 'SRV-0001', 'Servidor Dell PowerEdge R450', 'HARDWARE', 'Servidor', 'Dell', 'PowerEdge R450', 'SRV-DELL-R450-001', 4850.00, '2021-09-10', 72, 'MANTENIMIENTO', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Servidor de virtualización en ventana de mantenimiento preventivo.', 'dc051be1d32b1971bd3e9e11fc78cc66b862f510dc727799764e433e35c8172c', 1),
((SELECT id FROM categorias WHERE nombre = 'Herramientas de Diseño'), 'LIC-0002', 'Adobe Creative Cloud Equipos', 'SOFTWARE', 'Licencia', 'Adobe', 'Creative Cloud Teams', 'LIC-ADOBE-CC-002', 720.00, '2025-01-02', 12, 'DISPONIBLE', NULL, NULL, 1, NULL, 'Adobe', 'https://adminconsole.adobe.com/', '2026-01-02', 'Licencia anual compartida por Comunicaciones y Diseño.', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Control por cupos; clave operativa fuera de datos semilla.', '088e0702604e2319d319cf7ac24ad88c94535a32ec34aceb1784a2c86e582d7e', 1),
((SELECT id FROM categorias WHERE nombre = 'Software'), 'LIC-0003', 'AutoCAD LT', 'SOFTWARE', 'Licencia', 'Autodesk', 'AutoCAD LT 2026', 'LIC-AUTOCAD-003', 390.00, '2025-04-01', 12, 'DISPONIBLE', NULL, NULL, 1, NULL, 'Autodesk', 'https://manage.autodesk.com/', '2026-04-01', 'Licencias para infraestructura y proyectos técnicos.', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Asignar solo a personal técnico; clave operativa fuera de datos semilla.', 'afb36aba593fc04a783fb91bd62a53d2f4826120e40a654c8cf7f40700680d15', 1),
((SELECT id FROM categorias WHERE nombre = 'Seguridad Informática'), 'LIC-0004', 'ESET Endpoint Security', 'SOFTWARE', 'Licencia', 'ESET', 'Endpoint Security', 'LIC-ESET-004', 980.00, '2024-12-15', 12, 'DISPONIBLE', NULL, NULL, 1, NULL, 'ESET', 'https://eba.eset.com/', '2026-12-15', 'Cupos para estaciones administrativas y laboratorios.', 40, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Renovación anual de seguridad endpoint; clave operativa fuera de datos semilla.', '4710a39f11becc9af669bfbad482be82b6e6fbdeb0e3c7bb0188739de27c8f95', 1),
((SELECT id FROM categorias WHERE nombre = 'Software'), 'SW-0002', 'Jira Service Management', 'SOFTWARE', 'Mesa de ayuda', 'Atlassian', 'Cloud Standard', 'SW-JIRA-002', 540.00, '2025-06-01', 12, 'DISPONIBLE', NULL, NULL, 1, NULL, 'Atlassian', 'https://admin.atlassian.com/', '2026-06-01', 'Licencia para gestión de incidentes de Mesa de Ayuda.', 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Uso proyectado para mesa de ayuda; clave operativa fuera de datos semilla.', 'c09d257092bab6609fe017a8e15937891dbf5647ed01ec08613826230819614d', 1),
((SELECT id FROM categorias WHERE nombre = 'Equipo de Cómputo'), 'ACT-0006', 'Tablet Samsung Galaxy Tab A8', 'HARDWARE', 'Tablet', 'Samsung', 'Galaxy Tab A8', 'TAB-SAM-A8-006', 230.00, '2021-03-12', 36, 'DONADO', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, 'Patricia Castillo', 'Programa de Alfabetización Digital', 'ACTA-DON-2026-004', 'Donación aprobada por comité de activos; equipo funcional con desgaste normal.', '2026-06-15', NULL, NULL, NULL, NULL, 'Donado por renovación tecnológica.', '5550883ca0ea49a3c3720712209ab9718c2e7dab45358a7f6694c67c04224145', 0),
((SELECT id FROM categorias WHERE nombre = 'Periféricos'), 'ACT-0007', 'UPS APC Back-UPS 750', 'HARDWARE', 'UPS', 'APC', 'Back-UPS 750', 'UPS-APC-750-007', 115.00, '2020-08-20', 48, 'DESCARTE', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'Batería agotada, carcasa deteriorada y costo de reparación superior al reemplazo.', 1, '2026-05-20', 'INF-DESC-2026-002', 'Pendiente retiro por proveedor autorizado.', '75001e1b2c60e66f80b45eac3826f05b3d0dfb57fa686fad647c2d7a0b89a634', 1);

INSERT INTO asignaciones (inventario_id, colaborador_id, fecha_asignacion, ip_asignada, observaciones, estado)
VALUES
((SELECT id FROM inventario WHERE codigo_activo = 'ACT-0003'), (SELECT id FROM colaboradores WHERE email = 'carlos.gomez@cmdb.local'), '2024-06-01', '192.168.10.45', 'Laptop con cargador USB-C y mouse inalámbrico.', 'ACTIVA'),
((SELECT id FROM inventario WHERE codigo_activo = 'ACT-0005'), (SELECT id FROM colaboradores WHERE email = 'laura.vega@cmdb.local'), '2023-11-10', NULL, 'Monitor instalado en puesto de Recursos Humanos.', 'ACTIVA'),
((SELECT id FROM inventario WHERE codigo_activo = 'TEL-0003'), (SELECT id FROM colaboradores WHERE email = 'ana.rodriguez@cmdb.local'), '2024-10-10', '10.20.5.34', 'Teléfono IP con extensión 2204.', 'ACTIVA');

INSERT INTO licencia_asignaciones (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, fecha_fin, estado, observaciones)
VALUES
((SELECT id FROM inventario WHERE codigo_activo = 'LIC-0002'), (SELECT id FROM colaboradores WHERE email = 'ana.rodriguez@cmdb.local'), 1, 1, '2025-01-10', NULL, 'ACTIVA', 'Suite completa para campañas institucionales.'),
((SELECT id FROM inventario WHERE codigo_activo = 'LIC-0002'), (SELECT id FROM colaboradores WHERE email = 'valeria.torres@cmdb.local'), 1, 1, '2025-02-03', NULL, 'ACTIVA', 'Licencia para revisión de material gráfico.'),
((SELECT id FROM inventario WHERE codigo_activo = 'LIC-0003'), (SELECT id FROM colaboradores WHERE email = 'miguel.rios@cmdb.local'), 1, 1, '2025-04-05', NULL, 'ACTIVA', 'Diseños de infraestructura y planos técnicos.'),
((SELECT id FROM inventario WHERE codigo_activo = 'LIC-0004'), (SELECT id FROM colaboradores WHERE email = 'javier.chen@cmdb.local'), 1, 12, '2025-01-20', NULL, 'ACTIVA', 'Cupos instalados en laboratorio 2.'),
((SELECT id FROM inventario WHERE codigo_activo = 'SW-0002'), (SELECT id FROM colaboradores WHERE email = 'roberto.nunez@cmdb.local'), 1, 3, '2025-06-05', NULL, 'ACTIVA', 'Agentes de mesa de ayuda.');

INSERT INTO necesidades (colaborador_id, categoria_id, tipo_necesidad, descripcion, prioridad, costo_estimado, estado, comentario_resolucion)
VALUES
((SELECT id FROM colaboradores WHERE email = 'daniela.morales@cmdb.local'), (SELECT id FROM categorias WHERE nombre = 'Equipo de Cómputo'), 'EQUIPO', 'Equipo de escritorio para estación de autopréstamo en biblioteca.', 'ALTA', 780.00, 'PENDIENTE', NULL),
((SELECT id FROM colaboradores WHERE email = 'javier.chen@cmdb.local'), (SELECT id FROM categorias WHERE nombre = 'Equipo de Red'), 'EQUIPO', 'Punto de acceso adicional para mejorar cobertura del laboratorio 2.', 'MEDIA', 160.00, 'EN_REVISION', 'Validar canalización y punto de red disponible.'),
((SELECT id FROM colaboradores WHERE email = 'ana.rodriguez@cmdb.local'), (SELECT id FROM categorias WHERE nombre = 'Herramientas de Diseño'), 'LICENCIA', 'Licencia adicional de diseño para apoyo temporal de campaña institucional.', 'MEDIA', 720.00, 'PENDIENTE', NULL),
((SELECT id FROM colaboradores WHERE email = 'miguel.rios@cmdb.local'), (SELECT id FROM categorias WHERE nombre = 'Seguridad Informática'), 'SOFTWARE', 'Herramienta de monitoreo para alertas de disponibilidad de servicios críticos.', 'ALTA', 1250.00, 'EN_REVISION', 'Comparar opciones cloud y on-premise.');

INSERT INTO inventario_estado_historial (inventario_id, usuario_id, estado_anterior, estado_nuevo, motivo, observacion)
SELECT id, 1, NULL, estado, 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.'
FROM inventario
WHERE codigo_activo IN ('ACT-0003','ACT-0004','ACT-0005','NET-0002','NET-0003','TEL-0003','SRV-0001','LIC-0002','LIC-0003','LIC-0004','SW-0002','ACT-0006','ACT-0007');

INSERT INTO presupuestos (nombre, tipo, anio_inicio, anio_fin, total_estimado, estado, created_by)
VALUES
('Presupuesto tecnológico anual 2026', 'ANUAL', 2026, 2026, 2910.00, 'BORRADOR', 1),
('Plan quinquenal de renovación tecnológica 2026-2030', 'QUINQUENAL', 2026, 2030, 21850.00, 'BORRADOR', 1);

INSERT INTO presupuesto_detalles (presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, subtotal, anio)
VALUES
((SELECT id FROM presupuestos WHERE nombre = 'Presupuesto tecnológico anual 2026'), (SELECT id FROM categorias WHERE nombre = 'Equipo de Cómputo'), NULL, 'EQUIPO', 'Reposición de equipos administrativos de alto uso.', 2, 780.00, 1560.00, 2026),
((SELECT id FROM presupuestos WHERE nombre = 'Presupuesto tecnológico anual 2026'), (SELECT id FROM categorias WHERE nombre = 'Equipo de Red'), NULL, 'EQUIPO', 'Ampliación de cobertura inalámbrica en laboratorios.', 2, 160.00, 320.00, 2026),
((SELECT id FROM presupuestos WHERE nombre = 'Presupuesto tecnológico anual 2026'), (SELECT id FROM categorias WHERE nombre = 'Seguridad Informática'), NULL, 'SOFTWARE', 'Renovación y monitoreo de seguridad endpoint.', 1, 1030.00, 1030.00, 2026),
((SELECT id FROM presupuestos WHERE nombre = 'Plan quinquenal de renovación tecnológica 2026-2030'), (SELECT id FROM categorias WHERE nombre = 'Equipo de Cómputo'), NULL, 'EQUIPO', 'Renovación progresiva de laptops y desktops.', 20, 850.00, 17000.00, 2027),
((SELECT id FROM presupuestos WHERE nombre = 'Plan quinquenal de renovación tecnológica 2026-2030'), (SELECT id FROM categorias WHERE nombre = 'Servidores'), NULL, 'EQUIPO', 'Reserva para actualización de infraestructura de virtualización.', 1, 4850.00, 4850.00, 2028);

INSERT INTO noticias (usuario_id, titulo, resumen, contenido, publicada)
VALUES
(1, 'Buenas prácticas para la devolución de equipos', 'La revisión técnica evita reasignar equipos con fallas ocultas.', 'Antes de volver a marcar un equipo como disponible, registre motivo de devolución, estado físico y observación técnica. Esto mantiene trazabilidad y reduce incidentes posteriores.', 1),
(1, 'Control de licencias por cupos', 'Asignar cupos permite medir uso real y planificar renovaciones.', 'Las licencias con cantidad superior a uno deben administrarse por cupos asignados a colaboradores o áreas responsables. La CMDB ayuda a comparar cupos disponibles contra demanda.', 1),
(1, 'QR para activos críticos', 'El QR facilita identificar activos sin exponer información sensible.', 'Cada activo puede consultarse desde su detalle interno mediante QR. El código no debe contener claves de licencia ni datos confidenciales.', 1);

INSERT INTO bitacora (usuario_id, modulo, accion, descripcion, ip, nivel)
VALUES
(1, 'SISTEMA', 'SEMILLA', 'Base de datos CMDB creada con datos de ejemplo.', '127.0.0.1', 'INFO'),
(1, 'SISTEMA', 'SEMILLA_REALISTA', 'Datos realistas adicionales cargados para demostración.', '127.0.0.1', 'INFO');

-- Credenciales de prueba:

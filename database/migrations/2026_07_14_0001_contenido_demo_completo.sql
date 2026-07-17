-- ============================================================================
-- CMDB Integral - Contenido demo completo y conectado
-- Fecha: 2026-07-14
--
-- Ejecutar DESPUES de:
--   database/migrations/2026_07_13_0013_asignador_validaciones.sql
--
-- No borra informacion, no cambia credenciales y no contiene claves reales de
-- licencia. Esta pensado para enriquecer una base de pruebas ya instalada.
-- ============================================================================

USE cmdb_integral;

START TRANSACTION;

SET @admin_id := COALESCE((SELECT id FROM usuarios WHERE nombre_usuario = 'admin' LIMIT 1), 1);
SET @operador_id := COALESCE((SELECT id FROM usuarios WHERE nombre_usuario = 'operador' LIMIT 1), @admin_id);
SET @soporte_id := COALESCE((SELECT id FROM usuarios WHERE nombre_usuario = 'soporte' LIMIT 1), @operador_id);

-- --------------------------------------------------------------------------
-- Colaboradores y usuarios de prueba
-- --------------------------------------------------------------------------

INSERT IGNORE INTO colaboradores
(nombres, apellidos, identificacion, departamento, ubicacion, direccion, telefono, email, foto, activo)
VALUES
('Mariana', 'Pérez', '8-999-1012', 'Registro Académico', 'Edificio 101 - Ventanilla 3', 'Campus Central', '6000-1012', 'mariana.perez@cmdb.local', NULL, 1),
('Luis', 'Santamaría', '8-999-1013', 'Contabilidad', 'Edificio 201 - Oficina 7', 'Campus Central', '6000-1013', 'luis.santamaria@cmdb.local', NULL, 1),
('Elena', 'Vargas', '8-999-1014', 'Soporte Técnico', 'Edificio 303 - Taller', 'Campus Central', '6000-1014', 'elena.vargas@cmdb.local', NULL, 1),
('Andrés', 'Quintero', '8-999-1015', 'Laboratorios', 'Edificio 405 - Laboratorio 1', 'Campus Central', '6000-1015', 'andres.quintero@cmdb.local', NULL, 1),
('Mónica', 'Herrera', '8-999-1016', 'Extensión Universitaria', 'Sede Oeste - Coordinación', 'Sede Oeste', '6000-1016', 'monica.herrera@cmdb.local', NULL, 1),
('Gabriel', 'Sosa', '8-999-1017', 'Seguridad Física', 'Garita Principal', 'Campus Central', '6000-1017', 'gabriel.sosa@cmdb.local', NULL, 1),
('Isabel', 'Navarro', '8-999-1018', 'Admisiones', 'Edificio 101 - Oficina 2', 'Campus Central', '6000-1018', 'isabel.navarro@cmdb.local', NULL, 1),
('Tomás', 'Arias', '8-999-1019', 'Investigación', 'Edificio 501 - Laboratorio IoT', 'Campus Central', '6000-1019', 'tomas.arias@cmdb.local', NULL, 1),
('Camila', 'Paredes', '8-999-1020', 'Comunicaciones', 'Edificio 102 - Estudio Multimedia', 'Campus Central', '6000-1020', 'camila.paredes@cmdb.local', NULL, 1),
('Rafael', 'Méndez', '8-999-1021', 'Infraestructura', 'Edificio 303 - NOC', 'Campus Central', '6000-1021', 'rafael.mendez@cmdb.local', NULL, 1);

INSERT IGNORE INTO usuarios
(colaborador_id, nombre_usuario, email, password_hash, rol, activo, estado_cuenta, intentos_fallidos)
VALUES
((SELECT id FROM colaboradores WHERE email = 'elena.vargas@cmdb.local'), 'elena.vargas', 'elena.vargas@cmdb.local', '$2y$12$Q9DAchW14gC1HJzLGrrfJezVjS7HOlaoHFCHyyJer.4c8XeVenht2', 'OPERADOR', 1, 'ACTIVO', 0),
((SELECT id FROM colaboradores WHERE email = 'mariana.perez@cmdb.local'), 'mariana.perez', 'mariana.perez@cmdb.local', '$2y$12$/SgfYbw4agT2YOEDLGPsouUxXXisi8fP.8htY6OuwZ/HI27IYY2eG', 'COLABORADOR', 1, 'ACTIVO', 0),
((SELECT id FROM colaboradores WHERE email = 'andres.quintero@cmdb.local'), 'andres.quintero', 'andres.quintero@cmdb.local', '$2y$12$/SgfYbw4agT2YOEDLGPsouUxXXisi8fP.8htY6OuwZ/HI27IYY2eG', 'COLABORADOR', 1, 'ACTIVO', 0),
((SELECT id FROM colaboradores WHERE email = 'rafael.mendez@cmdb.local'), 'rafael.mendez', 'rafael.mendez@cmdb.local', '$2y$12$/SgfYbw4agT2YOEDLGPsouUxXXisi8fP.8htY6OuwZ/HI27IYY2eG', 'COLABORADOR', 1, 'ACTIVO', 0);

SET @elena_id := COALESCE((SELECT id FROM usuarios WHERE nombre_usuario = 'elena.vargas' LIMIT 1), @soporte_id);
SET @mariana_user_id := COALESCE((SELECT id FROM usuarios WHERE nombre_usuario = 'mariana.perez' LIMIT 1), @admin_id);
SET @andres_user_id := COALESCE((SELECT id FROM usuarios WHERE nombre_usuario = 'andres.quintero' LIMIT 1), @admin_id);
SET @rafael_user_id := COALESCE((SELECT id FROM usuarios WHERE nombre_usuario = 'rafael.mendez' LIMIT 1), @admin_id);

-- --------------------------------------------------------------------------
-- Catalogos adicionales
-- --------------------------------------------------------------------------

INSERT IGNORE INTO categorias (nombre, tipo, descripcion, activo)
VALUES
('Impresión y Digitalización', 'HARDWARE', 'Impresoras, escáneres y equipos de captura documental.', 1),
('Audiovisual', 'HARDWARE', 'Proyectores, cámaras, micrófonos y equipos multimedia.', 1),
('Energía y Respaldo', 'HARDWARE', 'UPS, baterías y protección eléctrica para equipos críticos.', 1),
('Servicios Cloud', 'SOFTWARE', 'Suscripciones SaaS, colaboración y servicios en nube.', 1),
('Base de Datos', 'SOFTWARE', 'Motores, herramientas y servicios de administración de datos.', 1);

-- --------------------------------------------------------------------------
-- Inventario de hardware con estados variados
-- --------------------------------------------------------------------------

INSERT IGNORE INTO inventario
(categoria_id, codigo_activo, nombre, tipo_activo, subcategoria, marca, modelo, serie, costo, fecha_ingreso, vida_util_meses, estado,
 imagen_principal, thumbnail, es_licencia, cantidad, responsable_donacion, beneficiario_donacion, evidencia_donacion, observacion_donacion,
 fecha_donacion, valor_donacion, autorizador_donacion_id, observacion_tecnica_descarte, evaluador_descarte_id, responsable_descarte_id,
 motivo_descarte, fecha_evaluacion_descarte, evidencia_descarte, notas, firma_integridad, activo)
VALUES
((SELECT id FROM categorias WHERE nombre = 'Equipo de Cómputo'), 'ACT-0008', 'Laptop Dell Latitude 5440', 'HARDWARE', 'Laptop', 'Dell', 'Latitude 5440', 'LAP-DELL-5440-008', 1125.00, '2025-08-12', 36, 'ASIGNADO', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Equipo asignado a Registro Académico para atención de ventanilla.', SHA2('LAP-DELL-5440-008|HARDWARE|ASIGNADO|2025-08-12|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Equipo de Cómputo'), 'ACT-0009', 'Desktop Lenovo ThinkCentre M70q', 'HARDWARE', 'Mini PC', 'Lenovo', 'ThinkCentre M70q', 'DESK-LEN-M70Q-009', 690.00, '2025-10-02', 48, 'DISPONIBLE', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Equipo listo para nuevo puesto administrativo o reemplazo rápido.', SHA2('DESK-LEN-M70Q-009|HARDWARE|DISPONIBLE|2025-10-02|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Impresión y Digitalización'), 'ACT-0010', 'Impresora multifuncional Brother MFC-L8900CDW', 'HARDWARE', 'Impresora multifuncional', 'Brother', 'MFC-L8900CDW', 'PRN-BRO-L8900-010', 620.00, '2022-04-18', 48, 'EN_REPARACION', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'En reparación por atasco recurrente en unidad fusora.', SHA2('PRN-BRO-L8900-010|HARDWARE|EN_REPARACION|2022-04-18|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Audiovisual'), 'ACT-0011', 'Proyector Epson PowerLite 2250U', 'HARDWARE', 'Proyector', 'Epson', 'PowerLite 2250U', 'PROJ-EPS-2250-011', 980.00, '2024-01-29', 60, 'ASIGNADO', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Asignado al estudio multimedia para capacitaciones y transmisiones.', SHA2('PROJ-EPS-2250-011|HARDWARE|ASIGNADO|2024-01-29|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Audiovisual'), 'ACT-0012', 'Cámara Sony Alpha ZV-E10', 'HARDWARE', 'Cámara', 'Sony', 'Alpha ZV-E10', 'CAM-SONY-ZVE10-012', 735.00, '2025-05-06', 48, 'DISPONIBLE', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Disponible para producción de material académico y comunicados.', SHA2('CAM-SONY-ZVE10-012|HARDWARE|DISPONIBLE|2025-05-06|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Servidores'), 'ACT-0013', 'NAS Synology DS923+', 'HARDWARE', 'Almacenamiento NAS', 'Synology', 'DS923+', 'NAS-SYN-923-013', 1040.00, '2024-07-10', 60, 'ASIGNADO', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Respaldo departamental administrado por Infraestructura.', SHA2('NAS-SYN-923-013|HARDWARE|ASIGNADO|2024-07-10|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Equipo de Red'), 'NET-0004', 'Switch core Cisco Catalyst 9300', 'HARDWARE', 'Switch core', 'Cisco', 'Catalyst 9300', 'SWT-CISCO-9300-004', 3920.00, '2023-03-15', 72, 'ASIGNADO', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Instalado en NOC para enlaces troncales del campus.', SHA2('SWT-CISCO-9300-004|HARDWARE|ASIGNADO|2023-03-15|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Equipo de Red'), 'NET-0005', 'Router MikroTik CCR2004', 'HARDWARE', 'Router', 'MikroTik', 'CCR2004-16G-2S+', 'RTR-MKT-2004-005', 465.00, '2025-09-20', 60, 'DISPONIBLE', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Equipo de contingencia para enlaces WAN secundarios.', SHA2('RTR-MKT-2004-005|HARDWARE|DISPONIBLE|2025-09-20|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Equipo de Red'), 'NET-0006', 'Punto de acceso UniFi U6 Pro', 'HARDWARE', 'Access Point', 'Ubiquiti', 'UniFi U6 Pro', 'AP-UBQ-U6PRO-006', 198.00, '2026-01-18', 48, 'ASIGNADO', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Instalado para ampliar cobertura inalámbrica en laboratorio 1.', SHA2('AP-UBQ-U6PRO-006|HARDWARE|ASIGNADO|2026-01-18|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Servidores'), 'SRV-0002', 'Servidor HPE ProLiant DL360 Gen10', 'HARDWARE', 'Servidor', 'HPE', 'ProLiant DL360 Gen10', 'SRV-HPE-DL360-002', 5180.00, '2020-11-11', 72, 'EN_REPARACION', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Reparación por alerta de memoria ECC y ventilador redundante.', SHA2('SRV-HPE-DL360-002|HARDWARE|EN_REPARACION|2020-11-11|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Servidores'), 'SRV-0003', 'Mini PC Proxmox Backup', 'HARDWARE', 'Servidor compacto', 'Intel', 'NUC 13 Pro', 'SRV-NUC-PROX-003', 890.00, '2025-12-05', 60, 'DISPONIBLE', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Disponible para laboratorio de respaldo y pruebas de virtualización.', SHA2('SRV-NUC-PROX-003|HARDWARE|DISPONIBLE|2025-12-05|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Equipo de Telefonía'), 'TEL-0004', 'Teléfono IP Yealink T54W', 'HARDWARE', 'Teléfono IP', 'Yealink', 'T54W', 'TEL-YEA-T54W-004', 185.00, '2025-06-14', 48, 'ASIGNADO', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Extensión para Registro Académico.', SHA2('TEL-YEA-T54W-004|HARDWARE|ASIGNADO|2025-06-14|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Impresión y Digitalización'), 'ACT-0014', 'Escáner Fujitsu ScanSnap iX1600', 'HARDWARE', 'Escáner documental', 'Fujitsu', 'ScanSnap iX1600', 'SCN-FUJ-IX1600-014', 420.00, '2024-09-09', 48, 'ASIGNADO', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Digitalización de expedientes de admisiones.', SHA2('SCN-FUJ-IX1600-014|HARDWARE|ASIGNADO|2024-09-09|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Energía y Respaldo'), 'ACT-0015', 'UPS APC Smart-UPS 1500VA', 'HARDWARE', 'UPS', 'APC', 'Smart-UPS 1500VA', 'UPS-APC-1500-015', 620.00, '2021-02-19', 48, 'DANADO', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, @elena_id, @admin_id, 'Baterías hinchadas y autonomía inferior a cinco minutos.', '2026-07-03', 'INF-DAN-2026-015', 'Pendiente decisión de reparación o descarte por proveedor autorizado.', SHA2('UPS-APC-1500-015|HARDWARE|DANADO|2021-02-19|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Equipo de Cómputo'), 'ACT-0016', 'Laptop Dell Latitude 3420', 'HARDWARE', 'Laptop', 'Dell', 'Latitude 3420', 'LAP-DELL-3420-016', 825.00, '2022-10-17', 36, 'DEVOLUCION_REGISTRADA', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Devuelta por fin de asignación; pendiente revisión física.', SHA2('LAP-DELL-3420-016|HARDWARE|DEVOLUCION_REGISTRADA|2022-10-17|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Equipo de Cómputo'), 'ACT-0017', 'Desktop Dell OptiPlex 7070', 'HARDWARE', 'Desktop', 'Dell', 'OptiPlex 7070', 'DESK-DELL-7070-017', 760.00, '2021-07-22', 48, 'REVISION_TECNICA', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'En mesa técnica por falla intermitente de encendido.', SHA2('DESK-DELL-7070-017|HARDWARE|REVISION_TECNICA|2021-07-22|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Equipo de Cómputo'), 'ACT-0018', 'Tablet Apple iPad 9th Gen', 'HARDWARE', 'Tablet', 'Apple', 'iPad 9th Gen', 'TAB-APL-IPAD9-018', 330.00, '2020-05-12', 36, 'DONADO', NULL, NULL, 0, 1, 'Patricia Castillo', 'Programa Aula Abierta', 'ACTA-DON-2026-018', 'Equipo funcional entregado para programa comunitario posterior a renovación.', '2026-07-01', 120.00, @admin_id, NULL, NULL, NULL, NULL, NULL, NULL, 'Donación documentada con acta y responsable institucional.', SHA2('TAB-APL-IPAD9-018|HARDWARE|DONADO|2020-05-12|demo', 256), 0),
((SELECT id FROM categorias WHERE nombre = 'Periféricos'), 'ACT-0019', 'Monitor LG UltraWide 29 pulgadas', 'HARDWARE', 'Monitor', 'LG', '29WN600-W', 'MON-LG-29WN-019', 255.00, '2019-08-30', 48, 'DESCARTE', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Panel con líneas permanentes, sin disponibilidad de repuesto económico.', @elena_id, @admin_id, 'Daño físico en panel LCD.', '2026-06-28', 'INF-DESC-2026-019', 'Marcado para descarte controlado.', SHA2('MON-LG-29WN-019|HARDWARE|DESCARTE|2019-08-30|demo', 256), 1);

-- --------------------------------------------------------------------------
-- Inventario de software y licencias por cupos
-- --------------------------------------------------------------------------

INSERT IGNORE INTO inventario
(categoria_id, codigo_activo, nombre, tipo_activo, subcategoria, marca, modelo, serie, costo, fecha_ingreso, vida_util_meses, estado,
 imagen_principal, thumbnail, es_licencia, clave_licencia, clave_licencia_cifrada, clave_licencia_hash, clave_licencia_algoritmo,
 clave_licencia_migrada_at, proveedor_licencia, tipo_licencia, fecha_adquisicion_licencia, url_licencia, fecha_vencimiento_licencia,
 estado_licencia, observaciones_licencia, cantidad, notas, firma_integridad, activo)
VALUES
((SELECT id FROM categorias WHERE nombre = 'Servicios Cloud'), 'LIC-0005', 'Zoom Workplace Business', 'SOFTWARE', 'Licencia', 'Zoom', 'Business', 'LIC-ZOOM-BUS-005', 1800.00, '2026-02-01', 12, 'DISPONIBLE', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'Zoom', 'Suscripción anual', '2026-02-01', 'https://admin.zoom.us/', '2027-02-01', 'ACTIVA', 'Cupos para clases virtuales, reuniones administrativas y eventos híbridos.', 25, 'Asignar por cupos; no incluir claves operativas en datos demo.', SHA2('LIC-ZOOM-BUS-005|SOFTWARE|DISPONIBLE|2026-02-01|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Sistemas Operativos'), 'LIC-0006', 'Windows Server 2022 Standard', 'SOFTWARE', 'Licencia', 'Microsoft', 'Windows Server 2022 Standard', 'LIC-WINSRV-2022-006', 1120.00, '2025-09-01', 36, 'DISPONIBLE', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'Microsoft', 'Licencia perpetua', '2025-09-01', 'https://admin.microsoft.com/', '2028-09-01', 'ACTIVA', 'Licencias para servidores virtuales de servicios internos.', 4, 'Administrar instalación por servidor autorizado.', SHA2('LIC-WINSRV-2022-006|SOFTWARE|DISPONIBLE|2025-09-01|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Servicios Cloud'), 'LIC-0007', 'GitHub Enterprise Cloud', 'SOFTWARE', 'Licencia', 'GitHub', 'Enterprise Cloud', 'LIC-GITHUB-ENT-007', 2700.00, '2026-03-10', 12, 'DISPONIBLE', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'GitHub', 'Suscripción anual', '2026-03-10', 'https://github.com/enterprises/', '2027-03-10', 'ACTIVA', 'Cupos para repositorios académicos, CI y proyectos de investigación.', 30, 'Uso por equipos de Tecnología, Laboratorios e Investigación.', SHA2('LIC-GITHUB-ENT-007|SOFTWARE|DISPONIBLE|2026-03-10|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Software'), 'SW-0003', 'GLPI Cloud Helpdesk', 'SOFTWARE', 'Mesa de ayuda', 'Teclib', 'GLPI Cloud', 'SW-GLPI-CLOUD-003', 960.00, '2026-01-15', 12, 'DISPONIBLE', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'Teclib', 'Suscripción anual', '2026-01-15', 'https://glpi-network.cloud/', '2027-01-15', 'ACTIVA', 'Mesa de ayuda para incidentes, activos y solicitudes de soporte.', 12, 'Cupos para operadores de soporte.', SHA2('SW-GLPI-CLOUD-003|SOFTWARE|DISPONIBLE|2026-01-15|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Seguridad Informática'), 'SW-0004', 'Veeam Backup Essentials', 'SOFTWARE', 'Respaldo', 'Veeam', 'Backup Essentials', 'SW-VEEAM-ESS-004', 1510.00, '2026-04-01', 12, 'DISPONIBLE', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'Veeam', 'Suscripción anual', '2026-04-01', 'https://my.veeam.com/', '2027-04-01', 'ACTIVA', 'Protección de servidores virtuales y repositorio de respaldos.', 6, 'Uso restringido a Infraestructura.', SHA2('SW-VEEAM-ESS-004|SOFTWARE|DISPONIBLE|2026-04-01|demo', 256), 1),
((SELECT id FROM categorias WHERE nombre = 'Servicios Cloud'), 'LIC-0008', 'TeamViewer Tensor', 'SOFTWARE', 'Acceso remoto', 'TeamViewer', 'Tensor', 'LIC-TV-TENSOR-008', 890.00, '2025-06-30', 12, 'DISPONIBLE', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 'TeamViewer', 'Suscripción anual', '2025-06-30', 'https://login.teamviewer.com/', '2026-06-30', 'VENCIDA', 'Licencia vencida conservada para pruebas de alertas y reportes.', 8, 'No asignar hasta renovar contrato.', SHA2('LIC-TV-TENSOR-008|SOFTWARE|DISPONIBLE|2025-06-30|demo', 256), 1);

-- --------------------------------------------------------------------------
-- Asignaciones activas e historicas
-- --------------------------------------------------------------------------

INSERT INTO asignaciones (inventario_id, colaborador_id, usuario_asignador_id, fecha_asignacion, fecha_devolucion, ip_asignada, observaciones, estado)
SELECT i.id, c.id, @operador_id, '2026-01-20', NULL, '192.168.20.18', 'Laptop entregada con docking USB-C y cargador.', 'ACTIVA'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'mariana.perez@cmdb.local'
WHERE i.codigo_activo = 'ACT-0008'
  AND NOT EXISTS (SELECT 1 FROM asignaciones a WHERE a.inventario_id = i.id AND a.estado = 'ACTIVA');

INSERT INTO asignaciones (inventario_id, colaborador_id, usuario_asignador_id, fecha_asignacion, fecha_devolucion, ip_asignada, observaciones, estado)
SELECT i.id, c.id, @operador_id, '2025-02-03', NULL, '192.168.30.61', 'Proyector custodiado por Comunicaciones para eventos.', 'ACTIVA'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'camila.paredes@cmdb.local'
WHERE i.codigo_activo = 'ACT-0011'
  AND NOT EXISTS (SELECT 1 FROM asignaciones a WHERE a.inventario_id = i.id AND a.estado = 'ACTIVA');

INSERT INTO asignaciones (inventario_id, colaborador_id, usuario_asignador_id, fecha_asignacion, fecha_devolucion, ip_asignada, observaciones, estado)
SELECT i.id, c.id, @admin_id, '2024-08-01', NULL, '10.10.0.25', 'NAS instalado en rack de Infraestructura.', 'ACTIVA'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'rafael.mendez@cmdb.local'
WHERE i.codigo_activo = 'ACT-0013'
  AND NOT EXISTS (SELECT 1 FROM asignaciones a WHERE a.inventario_id = i.id AND a.estado = 'ACTIVA');

INSERT INTO asignaciones (inventario_id, colaborador_id, usuario_asignador_id, fecha_asignacion, fecha_devolucion, ip_asignada, observaciones, estado)
SELECT i.id, c.id, @admin_id, '2023-03-20', NULL, '10.10.0.2', 'Switch core en producción, responsable NOC.', 'ACTIVA'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'rafael.mendez@cmdb.local'
WHERE i.codigo_activo = 'NET-0004'
  AND NOT EXISTS (SELECT 1 FROM asignaciones a WHERE a.inventario_id = i.id AND a.estado = 'ACTIVA');

INSERT INTO asignaciones (inventario_id, colaborador_id, usuario_asignador_id, fecha_asignacion, fecha_devolucion, ip_asignada, observaciones, estado)
SELECT i.id, c.id, @soporte_id, '2026-01-22', NULL, '10.40.1.21', 'AP instalado en laboratorio 1.', 'ACTIVA'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'andres.quintero@cmdb.local'
WHERE i.codigo_activo = 'NET-0006'
  AND NOT EXISTS (SELECT 1 FROM asignaciones a WHERE a.inventario_id = i.id AND a.estado = 'ACTIVA');

INSERT INTO asignaciones (inventario_id, colaborador_id, usuario_asignador_id, fecha_asignacion, fecha_devolucion, ip_asignada, observaciones, estado)
SELECT i.id, c.id, @operador_id, '2025-06-20', NULL, '10.20.5.41', 'Teléfono con extensión 2141.', 'ACTIVA'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'mariana.perez@cmdb.local'
WHERE i.codigo_activo = 'TEL-0004'
  AND NOT EXISTS (SELECT 1 FROM asignaciones a WHERE a.inventario_id = i.id AND a.estado = 'ACTIVA');

INSERT INTO asignaciones (inventario_id, colaborador_id, usuario_asignador_id, fecha_asignacion, fecha_devolucion, ip_asignada, observaciones, estado)
SELECT i.id, c.id, @operador_id, '2024-09-12', NULL, NULL, 'Escáner asignado para digitalización de expedientes.', 'ACTIVA'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'isabel.navarro@cmdb.local'
WHERE i.codigo_activo = 'ACT-0014'
  AND NOT EXISTS (SELECT 1 FROM asignaciones a WHERE a.inventario_id = i.id AND a.estado = 'ACTIVA');

INSERT INTO asignaciones (inventario_id, colaborador_id, usuario_asignador_id, fecha_asignacion, fecha_devolucion, ip_asignada, observaciones, estado)
SELECT i.id, c.id, @operador_id, '2022-05-02', '2026-07-02', NULL, 'Devuelta por atasco recurrente y manchas en impresión.', 'DEVUELTA'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'mariana.perez@cmdb.local'
WHERE i.codigo_activo = 'ACT-0010'
  AND NOT EXISTS (SELECT 1 FROM asignaciones a WHERE a.inventario_id = i.id AND a.estado = 'DEVUELTA' AND a.fecha_asignacion = '2022-05-02');

INSERT INTO asignaciones (inventario_id, colaborador_id, usuario_asignador_id, fecha_asignacion, fecha_devolucion, ip_asignada, observaciones, estado)
SELECT i.id, c.id, @operador_id, '2021-03-01', '2026-07-03', NULL, 'Devuelta por alarma de batería y autonomía insuficiente.', 'DEVUELTA'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'rafael.mendez@cmdb.local'
WHERE i.codigo_activo = 'ACT-0015'
  AND NOT EXISTS (SELECT 1 FROM asignaciones a WHERE a.inventario_id = i.id AND a.estado = 'DEVUELTA' AND a.fecha_asignacion = '2021-03-01');

INSERT INTO asignaciones (inventario_id, colaborador_id, usuario_asignador_id, fecha_asignacion, fecha_devolucion, ip_asignada, observaciones, estado)
SELECT i.id, c.id, @operador_id, '2023-01-11', NULL, '192.168.20.42', 'Solicitud de devolución por traslado de colaborador a equipo nuevo.', 'ACTIVA'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'luis.santamaria@cmdb.local'
WHERE i.codigo_activo = 'ACT-0016'
  AND NOT EXISTS (SELECT 1 FROM asignaciones a WHERE a.inventario_id = i.id AND a.estado = 'ACTIVA' AND a.fecha_asignacion = '2023-01-11');

INSERT INTO asignaciones (inventario_id, colaborador_id, usuario_asignador_id, fecha_asignacion, fecha_devolucion, ip_asignada, observaciones, estado)
SELECT i.id, c.id, @operador_id, '2021-08-10', NULL, '192.168.50.33', 'Equipo recibido físicamente y pendiente de revisión técnica.', 'ACTIVA'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'gabriel.sosa@cmdb.local'
WHERE i.codigo_activo = 'ACT-0017'
  AND NOT EXISTS (SELECT 1 FROM asignaciones a WHERE a.inventario_id = i.id AND a.estado = 'ACTIVA' AND a.fecha_asignacion = '2021-08-10');

-- --------------------------------------------------------------------------
-- Devoluciones y revisiones tecnicas
-- --------------------------------------------------------------------------

INSERT INTO devoluciones (asignacion_id, inventario_id, solicitado_por, recibido_por, motivo, estado_fisico, observaciones, evidencia, fecha_recepcion, estado)
SELECT a.id, i.id, @mariana_user_id, @elena_id, 'Falla de impresión y atasco recurrente', 'REGULAR', 'Equipo recibido con tóner instalado, bandeja y cable de poder.', 'DEV-ACT-0010-20260702', '2026-07-02', 'APROBADA'
FROM asignaciones a
INNER JOIN inventario i ON i.id = a.inventario_id
WHERE i.codigo_activo = 'ACT-0010' AND a.estado = 'DEVUELTA'
  AND NOT EXISTS (SELECT 1 FROM devoluciones d WHERE d.asignacion_id = a.id);

INSERT INTO devoluciones (asignacion_id, inventario_id, solicitado_por, recibido_por, motivo, estado_fisico, observaciones, evidencia, fecha_recepcion, estado)
SELECT a.id, i.id, @rafael_user_id, @elena_id, 'Autonomía insuficiente y alarma de batería', 'DANADO', 'UPS recibido con batería hinchada y sin capacidad de respaldo.', 'DEV-ACT-0015-20260703', '2026-07-03', 'APROBADA'
FROM asignaciones a
INNER JOIN inventario i ON i.id = a.inventario_id
WHERE i.codigo_activo = 'ACT-0015' AND a.estado = 'DEVUELTA'
  AND NOT EXISTS (SELECT 1 FROM devoluciones d WHERE d.asignacion_id = a.id);

INSERT INTO devoluciones (asignacion_id, inventario_id, solicitado_por, recibido_por, motivo, estado_fisico, observaciones, evidencia, fecha_recepcion, accesorios_recibidos, observacion_recepcion, estado)
SELECT a.id, i.id, @operador_id, NULL, 'Fin de asignación por renovación de equipo', 'BUENO', 'Pendiente verificación de batería, cargador y estado físico.', 'DEV-ACT-0016-20260702', NULL, NULL, NULL, 'PENDIENTE_REVISION'
FROM asignaciones a
INNER JOIN inventario i ON i.id = a.inventario_id
WHERE i.codigo_activo = 'ACT-0016' AND a.estado = 'ACTIVA'
  AND NOT EXISTS (SELECT 1 FROM devoluciones d WHERE d.asignacion_id = a.id);

INSERT INTO devoluciones (asignacion_id, inventario_id, solicitado_por, recibido_por, motivo, estado_fisico, observaciones, evidencia, fecha_recepcion, accesorios_recibidos, observacion_recepcion, estado)
SELECT a.id, i.id, @operador_id, @elena_id, 'Falla intermitente de encendido', 'REGULAR', 'En diagnóstico por posible fuente de poder.', 'DEV-ACT-0017-20260705', '2026-07-05 09:30:00', 'Cable de poder y mouse USB.', 'Recibido con polvo interno visible.', 'EN_REVISION'
FROM asignaciones a
INNER JOIN inventario i ON i.id = a.inventario_id
WHERE i.codigo_activo = 'ACT-0017' AND a.estado = 'ACTIVA'
  AND NOT EXISTS (SELECT 1 FROM devoluciones d WHERE d.asignacion_id = a.id);

INSERT INTO revisiones_tecnicas
(devolucion_id, inventario_id, tecnico_id, resultado, diagnostico, opinion_tecnica, recomendacion, observacion_tecnica, evidencia, aprobador_id)
SELECT d.id, i.id, @elena_id, 'EN_REPARACION',
       'Unidad fusora con desgaste y rodillo de arrastre desalineado.',
       'La reparación es viable y cuesta menos que reemplazar el equipo.',
       'Solicitar repuesto de fusor y limpieza interna completa.',
       'Se aprueba reparación por proveedor autorizado.',
       'REV-ACT-0010-20260704', @admin_id
FROM devoluciones d
INNER JOIN inventario i ON i.id = d.inventario_id
WHERE i.codigo_activo = 'ACT-0010'
  AND NOT EXISTS (SELECT 1 FROM revisiones_tecnicas r WHERE r.devolucion_id = d.id);

INSERT INTO revisiones_tecnicas
(devolucion_id, inventario_id, tecnico_id, resultado, diagnostico, opinion_tecnica, recomendacion, observacion_tecnica, evidencia, aprobador_id)
SELECT d.id, i.id, @elena_id, 'DANADO',
       'Baterías hinchadas, alarma continua y autonomía inferior a cinco minutos.',
       'No se recomienda uso hasta evaluación de proveedor por riesgo operativo.',
       'Cotizar baterías y comparar contra reemplazo completo.',
       'Equipo marcado como dañado y retirado de uso.',
       'REV-ACT-0015-20260704', @admin_id
FROM devoluciones d
INNER JOIN inventario i ON i.id = d.inventario_id
WHERE i.codigo_activo = 'ACT-0015'
  AND NOT EXISTS (SELECT 1 FROM revisiones_tecnicas r WHERE r.devolucion_id = d.id);

-- --------------------------------------------------------------------------
-- Asignacion de licencias
-- --------------------------------------------------------------------------

INSERT INTO licencia_asignaciones (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, fecha_fin, estado, observaciones)
SELECT i.id, c.id, @operador_id, 4, '2026-02-05', NULL, 'ACTIVA', 'Cupos para webinars y reuniones de Comunicaciones.'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'camila.paredes@cmdb.local'
WHERE i.codigo_activo = 'LIC-0005'
  AND NOT EXISTS (SELECT 1 FROM licencia_asignaciones la WHERE la.inventario_id = i.id AND la.colaborador_id = c.id AND la.estado = 'ACTIVA');

INSERT INTO licencia_asignaciones (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, fecha_fin, estado, observaciones)
SELECT i.id, c.id, @operador_id, 6, '2026-02-06', NULL, 'ACTIVA', 'Cupos compartidos para clases y tutorías híbridas.'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'monica.herrera@cmdb.local'
WHERE i.codigo_activo = 'LIC-0005'
  AND NOT EXISTS (SELECT 1 FROM licencia_asignaciones la WHERE la.inventario_id = i.id AND la.colaborador_id = c.id AND la.estado = 'ACTIVA');

INSERT INTO licencia_asignaciones (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, fecha_fin, estado, observaciones)
SELECT i.id, c.id, @admin_id, 2, '2025-09-12', NULL, 'ACTIVA', 'Licencias para servidores virtualizados de servicios internos.'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'rafael.mendez@cmdb.local'
WHERE i.codigo_activo = 'LIC-0006'
  AND NOT EXISTS (SELECT 1 FROM licencia_asignaciones la WHERE la.inventario_id = i.id AND la.colaborador_id = c.id AND la.estado = 'ACTIVA');

INSERT INTO licencia_asignaciones (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, fecha_fin, estado, observaciones)
SELECT i.id, c.id, @admin_id, 8, '2026-03-12', NULL, 'ACTIVA', 'Repositorios de proyectos académicos y automatización CI.'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'andres.quintero@cmdb.local'
WHERE i.codigo_activo = 'LIC-0007'
  AND NOT EXISTS (SELECT 1 FROM licencia_asignaciones la WHERE la.inventario_id = i.id AND la.colaborador_id = c.id AND la.estado = 'ACTIVA');

INSERT INTO licencia_asignaciones (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, fecha_fin, estado, observaciones)
SELECT i.id, c.id, @admin_id, 5, '2026-03-12', NULL, 'ACTIVA', 'Repositorios de investigación y documentación técnica.'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'tomas.arias@cmdb.local'
WHERE i.codigo_activo = 'LIC-0007'
  AND NOT EXISTS (SELECT 1 FROM licencia_asignaciones la WHERE la.inventario_id = i.id AND la.colaborador_id = c.id AND la.estado = 'ACTIVA');

INSERT INTO licencia_asignaciones (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, fecha_fin, estado, observaciones)
SELECT i.id, c.id, @soporte_id, 6, '2026-01-18', NULL, 'ACTIVA', 'Operadores de mesa de ayuda y seguimiento de tickets.'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'elena.vargas@cmdb.local'
WHERE i.codigo_activo = 'SW-0003'
  AND NOT EXISTS (SELECT 1 FROM licencia_asignaciones la WHERE la.inventario_id = i.id AND la.colaborador_id = c.id AND la.estado = 'ACTIVA');

INSERT INTO licencia_asignaciones (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, fecha_fin, estado, observaciones)
SELECT i.id, c.id, @admin_id, 3, '2026-04-03', NULL, 'ACTIVA', 'Respaldos de máquinas virtuales críticas.'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'miguel.rios@cmdb.local'
WHERE i.codigo_activo = 'SW-0004'
  AND NOT EXISTS (SELECT 1 FROM licencia_asignaciones la WHERE la.inventario_id = i.id AND la.colaborador_id = c.id AND la.estado = 'ACTIVA');

INSERT INTO licencia_asignaciones (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, fecha_fin, estado, observaciones)
SELECT i.id, c.id, @soporte_id, 2, '2025-07-02', '2026-06-30', 'VENCIDA', 'Cupos vencidos conservados para reporte de renovaciones.'
FROM inventario i
INNER JOIN colaboradores c ON c.email = 'elena.vargas@cmdb.local'
WHERE i.codigo_activo = 'LIC-0008'
  AND NOT EXISTS (SELECT 1 FROM licencia_asignaciones la WHERE la.inventario_id = i.id AND la.colaborador_id = c.id AND la.estado = 'VENCIDA');

-- --------------------------------------------------------------------------
-- Solicitudes, historial y presupuesto conectado
-- --------------------------------------------------------------------------

INSERT INTO necesidades
(colaborador_id, categoria_id, tipo_necesidad, descripcion, justificacion, prioridad, costo_estimado, costo_unitario_estimado, cantidad,
 anio_objetivo, estado, comentario_resolucion, usuario_procesador_id, respuesta_administrativa, fecha_procesamiento)
SELECT c.id, cat.id, 'EQUIPO', 'Kit móvil de videoconferencia para sede oeste.', 'La sede atiende reuniones híbridas con docentes externos y requiere cámara, micrófono y pantalla auxiliar.', 'ALTA', 1050.00, 1050.00, 1, 2027, 'APROBADA', 'Aprobada por impacto directo en docencia híbrida.', @operador_id, 'Aprobada para compra en el presupuesto operativo 2027.', '2026-07-08 10:30:00'
FROM colaboradores c
INNER JOIN categorias cat ON cat.nombre = 'Audiovisual'
WHERE c.email = 'monica.herrera@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM necesidades n WHERE n.colaborador_id = c.id AND n.descripcion = 'Kit móvil de videoconferencia para sede oeste.');

INSERT INTO necesidades
(colaborador_id, categoria_id, tipo_necesidad, descripcion, justificacion, prioridad, costo_estimado, costo_unitario_estimado, cantidad,
 anio_objetivo, estado, comentario_resolucion, usuario_procesador_id, respuesta_administrativa, fecha_procesamiento)
SELECT c.id, cat.id, 'EQUIPO', 'Reposición de UPS para rack de comunicaciones del NOC.', 'El equipo actual presenta autonomía insuficiente y afecta continuidad de enlaces críticos.', 'ALTA', 1650.00, 1650.00, 1, 2027, 'EN_TRAMITE', 'Pendiente cotización con proveedor autorizado.', NULL, NULL, NULL
FROM colaboradores c
INNER JOIN categorias cat ON cat.nombre = 'Energía y Respaldo'
WHERE c.email = 'rafael.mendez@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM necesidades n WHERE n.colaborador_id = c.id AND n.descripcion = 'Reposición de UPS para rack de comunicaciones del NOC.');

INSERT INTO necesidades
(colaborador_id, categoria_id, tipo_necesidad, descripcion, justificacion, prioridad, costo_estimado, costo_unitario_estimado, cantidad,
 anio_objetivo, estado, comentario_resolucion, usuario_procesador_id, respuesta_administrativa, fecha_procesamiento)
SELECT c.id, cat.id, 'SOFTWARE', 'Licencias MATLAB para prácticas de laboratorio.', 'Se requieren cupos temporales para simulación y análisis numérico en laboratorios.', 'MEDIA', 1800.00, 600.00, 3, 2027, 'EN_ESPERA', NULL, NULL, NULL, NULL
FROM colaboradores c
INNER JOIN categorias cat ON cat.nombre = 'Software'
WHERE c.email = 'andres.quintero@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM necesidades n WHERE n.colaborador_id = c.id AND n.descripcion = 'Licencias MATLAB para prácticas de laboratorio.');

INSERT INTO necesidades
(colaborador_id, categoria_id, tipo_necesidad, descripcion, justificacion, prioridad, costo_estimado, costo_unitario_estimado, cantidad,
 anio_objetivo, estado, comentario_resolucion, usuario_procesador_id, respuesta_administrativa, fecha_procesamiento)
SELECT c.id, cat.id, 'LICENCIA', 'Banco de imágenes premium para campañas institucionales.', 'Solicitud para ampliar recursos gráficos de campañas y redes sociales.', 'BAJA', 420.00, 420.00, 1, 2027, 'RECHAZADA', 'No se aprueba por existir alternativa institucional vigente.', @operador_id, 'Rechazada: usar banco institucional ya contratado y revisar demanda en el siguiente periodo.', '2026-07-09 15:45:00'
FROM colaboradores c
INNER JOIN categorias cat ON cat.nombre = 'Herramientas de Diseño'
WHERE c.email = 'camila.paredes@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM necesidades n WHERE n.colaborador_id = c.id AND n.descripcion = 'Banco de imágenes premium para campañas institucionales.');

INSERT INTO necesidades
(colaborador_id, categoria_id, tipo_necesidad, descripcion, justificacion, prioridad, costo_estimado, costo_unitario_estimado, cantidad,
 anio_objetivo, estado, comentario_resolucion, usuario_procesador_id, respuesta_administrativa, fecha_procesamiento)
SELECT c.id, cat.id, 'EQUIPO', 'Monitores duales para cierre contable.', 'El equipo de Contabilidad revisa conciliaciones y reportes simultáneos durante cierres mensuales.', 'MEDIA', 420.00, 210.00, 2, 2027, 'EN_ESPERA', NULL, NULL, NULL, NULL
FROM colaboradores c
INNER JOIN categorias cat ON cat.nombre = 'Periféricos'
WHERE c.email = 'luis.santamaria@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM necesidades n WHERE n.colaborador_id = c.id AND n.descripcion = 'Monitores duales para cierre contable.');

INSERT INTO necesidades
(colaborador_id, categoria_id, tipo_necesidad, descripcion, justificacion, prioridad, costo_estimado, costo_unitario_estimado, cantidad,
 anio_objetivo, estado, comentario_resolucion, usuario_procesador_id, respuesta_administrativa, fecha_procesamiento)
SELECT c.id, cat.id, 'EQUIPO', 'Sensores y microcontroladores para laboratorio IoT.', 'Proyecto de investigación requiere kits de prototipado para medición ambiental.', 'MEDIA', 640.00, 160.00, 4, 2027, 'EN_TRAMITE', 'Se revisa disponibilidad en compras y compatibilidad técnica.', NULL, NULL, NULL
FROM colaboradores c
INNER JOIN categorias cat ON cat.nombre = 'Hardware'
WHERE c.email = 'tomas.arias@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM necesidades n WHERE n.colaborador_id = c.id AND n.descripcion = 'Sensores y microcontroladores para laboratorio IoT.');

INSERT INTO necesidades
(colaborador_id, categoria_id, tipo_necesidad, descripcion, justificacion, prioridad, costo_estimado, costo_unitario_estimado, cantidad,
 anio_objetivo, estado, comentario_resolucion, usuario_procesador_id, respuesta_administrativa, fecha_procesamiento)
SELECT c.id, cat.id, 'SOFTWARE', 'Herramienta de inventario remoto para soporte técnico.', 'Soporte requiere detectar software instalado, parches pendientes y estado de estaciones sin visita presencial.', 'ALTA', 900.00, 900.00, 1, 2027, 'EN_TRAMITE', 'Pendiente prueba piloto con diez equipos.', NULL, NULL, NULL
FROM colaboradores c
INNER JOIN categorias cat ON cat.nombre = 'Servicios Cloud'
WHERE c.email = 'elena.vargas@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM necesidades n WHERE n.colaborador_id = c.id AND n.descripcion = 'Herramienta de inventario remoto para soporte técnico.');

INSERT INTO necesidades
(colaborador_id, categoria_id, tipo_necesidad, descripcion, justificacion, prioridad, costo_estimado, costo_unitario_estimado, cantidad,
 anio_objetivo, estado, comentario_resolucion, usuario_procesador_id, respuesta_administrativa, fecha_procesamiento)
SELECT c.id, cat.id, 'SOFTWARE', 'Evaluación de herramienta SIEM para eventos críticos.', 'Se requiere análisis técnico y financiero antes de estimar compra.', 'ALTA', NULL, NULL, 1, 2027, 'EN_ESPERA', NULL, NULL, NULL, NULL
FROM colaboradores c
INNER JOIN categorias cat ON cat.nombre = 'Seguridad Informática'
WHERE c.email = 'rafael.mendez@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM necesidades n WHERE n.colaborador_id = c.id AND n.descripcion = 'Evaluación de herramienta SIEM para eventos críticos.');

INSERT INTO necesidades_historial (necesidad_id, usuario_id, estado_anterior, estado_nuevo, observacion, respuesta_administrativa)
SELECT n.id, @operador_id, NULL, n.estado, 'Carga demo ampliada de solicitud con datos presupuestarios.', n.respuesta_administrativa
FROM necesidades n
WHERE n.descripcion IN (
    'Kit móvil de videoconferencia para sede oeste.',
    'Reposición de UPS para rack de comunicaciones del NOC.',
    'Licencias MATLAB para prácticas de laboratorio.',
    'Banco de imágenes premium para campañas institucionales.',
    'Monitores duales para cierre contable.',
    'Sensores y microcontroladores para laboratorio IoT.',
    'Herramienta de inventario remoto para soporte técnico.',
    'Evaluación de herramienta SIEM para eventos críticos.'
)
  AND NOT EXISTS (
      SELECT 1 FROM necesidades_historial h
      WHERE h.necesidad_id = n.id AND h.observacion = 'Carga demo ampliada de solicitud con datos presupuestarios.'
  );

INSERT INTO necesidades_historial (necesidad_id, usuario_id, estado_anterior, estado_nuevo, observacion, respuesta_administrativa)
SELECT n.id, n.usuario_procesador_id, 'EN_TRAMITE', n.estado, 'Resolución administrativa registrada para demostración.', n.respuesta_administrativa
FROM necesidades n
WHERE n.descripcion IN (
    'Kit móvil de videoconferencia para sede oeste.',
    'Banco de imágenes premium para campañas institucionales.'
)
  AND NOT EXISTS (
      SELECT 1 FROM necesidades_historial h
      WHERE h.necesidad_id = n.id AND h.observacion = 'Resolución administrativa registrada para demostración.'
  );

INSERT INTO presupuestos
(nombre, tipo, anio_inicio, anio_fin, total_estimado, estado, created_by, presupuesto_base, inflacion_anual, crecimiento_anual,
 total_quinquenal, registros_sin_costo, supuestos, filtros_json)
SELECT 'Presupuesto demo operativo 2027', 'ANUAL', 2027, 2027, 10090.00, 'BORRADOR', @admin_id, 10090.00, 2.50, 4.00,
       10090.00, 1,
       'Presupuesto anual demo con solicitudes 2027, licencias, red, audiovisual y un registro sin costo estimado.',
       '{"anio":2027,"fuente":"contenido_demo_completo","incluye_solicitudes_sin_costo":true}'
WHERE NOT EXISTS (SELECT 1 FROM presupuestos WHERE nombre = 'Presupuesto demo operativo 2027');

INSERT INTO presupuestos
(nombre, tipo, anio_inicio, anio_fin, total_estimado, estado, created_by, presupuesto_base, inflacion_anual, crecimiento_anual,
 total_quinquenal, registros_sin_costo, supuestos, filtros_json)
SELECT 'Plan demo de resiliencia tecnológica 2027-2031', 'QUINQUENAL', 2027, 2031, 46500.00, 'BORRADOR', @admin_id, 9000.00, 2.50, 4.00,
       46500.00, 0,
       'Plan quinquenal demo con renovación de endpoints, red, servidores, licencias y respaldo.',
       '{"anio_inicio":2027,"anio_fin":2031,"fuente":"contenido_demo_completo"}'
WHERE NOT EXISTS (SELECT 1 FROM presupuestos WHERE nombre = 'Plan demo de resiliencia tecnológica 2027-2031');

INSERT INTO presupuesto_detalles
(presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, costo_base, subtotal, anio,
 year_index, factor_proyeccion, inflacion_anual, crecimiento_anual, tiene_costo, motivo_sin_costo, prioridad, estado_solicitud)
SELECT p.id, cat.id, NULL, 'EQUIPO', 'Laptops y monitores para áreas administrativas.', 4, 980.00, 980.00, 3920.00, 2027,
       0, 1.00000000, 2.50, 4.00, 1, NULL, 'MEDIA', 'EN_ESPERA'
FROM presupuestos p
INNER JOIN categorias cat ON cat.nombre = 'Equipo de Cómputo'
WHERE p.nombre = 'Presupuesto demo operativo 2027'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Laptops y monitores para áreas administrativas.');

INSERT INTO presupuesto_detalles
(presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, costo_base, subtotal, anio,
 year_index, factor_proyeccion, inflacion_anual, crecimiento_anual, tiene_costo, motivo_sin_costo, prioridad, estado_solicitud)
SELECT p.id, cat.id, n.id, 'EQUIPO', 'Reposición UPS y protección eléctrica del NOC.', 1, 1650.00, 1650.00, 1650.00, 2027,
       0, 1.00000000, 2.50, 4.00, 1, NULL, 'ALTA', 'EN_TRAMITE'
FROM presupuestos p
INNER JOIN categorias cat ON cat.nombre = 'Energía y Respaldo'
LEFT JOIN necesidades n ON n.descripcion = 'Reposición de UPS para rack de comunicaciones del NOC.'
WHERE p.nombre = 'Presupuesto demo operativo 2027'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Reposición UPS y protección eléctrica del NOC.');

INSERT INTO presupuesto_detalles
(presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, costo_base, subtotal, anio,
 year_index, factor_proyeccion, inflacion_anual, crecimiento_anual, tiene_costo, motivo_sin_costo, prioridad, estado_solicitud)
SELECT p.id, cat.id, n.id, 'EQUIPO', 'Kit móvil de videoconferencia para extensión.', 1, 1050.00, 1050.00, 1050.00, 2027,
       0, 1.00000000, 2.50, 4.00, 1, NULL, 'ALTA', 'APROBADA'
FROM presupuestos p
INNER JOIN categorias cat ON cat.nombre = 'Audiovisual'
LEFT JOIN necesidades n ON n.descripcion = 'Kit móvil de videoconferencia para sede oeste.'
WHERE p.nombre = 'Presupuesto demo operativo 2027'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Kit móvil de videoconferencia para extensión.');

INSERT INTO presupuesto_detalles
(presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, costo_base, subtotal, anio,
 year_index, factor_proyeccion, inflacion_anual, crecimiento_anual, tiene_costo, motivo_sin_costo, prioridad, estado_solicitud)
SELECT p.id, cat.id, NULL, 'LICENCIA', 'Cupos colaborativos GitHub y Zoom.', 12, 180.00, 180.00, 2160.00, 2027,
       0, 1.00000000, 2.50, 4.00, 1, NULL, 'MEDIA', 'EN_TRAMITE'
FROM presupuestos p
INNER JOIN categorias cat ON cat.nombre = 'Servicios Cloud'
WHERE p.nombre = 'Presupuesto demo operativo 2027'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Cupos colaborativos GitHub y Zoom.');

INSERT INTO presupuesto_detalles
(presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, costo_base, subtotal, anio,
 year_index, factor_proyeccion, inflacion_anual, crecimiento_anual, tiene_costo, motivo_sin_costo, prioridad, estado_solicitud)
SELECT p.id, cat.id, NULL, 'RENOVACION', 'Renovación de respaldo y monitoreo de seguridad.', 1, 1310.00, 1310.00, 1310.00, 2027,
       0, 1.00000000, 2.50, 4.00, 1, NULL, 'ALTA', 'EN_TRAMITE'
FROM presupuestos p
INNER JOIN categorias cat ON cat.nombre = 'Seguridad Informática'
WHERE p.nombre = 'Presupuesto demo operativo 2027'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Renovación de respaldo y monitoreo de seguridad.');

INSERT INTO presupuesto_detalles
(presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, costo_base, subtotal, anio,
 year_index, factor_proyeccion, inflacion_anual, crecimiento_anual, tiene_costo, motivo_sin_costo, prioridad, estado_solicitud)
SELECT p.id, cat.id, n.id, 'SOFTWARE', 'Evaluación SIEM pendiente de estimación financiera.', 1, 0.00, NULL, 0.00, 2027,
       0, 1.00000000, 2.50, 4.00, 0, 'Solicitud técnica sin costo estimado al momento de presupuesto.', 'ALTA', 'EN_ESPERA'
FROM presupuestos p
INNER JOIN categorias cat ON cat.nombre = 'Seguridad Informática'
LEFT JOIN necesidades n ON n.descripcion = 'Evaluación de herramienta SIEM para eventos críticos.'
WHERE p.nombre = 'Presupuesto demo operativo 2027'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Evaluación SIEM pendiente de estimación financiera.');

INSERT INTO presupuesto_detalles
(presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, costo_base, subtotal, anio,
 year_index, factor_proyeccion, inflacion_anual, crecimiento_anual, tiene_costo, motivo_sin_costo, prioridad, estado_solicitud)
SELECT p.id, cat.id, NULL, 'EQUIPO', 'Renovación de endpoints por ciclo de vida.', 10, 900.00, 900.00, 9000.00, 2027,
       0, 1.00000000, 2.50, 4.00, 1, NULL, 'ALTA', 'EN_TRAMITE'
FROM presupuestos p
INNER JOIN categorias cat ON cat.nombre = 'Equipo de Cómputo'
WHERE p.nombre = 'Plan demo de resiliencia tecnológica 2027-2031'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Renovación de endpoints por ciclo de vida.');

INSERT INTO presupuesto_detalles
(presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, costo_base, subtotal, anio,
 year_index, factor_proyeccion, inflacion_anual, crecimiento_anual, tiene_costo, motivo_sin_costo, prioridad, estado_solicitud)
SELECT p.id, cat.id, NULL, 'EQUIPO', 'Actualización de red de acceso y WiFi.', 6, 650.00, 610.00, 3900.00, 2028,
       1, 1.06600000, 2.50, 4.00, 1, NULL, 'MEDIA', 'EN_TRAMITE'
FROM presupuestos p
INNER JOIN categorias cat ON cat.nombre = 'Equipo de Red'
WHERE p.nombre = 'Plan demo de resiliencia tecnológica 2027-2031'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Actualización de red de acceso y WiFi.');

INSERT INTO presupuesto_detalles
(presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, costo_base, subtotal, anio,
 year_index, factor_proyeccion, inflacion_anual, crecimiento_anual, tiene_costo, motivo_sin_costo, prioridad, estado_solicitud)
SELECT p.id, cat.id, NULL, 'EQUIPO', 'Servidor de respaldo para continuidad operativa.', 1, 6200.00, 5450.00, 6200.00, 2029,
       2, 1.13635600, 2.50, 4.00, 1, NULL, 'ALTA', 'EN_ESPERA'
FROM presupuestos p
INNER JOIN categorias cat ON cat.nombre = 'Servidores'
WHERE p.nombre = 'Plan demo de resiliencia tecnológica 2027-2031'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Servidor de respaldo para continuidad operativa.');

INSERT INTO presupuesto_detalles
(presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, costo_base, subtotal, anio,
 year_index, factor_proyeccion, inflacion_anual, crecimiento_anual, tiene_costo, motivo_sin_costo, prioridad, estado_solicitud)
SELECT p.id, cat.id, NULL, 'RENOVACION', 'Renovación escalonada de licencias cloud.', 50, 72.00, 60.00, 3600.00, 2030,
       3, 1.21135550, 2.50, 4.00, 1, NULL, 'MEDIA', 'EN_TRAMITE'
FROM presupuestos p
INNER JOIN categorias cat ON cat.nombre = 'Servicios Cloud'
WHERE p.nombre = 'Plan demo de resiliencia tecnológica 2027-2031'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Renovación escalonada de licencias cloud.');

INSERT INTO presupuesto_detalles
(presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, costo_base, subtotal, anio,
 year_index, factor_proyeccion, inflacion_anual, crecimiento_anual, tiene_costo, motivo_sin_costo, prioridad, estado_solicitud)
SELECT p.id, cat.id, NULL, 'MANTENIMIENTO', 'Bolsa de mantenimiento y reemplazos críticos.', 20, 1190.00, 980.00, 23800.00, 2031,
       4, 1.29130500, 2.50, 4.00, 1, NULL, 'ALTA', 'EN_TRAMITE'
FROM presupuestos p
INNER JOIN categorias cat ON cat.nombre = 'Hardware'
WHERE p.nombre = 'Plan demo de resiliencia tecnológica 2027-2031'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Bolsa de mantenimiento y reemplazos críticos.');

-- --------------------------------------------------------------------------
-- Historial de ubicaciones, estados y QR seguro
-- --------------------------------------------------------------------------

INSERT INTO ubicaciones_historial
(colaborador_id, ubicacion_anterior, ubicacion_nueva, tipo, fecha_inicio, fecha_fin, motivo, usuario_id)
SELECT c.id, NULL, c.ubicacion,
       CASE
           WHEN c.ubicacion LIKE 'Edificio%' THEN 'EDIFICIO'
           WHEN c.ubicacion LIKE 'Sede%' THEN 'SEDE'
           WHEN c.ubicacion LIKE 'Casa%' THEN 'CASA'
           ELSE 'OTRO'
       END,
       '2026-01-15', NULL, 'Carga demo de ubicación actual del colaborador.', @operador_id
FROM colaboradores c
WHERE c.email IN (
    'mariana.perez@cmdb.local', 'luis.santamaria@cmdb.local', 'elena.vargas@cmdb.local',
    'andres.quintero@cmdb.local', 'monica.herrera@cmdb.local', 'gabriel.sosa@cmdb.local',
    'isabel.navarro@cmdb.local', 'tomas.arias@cmdb.local', 'camila.paredes@cmdb.local',
    'rafael.mendez@cmdb.local'
)
  AND NOT EXISTS (
      SELECT 1 FROM ubicaciones_historial h
      WHERE h.colaborador_id = c.id AND h.ubicacion_nueva = c.ubicacion AND h.motivo = 'Carga demo de ubicación actual del colaborador.'
  );

INSERT INTO inventario_estado_historial
(inventario_id, usuario_id, estado_anterior, estado_nuevo, motivo, observacion, entidad_origen, entidad_id)
SELECT i.id, @operador_id, NULL, i.estado, 'Carga demo ampliada', 'Estado inicial registrado para datos demo completos.', 'inventario', i.id
FROM inventario i
WHERE i.codigo_activo IN (
    'ACT-0008','ACT-0009','ACT-0010','ACT-0011','ACT-0012','ACT-0013','NET-0004','NET-0005','NET-0006',
    'SRV-0002','SRV-0003','TEL-0004','ACT-0014','ACT-0015','ACT-0016','ACT-0017','ACT-0018','ACT-0019',
    'LIC-0005','LIC-0006','LIC-0007','SW-0003','SW-0004','LIC-0008'
)
  AND NOT EXISTS (
      SELECT 1 FROM inventario_estado_historial h
      WHERE h.inventario_id = i.id AND h.motivo = 'Carga demo ampliada'
  );

INSERT INTO inventario_estado_historial
(inventario_id, usuario_id, estado_anterior, estado_nuevo, motivo, observacion, entidad_origen, entidad_id)
SELECT i.id, @elena_id, 'ASIGNADO', 'DEVOLUCION_REGISTRADA', 'Devolución demo registrada', 'Registro de devolución para revisión técnica.', 'devoluciones', d.id
FROM inventario i
INNER JOIN devoluciones d ON d.inventario_id = i.id
WHERE i.codigo_activo IN ('ACT-0010','ACT-0015','ACT-0016','ACT-0017')
  AND NOT EXISTS (
      SELECT 1 FROM inventario_estado_historial h
      WHERE h.inventario_id = i.id AND h.motivo = 'Devolución demo registrada'
  );

INSERT INTO inventario_estado_historial
(inventario_id, usuario_id, estado_anterior, estado_nuevo, motivo, observacion, entidad_origen, entidad_id)
SELECT i.id, @elena_id, 'DEVOLUCION_REGISTRADA', 'REVISION_TECNICA', 'Inicio de revisión demo', 'Mesa técnica inicia diagnóstico del activo.', 'devoluciones', d.id
FROM inventario i
INNER JOIN devoluciones d ON d.inventario_id = i.id
WHERE i.codigo_activo IN ('ACT-0010','ACT-0015','ACT-0017')
  AND NOT EXISTS (
      SELECT 1 FROM inventario_estado_historial h
      WHERE h.inventario_id = i.id AND h.motivo = 'Inicio de revisión demo'
  );

INSERT INTO inventario_estado_historial
(inventario_id, usuario_id, estado_anterior, estado_nuevo, motivo, observacion, entidad_origen, entidad_id)
SELECT i.id, @elena_id, 'REVISION_TECNICA', r.resultado, 'Resultado de revisión demo', r.observacion_tecnica, 'revisiones_tecnicas', r.id
FROM inventario i
INNER JOIN revisiones_tecnicas r ON r.inventario_id = i.id
WHERE i.codigo_activo IN ('ACT-0010','ACT-0015')
  AND NOT EXISTS (
      SELECT 1 FROM inventario_estado_historial h
      WHERE h.inventario_id = i.id AND h.motivo = 'Resultado de revisión demo'
  );

INSERT INTO inventario_qr
(inventario_id, token, payload_hash, activo, token_hash, estado, created_by, access_count)
SELECT i.id,
       SHA2(CONCAT('qr-token:', i.codigo_activo, ':2026-07-14'), 256) AS token,
       SHA2(CONCAT(
           'cmdb-qr-payload-v1:',
           i.id,
           ':',
           SHA2(CONCAT('cmdb-qr-token-v1:', SHA2(CONCAT('qr-token:', i.codigo_activo, ':2026-07-14'), 256)), 256)
       ), 256) AS payload_hash,
       1,
       SHA2(CONCAT('cmdb-qr-token-v1:', SHA2(CONCAT('qr-token:', i.codigo_activo, ':2026-07-14'), 256)), 256) AS token_hash,
       'ACTIVO',
       @operador_id,
       0
FROM inventario i
WHERE i.codigo_activo IN (
    'ACT-0008','ACT-0009','ACT-0010','ACT-0011','ACT-0012','ACT-0013','NET-0004','NET-0005','NET-0006',
    'SRV-0002','SRV-0003','TEL-0004','ACT-0014','ACT-0015','ACT-0016','ACT-0017','ACT-0018','ACT-0019',
    'LIC-0005','LIC-0006','LIC-0007','SW-0003','SW-0004','LIC-0008'
)
  AND NOT EXISTS (
      SELECT 1 FROM inventario_qr q
      WHERE q.inventario_id = i.id AND q.activo = 1 AND q.revoked_at IS NULL
  );

-- --------------------------------------------------------------------------
-- Noticias, accesos y bitacora
-- --------------------------------------------------------------------------

INSERT INTO noticias (usuario_id, titulo, resumen, contenido, publicada)
SELECT @admin_id,
       'Calendario de renovación tecnológica 2027',
       'La CMDB ya refleja solicitudes, activos críticos y presupuesto preliminar.',
       'El equipo de Tecnología utilizará las solicitudes registradas y el inventario con depreciación próxima para priorizar compras del periodo 2027.',
       1
WHERE NOT EXISTS (SELECT 1 FROM noticias WHERE titulo = 'Calendario de renovación tecnológica 2027');

INSERT INTO noticias (usuario_id, titulo, resumen, contenido, publicada)
SELECT @admin_id,
       'Revisión de activos devueltos',
       'Toda devolución debe pasar por diagnóstico antes de reasignarse.',
       'El flujo de devolución registra estado físico, evidencia y resultado técnico. Esto evita reasignar equipos con fallas y mantiene trazabilidad de decisiones.',
       1
WHERE NOT EXISTS (SELECT 1 FROM noticias WHERE titulo = 'Revisión de activos devueltos');

INSERT INTO noticias (usuario_id, titulo, resumen, contenido, publicada)
SELECT @admin_id,
       'Control de cupos SaaS',
       'Las licencias por cantidad permiten ver consumo real por departamento.',
       'Las asignaciones de Zoom, GitHub, GLPI y respaldo ayudan a comparar cupos contratados, cupos activos y renovaciones próximas.',
       1
WHERE NOT EXISTS (SELECT 1 FROM noticias WHERE titulo = 'Control de cupos SaaS');

INSERT INTO accesos_portal_colaborador (usuario_id, ip, accessed_at)
SELECT u.id, '192.168.20.18', '2026-07-12 08:15:00'
FROM usuarios u
WHERE u.nombre_usuario = 'mariana.perez'
  AND NOT EXISTS (
      SELECT 1 FROM accesos_portal_colaborador a
      WHERE a.usuario_id = u.id AND a.ip = '192.168.20.18' AND a.accessed_at = '2026-07-12 08:15:00'
  );

INSERT INTO accesos_portal_colaborador (usuario_id, ip, accessed_at)
SELECT u.id, '192.168.40.24', '2026-07-12 09:05:00'
FROM usuarios u
WHERE u.nombre_usuario = 'andres.quintero'
  AND NOT EXISTS (
      SELECT 1 FROM accesos_portal_colaborador a
      WHERE a.usuario_id = u.id AND a.ip = '192.168.40.24' AND a.accessed_at = '2026-07-12 09:05:00'
  );

INSERT INTO intentos_login (usuario_id, identificador, ip, exitoso, motivo, created_at)
SELECT u.id, 'elena.vargas@cmdb.local', '192.168.10.80', 1, 'Inicio de sesión demo exitoso', '2026-07-12 07:55:00'
FROM usuarios u
WHERE u.nombre_usuario = 'elena.vargas'
  AND NOT EXISTS (
      SELECT 1 FROM intentos_login l
      WHERE l.identificador = 'elena.vargas@cmdb.local' AND l.ip = '192.168.10.80' AND l.created_at = '2026-07-12 07:55:00'
  );

INSERT INTO intentos_login (usuario_id, identificador, ip, exitoso, motivo, created_at)
SELECT NULL, 'usuario.inexistente@cmdb.local', '192.168.10.81', 0, 'Usuario no encontrado - evento demo', '2026-07-12 08:02:00'
WHERE NOT EXISTS (
    SELECT 1 FROM intentos_login l
    WHERE l.identificador = 'usuario.inexistente@cmdb.local' AND l.ip = '192.168.10.81' AND l.created_at = '2026-07-12 08:02:00'
);

INSERT INTO bitacora (usuario_id, modulo, accion, descripcion, ip, nivel)
SELECT @admin_id, 'SISTEMA', 'SEMILLA_DEMO_COMPLETA', 'Contenido demo completo cargado: inventario, asignaciones, licencias, solicitudes, presupuesto y QR.', '127.0.0.1', 'INFO'
WHERE NOT EXISTS (
    SELECT 1 FROM bitacora
    WHERE modulo = 'SISTEMA' AND accion = 'SEMILLA_DEMO_COMPLETA'
);

COMMIT;

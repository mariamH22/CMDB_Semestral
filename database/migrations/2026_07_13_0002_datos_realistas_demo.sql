-- ============================================================================
-- CMDB Integral - Datos realistas adicionales para demostracion
-- Fecha: 2026-07-13
--
-- Ejecutar DESPUES de:
--   database/migrations/2026_07_13_0001_seguridad_funcionalidades_pendientes.sql
--
-- No borra informacion, no cambia credenciales y no contiene configuracion
-- de Ubuntu/Nginx. Esta pensado para una base existente de pruebas.
-- ============================================================================

USE cmdb_integral;

INSERT IGNORE INTO colaboradores
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

INSERT IGNORE INTO usuarios (colaborador_id, nombre_usuario, email, password_hash, rol, activo, estado_cuenta, intentos_fallidos)
VALUES
((SELECT id FROM colaboradores WHERE email = 'ana.rodriguez@cmdb.local'), 'ana.rodriguez', 'ana.rodriguez@cmdb.local', '$2y$12$/SgfYbw4agT2YOEDLGPsouUxXXisi8fP.8htY6OuwZ/HI27IYY2eG', 'COLABORADOR', 1, 'ACTIVO', 0),
((SELECT id FROM colaboradores WHERE email = 'miguel.rios@cmdb.local'), 'miguel.rios', 'miguel.rios@cmdb.local', '$2y$12$/SgfYbw4agT2YOEDLGPsouUxXXisi8fP.8htY6OuwZ/HI27IYY2eG', 'COLABORADOR', 1, 'ACTIVO', 0),
((SELECT id FROM colaboradores WHERE email = 'roberto.nunez@cmdb.local'), 'soporte', 'soporte@cmdb.local', '$2y$12$Q9DAchW14gC1HJzLGrrfJezVjS7HOlaoHFCHyyJer.4c8XeVenht2', 'OPERADOR', 1, 'ACTIVO', 0);

INSERT IGNORE INTO categorias (nombre, tipo, descripcion, activo) VALUES
('Servidores', 'HARDWARE', 'Servidores físicos y equipos de centro de datos.', 1),
('Periféricos', 'HARDWARE', 'Monitores, UPS, teclados, impresoras y accesorios.', 1),
('Seguridad Informática', 'SOFTWARE', 'Herramientas de protección, antivirus y monitoreo.', 1),
('Sistemas Operativos', 'SOFTWARE', 'Sistemas operativos de escritorio y servidor.', 1),
('Herramientas de Diseño', 'SOFTWARE', 'Software de diseño, edición y producción multimedia.', 1);

INSERT IGNORE INTO inventario
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
SELECT i.id, c.id, '2024-06-01', '192.168.10.45', 'Laptop con cargador USB-C y mouse inalámbrico.', 'ACTIVA'
FROM inventario i, colaboradores c
WHERE i.codigo_activo = 'ACT-0003' AND c.email = 'carlos.gomez@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM asignaciones a WHERE a.inventario_id = i.id AND a.estado = 'ACTIVA');

INSERT INTO asignaciones (inventario_id, colaborador_id, fecha_asignacion, ip_asignada, observaciones, estado)
SELECT i.id, c.id, '2023-11-10', NULL, 'Monitor instalado en puesto de Recursos Humanos.', 'ACTIVA'
FROM inventario i, colaboradores c
WHERE i.codigo_activo = 'ACT-0005' AND c.email = 'laura.vega@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM asignaciones a WHERE a.inventario_id = i.id AND a.estado = 'ACTIVA');

INSERT INTO asignaciones (inventario_id, colaborador_id, fecha_asignacion, ip_asignada, observaciones, estado)
SELECT i.id, c.id, '2024-10-10', '10.20.5.34', 'Teléfono IP con extensión 2204.', 'ACTIVA'
FROM inventario i, colaboradores c
WHERE i.codigo_activo = 'TEL-0003' AND c.email = 'ana.rodriguez@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM asignaciones a WHERE a.inventario_id = i.id AND a.estado = 'ACTIVA');

INSERT INTO licencia_asignaciones (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, fecha_fin, estado, observaciones)
SELECT i.id, c.id, 1, 1, '2025-01-10', NULL, 'ACTIVA', 'Suite completa para campañas institucionales.'
FROM inventario i, colaboradores c
WHERE i.codigo_activo = 'LIC-0002' AND c.email = 'ana.rodriguez@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM licencia_asignaciones la WHERE la.inventario_id = i.id AND la.colaborador_id = c.id AND la.estado = 'ACTIVA');

INSERT INTO licencia_asignaciones (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, fecha_fin, estado, observaciones)
SELECT i.id, c.id, 1, 1, '2025-02-03', NULL, 'ACTIVA', 'Licencia para revisión de material gráfico.'
FROM inventario i, colaboradores c
WHERE i.codigo_activo = 'LIC-0002' AND c.email = 'valeria.torres@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM licencia_asignaciones la WHERE la.inventario_id = i.id AND la.colaborador_id = c.id AND la.estado = 'ACTIVA');

INSERT INTO licencia_asignaciones (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, fecha_fin, estado, observaciones)
SELECT i.id, c.id, 1, 1, '2025-04-05', NULL, 'ACTIVA', 'Diseños de infraestructura y planos técnicos.'
FROM inventario i, colaboradores c
WHERE i.codigo_activo = 'LIC-0003' AND c.email = 'miguel.rios@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM licencia_asignaciones la WHERE la.inventario_id = i.id AND la.colaborador_id = c.id AND la.estado = 'ACTIVA');

INSERT INTO licencia_asignaciones (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, fecha_fin, estado, observaciones)
SELECT i.id, c.id, 1, 12, '2025-01-20', NULL, 'ACTIVA', 'Cupos instalados en laboratorio 2.'
FROM inventario i, colaboradores c
WHERE i.codigo_activo = 'LIC-0004' AND c.email = 'javier.chen@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM licencia_asignaciones la WHERE la.inventario_id = i.id AND la.colaborador_id = c.id AND la.estado = 'ACTIVA');

INSERT INTO licencia_asignaciones (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, fecha_fin, estado, observaciones)
SELECT i.id, c.id, 1, 3, '2025-06-05', NULL, 'ACTIVA', 'Agentes de mesa de ayuda.'
FROM inventario i, colaboradores c
WHERE i.codigo_activo = 'SW-0002' AND c.email = 'roberto.nunez@cmdb.local'
  AND NOT EXISTS (SELECT 1 FROM licencia_asignaciones la WHERE la.inventario_id = i.id AND la.colaborador_id = c.id AND la.estado = 'ACTIVA');

INSERT INTO necesidades (colaborador_id, categoria_id, tipo_necesidad, descripcion, prioridad, costo_estimado, estado, comentario_resolucion)
SELECT c.id, cat.id, 'EQUIPO', 'Equipo de escritorio para estación de autopréstamo en biblioteca.', 'ALTA', 780.00, 'PENDIENTE', NULL
FROM colaboradores c, categorias cat
WHERE c.email = 'daniela.morales@cmdb.local' AND cat.nombre = 'Equipo de Cómputo'
  AND NOT EXISTS (SELECT 1 FROM necesidades n WHERE n.colaborador_id = c.id AND n.descripcion = 'Equipo de escritorio para estación de autopréstamo en biblioteca.');

INSERT INTO necesidades (colaborador_id, categoria_id, tipo_necesidad, descripcion, prioridad, costo_estimado, estado, comentario_resolucion)
SELECT c.id, cat.id, 'EQUIPO', 'Punto de acceso adicional para mejorar cobertura del laboratorio 2.', 'MEDIA', 160.00, 'EN_REVISION', 'Validar canalización y punto de red disponible.'
FROM colaboradores c, categorias cat
WHERE c.email = 'javier.chen@cmdb.local' AND cat.nombre = 'Equipo de Red'
  AND NOT EXISTS (SELECT 1 FROM necesidades n WHERE n.colaborador_id = c.id AND n.descripcion = 'Punto de acceso adicional para mejorar cobertura del laboratorio 2.');

INSERT INTO necesidades (colaborador_id, categoria_id, tipo_necesidad, descripcion, prioridad, costo_estimado, estado, comentario_resolucion)
SELECT c.id, cat.id, 'LICENCIA', 'Licencia adicional de diseño para apoyo temporal de campaña institucional.', 'MEDIA', 720.00, 'PENDIENTE', NULL
FROM colaboradores c, categorias cat
WHERE c.email = 'ana.rodriguez@cmdb.local' AND cat.nombre = 'Herramientas de Diseño'
  AND NOT EXISTS (SELECT 1 FROM necesidades n WHERE n.colaborador_id = c.id AND n.descripcion = 'Licencia adicional de diseño para apoyo temporal de campaña institucional.');

INSERT INTO necesidades (colaborador_id, categoria_id, tipo_necesidad, descripcion, prioridad, costo_estimado, estado, comentario_resolucion)
SELECT c.id, cat.id, 'SOFTWARE', 'Herramienta de monitoreo para alertas de disponibilidad de servicios críticos.', 'ALTA', 1250.00, 'EN_REVISION', 'Comparar opciones cloud y on-premise.'
FROM colaboradores c, categorias cat
WHERE c.email = 'miguel.rios@cmdb.local' AND cat.nombre = 'Seguridad Informática'
  AND NOT EXISTS (SELECT 1 FROM necesidades n WHERE n.colaborador_id = c.id AND n.descripcion = 'Herramienta de monitoreo para alertas de disponibilidad de servicios críticos.');

INSERT INTO inventario_estado_historial (inventario_id, usuario_id, estado_anterior, estado_nuevo, motivo, observacion)
SELECT i.id, 1, NULL, i.estado, 'Carga semilla realista', 'Estado inicial registrado durante carga de datos demo.'
FROM inventario i
WHERE i.codigo_activo IN ('ACT-0003','ACT-0004','ACT-0005','NET-0002','NET-0003','TEL-0003','SRV-0001','LIC-0002','LIC-0003','LIC-0004','SW-0002','ACT-0006','ACT-0007')
  AND NOT EXISTS (SELECT 1 FROM inventario_estado_historial h WHERE h.inventario_id = i.id AND h.motivo = 'Carga semilla realista');

INSERT INTO presupuestos (nombre, tipo, anio_inicio, anio_fin, total_estimado, estado, created_by)
SELECT 'Presupuesto tecnológico anual 2026', 'ANUAL', 2026, 2026, 2910.00, 'BORRADOR', 1
WHERE NOT EXISTS (SELECT 1 FROM presupuestos WHERE nombre = 'Presupuesto tecnológico anual 2026');

INSERT INTO presupuestos (nombre, tipo, anio_inicio, anio_fin, total_estimado, estado, created_by)
SELECT 'Plan quinquenal de renovación tecnológica 2026-2030', 'QUINQUENAL', 2026, 2030, 21850.00, 'BORRADOR', 1
WHERE NOT EXISTS (SELECT 1 FROM presupuestos WHERE nombre = 'Plan quinquenal de renovación tecnológica 2026-2030');

INSERT INTO presupuesto_detalles (presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, subtotal, anio)
SELECT p.id, c.id, NULL, 'EQUIPO', 'Reposición de equipos administrativos de alto uso.', 2, 780.00, 1560.00, 2026
FROM presupuestos p, categorias c
WHERE p.nombre = 'Presupuesto tecnológico anual 2026' AND c.nombre = 'Equipo de Cómputo'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Reposición de equipos administrativos de alto uso.');

INSERT INTO presupuesto_detalles (presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, subtotal, anio)
SELECT p.id, c.id, NULL, 'EQUIPO', 'Ampliación de cobertura inalámbrica en laboratorios.', 2, 160.00, 320.00, 2026
FROM presupuestos p, categorias c
WHERE p.nombre = 'Presupuesto tecnológico anual 2026' AND c.nombre = 'Equipo de Red'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Ampliación de cobertura inalámbrica en laboratorios.');

INSERT INTO presupuesto_detalles (presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, subtotal, anio)
SELECT p.id, c.id, NULL, 'SOFTWARE', 'Renovación y monitoreo de seguridad endpoint.', 1, 1030.00, 1030.00, 2026
FROM presupuestos p, categorias c
WHERE p.nombre = 'Presupuesto tecnológico anual 2026' AND c.nombre = 'Seguridad Informática'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Renovación y monitoreo de seguridad endpoint.');

INSERT INTO presupuesto_detalles (presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, subtotal, anio)
SELECT p.id, c.id, NULL, 'EQUIPO', 'Renovación progresiva de laptops y desktops.', 20, 850.00, 17000.00, 2027
FROM presupuestos p, categorias c
WHERE p.nombre = 'Plan quinquenal de renovación tecnológica 2026-2030' AND c.nombre = 'Equipo de Cómputo'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Renovación progresiva de laptops y desktops.');

INSERT INTO presupuesto_detalles (presupuesto_id, categoria_id, necesidad_id, tipo_necesidad, descripcion, cantidad, costo_unitario, subtotal, anio)
SELECT p.id, c.id, NULL, 'EQUIPO', 'Reserva para actualización de infraestructura de virtualización.', 1, 4850.00, 4850.00, 2028
FROM presupuestos p, categorias c
WHERE p.nombre = 'Plan quinquenal de renovación tecnológica 2026-2030' AND c.nombre = 'Servidores'
  AND NOT EXISTS (SELECT 1 FROM presupuesto_detalles d WHERE d.presupuesto_id = p.id AND d.descripcion = 'Reserva para actualización de infraestructura de virtualización.');

INSERT INTO noticias (usuario_id, titulo, resumen, contenido, publicada)
SELECT 1, 'Buenas prácticas para la devolución de equipos', 'La revisión técnica evita reasignar equipos con fallas ocultas.', 'Antes de volver a marcar un equipo como disponible, registre motivo de devolución, estado físico y observación técnica. Esto mantiene trazabilidad y reduce incidentes posteriores.', 1
WHERE NOT EXISTS (SELECT 1 FROM noticias WHERE titulo = 'Buenas prácticas para la devolución de equipos');

INSERT INTO noticias (usuario_id, titulo, resumen, contenido, publicada)
SELECT 1, 'Control de licencias por cupos', 'Asignar cupos permite medir uso real y planificar renovaciones.', 'Las licencias con cantidad superior a uno deben administrarse por cupos asignados a colaboradores o áreas responsables. La CMDB ayuda a comparar cupos disponibles contra demanda.', 1
WHERE NOT EXISTS (SELECT 1 FROM noticias WHERE titulo = 'Control de licencias por cupos');

INSERT INTO noticias (usuario_id, titulo, resumen, contenido, publicada)
SELECT 1, 'QR para activos críticos', 'El QR facilita identificar activos sin exponer información sensible.', 'Cada activo puede consultarse desde su detalle interno mediante QR. El código no debe contener claves de licencia ni datos confidenciales.', 1
WHERE NOT EXISTS (SELECT 1 FROM noticias WHERE titulo = 'QR para activos críticos');

INSERT INTO bitacora (usuario_id, modulo, accion, descripcion, ip, nivel)
SELECT 1, 'SISTEMA', 'SEMILLA_REALISTA', 'Datos realistas adicionales cargados para demostración.', '127.0.0.1', 'INFO'
WHERE NOT EXISTS (SELECT 1 FROM bitacora WHERE modulo = 'SISTEMA' AND accion = 'SEMILLA_REALISTA');

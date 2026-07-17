# Auditoria post correccion

Fecha: 2026-07-14

## Cambios aplicados

| Archivo | Razon | Evidencia |
| --- | --- | --- |
| `database/cmdb_integral.sql`, `database/install/fresh_install.sql`, `database/install/cmdb_integral_full_install.sql` | Sincronizar instalacion limpia con migraciones: RSA, auditoria, QR seguro, licencias, solicitudes, presupuesto, devoluciones y revision. | `php tests/InstallSchemaSqlTest.php` devuelve `OK InstallSchemaSqlTest`. |
| `database/install/cmdb_integral_full_install.sql`, `database/install/fresh_install.sql`, `database/cmdb_integral.sql`, `database/migrations/2026_07_13_0010_solicitudes_historial_formal.sql` | Alinear constraints/indices de `necesidades_historial` con la base real para que la instalacion limpia produzca el mismo esquema final. | `php database/tools/verify_install_sql_against_live_schema.php database/install/cmdb_integral_full_install.sql` OK; tambien OK para `fresh_install.sql` y `database/cmdb_integral.sql`. |
| `database/tools/verify_clean_install_app_smoke.php` | Probar que una instalacion limpia no solo coincide en columnas, sino que es usable por los modelos reales de la aplicacion. | OK para `cmdb_integral_full_install.sql` y `fresh_install.sql`: inventario, dashboard, donaciones historicas, devoluciones, licencias, QR, necesidades y presupuestos responden contra base temporal. |
| `database/migrations/2026_07_13_0003_*.sql` a `0013_*.sql` | Eliminar sintaxis no portable `ADD COLUMN IF NOT EXISTS` / `CREATE INDEX IF NOT EXISTS` que produjo error 1064. | `rg -n "ADD COLUMN IF NOT EXISTS|CREATE (UNIQUE )?INDEX IF NOT EXISTS" database/migrations` sin coincidencias reales. |
| `database/tools/verify_environment.php` | Verificar extensiones PHP, conexion PDO y contrato real de esquema. | Verificador final OK contra MySQL real `cmdb_integral`. |
| `database/tools/configure_mysql_credentials.php`, `README.md` | Capturar credenciales reales desde terminal con password oculto, validar PDO antes de guardar y crear `config.local.php` fuera de Git. | `php -l database/tools/configure_mysql_credentials.php` OK; prueba con password falso devuelve `1045` y no crea `config.local.php`. |
| `app/Core/Validator.php`, `app/Core/Sanitizer.php`, `app/Core/ReportService.php`, `tests/Phase1SecurityTest.php` | Evitar error fatal si falta `mbstring` durante pruebas o formularios; la instalacion sigue exigiendo `mbstring` y el verificador lo comprueba. | `php tests/Phase1SecurityTest.php` OK; `php database/tools/verify_environment.php` confirma `mbstring`. |
| `database/tools/apply_incremental_migrations.php` | Respaldar base real y aplicar migraciones incrementales faltantes sin exponer credenciales. | Respaldos creados en `database/backups/`; migraciones `0003` a `0013` y `2026_07_14_0002` OK; verificador final OK. |
| `database/tools/verify_install_sql_against_live_schema.php`, `tests/InstallSchemaSqlTest.php` | Evitar falso positivo del instalador: importar SQL en base temporal y comparar tablas, columnas, tipos e indices con el esquema real. | El instalador recomendado importa en MySQL real y coincide con `cmdb_integral`; `InstallSchemaSqlTest` prohibe constraints obsoletas. |
| `app/Models/Assignment.php`, `app/Models/ReturnReview.php`, `app/Controllers/AssignmentsController.php`, `app/Controllers/PortalController.php`, vistas/rutas, `database/migrations/2026_07_14_0002_devolucion_recepcion_independiente.sql` | Separar solicitud, recepcion fisica y revision tecnica. La asignacion solo cierra al finalizar revision; `estado_fisico` queda `NULL` hasta recepcion. | `php database/tools/verify_return_flow_real_db.php` OK contra MySQL real. |
| `app/Core/InventoryImagePolicy.php`, `app/Models/InventoryItem.php`, `app/Core/Controller.php`, `app/Core/Validator.php`, `app/Controllers/InventoryController.php`, `app/Views/inventory/form.php`, `public/assets/js/app.js`, `database/tools/verify_hardware_image_policy_real_db.php` | Exigir minimo dos imagenes para hardware en frontend, controlador y modelo; generar miniatura real con GD o fallar sin reutilizar el original; limpiar archivos subidos si falla la persistencia. | `php tests/Phase7AImagesLocationTest.php` OK; `php database/tools/verify_hardware_image_policy_real_db.php` OK contra MySQL real; `node --check` OK. |
| `app/Core/QrPublicPayload.php`, `app/Models/InventoryQr.php`, `app/Views/inventory/qr_lookup.php`, `database/tools/verify_qr_public_payload_real_db.php` | Mostrar codigo, nombre, categoria, marca, estado, precio y fecha de adquisicion sin exponer token, colaborador ni claves. | `php tests/Phase5BQrSecurityTest.php` OK; `php database/tools/verify_qr_public_payload_real_db.php` OK contra MySQL real. |
| `app/Models/InventoryItem.php`, `app/Core/ReportService.php`, `database/migrations/2026_07_14_0003_donacion_retira_activo.sql`, datos semilla, `database/tools/verify_donation_retirement_real_db.php`, `tests/Phase6CReportsExportTest.php` | DONADO queda `activo=0`, fuera del inventario operativo, pero visible en reporte historico independiente de donaciones. | `php tests/Phase6CReportsExportTest.php` OK; `php database/tools/verify_donation_retirement_real_db.php` OK contra MySQL real. |
| `.env.example`, `README.md`, `docs/ARQUITECTURA_SOLID.md`, `docs/DEPENDENCIAS_SEGURIDAD.md`, `docs/IMAGENES_UBICACIONES.md`, `docs/QR_SEGURO.md`, matrices y pruebas manuales | Aclarar que `.env` no se carga automaticamente, documentar extensiones obligatorias y actualizar QR, presupuesto, recepcion formal, imagenes y donaciones. | Revision textual con `rg`; suite completa OK. |

## Certificacion CLI con base real

Las credenciales reales quedaron en `app/Config/config.local.php` con permisos `0600` y fuera del control de versiones. Se aplicaron migraciones incrementales sobre `cmdb_integral` con respaldo previo:

```text
database/backups/cmdb_integral_20260714_105007_pre_migrations.sql
database/backups/cmdb_integral_20260714_115000_pre_migrations.sql
database/backups/cmdb_integral_20260714_120713_pre_migrations.sql
```

Resultado final:

```text
Verificacion completa: entorno y esquema principal listos.
[OK] Migraciones aplicadas y esquema real verificado.
```

Verificacion funcional de requisito 21:

```text
[OK] Solicitud registrada sin cerrar asignacion y sin receptor fisico.
[OK] Recepcion por el mismo solicitante rechazada.
[OK] Recepcion fisica independiente registrada con condicion y accesorios.
[OK] Revision tecnica cierra asignacion y libera el activo segun resultado.
```

Verificacion funcional de donaciones:

```text
[OK] Base real no contiene donaciones activas en inventario operativo.
[OK] Flujo formal de donacion deja estado DONADO y activo = 0.
[OK] Inventario general excluye el activo donado por activo = 0.
[OK] Reporte de donaciones usa historico independiente e incluye donados inactivos.
```

## Limitacion de certificacion por navegador

Si la auditoria se ejecuta en un entorno sin MySQL/MariaDB operativo o sin extensiones `pdo_mysql`, `mbstring` y `gd`, no es posible certificar por navegador ni por persistencia real:

- Login.
- CRUD completos.
- Carga de imagenes.
- Descargas.
- Reportes.
- Devoluciones.
- Donaciones.
- Presupuestos.
- Flujos completos por rol.

Esta limitacion no prueba que los modulos fallen; solo deja la certificacion final pendiente hasta ejecutar `docs/VALIDACION_FINAL_XAMPP_WAMPSERVER.md` sobre una base limpia en XAMPP o WampServer.

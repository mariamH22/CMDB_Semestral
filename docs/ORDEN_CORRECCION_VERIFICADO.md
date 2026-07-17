# Orden de correccion verificado

Fecha: 2026-07-14

Este documento resume el orden recomendado de correccion y la evidencia disponible en el proyecto.

| Orden | Correccion | Estado | Evidencia |
| --- | --- | --- | --- |
| 1 | Consolidar todas las migraciones en un instalador SQL final. | Completado | `database/install/cmdb_integral_full_install.sql`, `database/install/fresh_install.sql` y `database/cmdb_integral.sql` contienen el esquema final. |
| 2 | Probar el instalador sobre una base completamente vacia. | Completado por CLI | `php database/tools/verify_install_sql_against_live_schema.php database/install/cmdb_integral_full_install.sql` compara esquema; `php database/tools/verify_clean_install_app_smoke.php database/install/cmdb_integral_full_install.sql` importa una base temporal y consulta modelos reales de inventario, dashboard, devoluciones, licencias, QR, necesidades y presupuestos. |
| 3 | Separar solicitud, recepcion y revision tecnica de devoluciones. | Completado | `php database/tools/verify_return_flow_real_db.php` prueba solicitud sin cierre inmediato, recepcion fisica independiente y cierre tecnico. |
| 4 | Exigir dos imagenes para hardware. | Completado | `php tests/Phase7AImagesLocationTest.php` y `php database/tools/verify_hardware_image_policy_real_db.php`. |
| 5 | Agregar precio y fecha de adquisicion a la consulta QR autorizada/publica por token. | Completado | `app/Views/inventory/qr_lookup.php` muestra `Precio` y `Fecha de adquisicion`; `php database/tools/verify_qr_public_payload_real_db.php`. |
| 6 | Retirar equipos donados del inventario operativo. | Completado | `database/migrations/2026_07_14_0003_donacion_retira_activo.sql`; `php database/tools/verify_donation_retirement_real_db.php`. |
| 7 | Activar y documentar extensiones PHP. | Completado y verificado | `README.md`, `docs/DEPENDENCIAS_SEGURIDAD.md` y `php database/tools/verify_environment.php` comprueban `pdo_mysql`, `mbstring`, `gd`, `fileinfo`, `openssl` y `sodium`. |
| 8 | Corregir la configuracion de `.env` o eliminar instrucciones confusas. | Completado | `.env.example` aclara que no se carga automaticamente; la configuracion real usa variables del sistema o `app/Config/config.local.php`. |
| 9 | Ejecutar pruebas de integracion con MySQL. | Completado por CLI; pendiente validacion final por navegador | Verificadores reales de devoluciones, imagenes, QR y donaciones pasan contra MySQL. La validacion final de navegador esta documentada en `docs/VALIDACION_FINAL_XAMPP_WAMPSERVER.md`. |

## Comandos de cierre recomendados

```bash
php database/tools/verify_environment.php
php database/tools/verify_install_sql_against_live_schema.php database/install/cmdb_integral_full_install.sql
php database/tools/verify_clean_install_app_smoke.php database/install/cmdb_integral_full_install.sql
php database/tools/verify_return_flow_real_db.php
php database/tools/verify_hardware_image_policy_real_db.php
php database/tools/verify_qr_public_payload_real_db.php
php database/tools/verify_donation_retirement_real_db.php
for test in tests/*.php; do php "$test" || exit 1; done
node --check public/assets/js/app.js
```

## Limitacion

Estos comandos prueban reglas de negocio y persistencia por CLI. La certificacion final de login, CRUD, descargas y flujos completos por rol debe hacerse por navegador en XAMPP/WampServer limpio, como se describe en:

```text
docs/VALIDACION_FINAL_XAMPP_WAMPSERVER.md
```

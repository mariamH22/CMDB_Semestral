# Reporte de pruebas

Fecha: 2026-07-14

## Ciclo 1

Comandos ejecutados:

```bash
find app public tests database/tools -name '*.php' -print0 | xargs -0 -n1 php -l
for f in tests/*.php; do php "$f" || exit 1; done
node --check public/assets/js/app.js
php database/tools/verify_environment.php
```

Resultado:

- Lint PHP completo: OK.
- Pruebas PHP: OK (`InstallSchemaSqlTest`, fases 1 a 7A).
- JavaScript: OK.
- Verificador de entorno inicial: extensiones OK; conexion DB real bloqueada hasta configurar credenciales reales.
- Evidencia funcional agregada: `Phase6CReportsExportTest` valida que donaciones historicas inactivas sigan disponibles en reportes.

## Ciclo 2

Comandos ejecutados:

```bash
find app public tests database/tools -name '*.php' -print0 | xargs -0 -n1 php -l
for f in tests/*.php; do php "$f" || exit 1; done
node --check public/assets/js/app.js
```

Resultado:

- Lint PHP completo: OK.
- Pruebas PHP: OK.
- JavaScript: OK.
- `php tests/Phase6CReportsExportTest.php`: OK, incluyendo fila `DONADO` con `activo=0`.

## Ciclo 3

Comandos ejecutados:

```bash
find app public tests database/tools -name '*.php' -print0 | xargs -0 -n1 php -l
for f in tests/*.php; do php "$f" || exit 1; done
node --check public/assets/js/app.js
```

Resultado:

- Lint PHP completo: OK.
- Pruebas PHP: OK.
- JavaScript: OK.

## Verificacion de compatibilidad SQL

```bash
rg -n "ADD COLUMN IF NOT EXISTS|CREATE (UNIQUE )?INDEX IF NOT EXISTS" database/migrations
```

Resultado: sin coincidencias reales. Las migraciones ya no usan la sintaxis que causo `ERROR 1064`.

## Verificacion del instalador recomendado

Comandos ejecutados:

```bash
php database/tools/verify_install_sql_against_live_schema.php database/install/cmdb_integral_full_install.sql
php database/tools/verify_install_sql_against_live_schema.php database/install/fresh_install.sql
php database/tools/verify_install_sql_against_live_schema.php database/cmdb_integral.sql
php database/tools/verify_clean_install_app_smoke.php database/install/cmdb_integral_full_install.sql
php database/tools/verify_clean_install_app_smoke.php database/install/fresh_install.sql
```

Resultado:

- `database/install/cmdb_integral_full_install.sql`: importa en MySQL real y coincide con `cmdb_integral`.
- `database/install/fresh_install.sql`: importa en MySQL real y coincide con `cmdb_integral`.
- `database/cmdb_integral.sql`: importa en MySQL real y coincide con `cmdb_integral`.
- Smoke de instalacion limpia: `cmdb_integral_full_install.sql` y `fresh_install.sql` crean una base temporal utilizable por los modelos reales.

Evidencia del smoke:

```text
[OK] Categorias activas consultadas por modelo: 11.
[OK] Inventario operativo por modelo: 17 activo(s), 18 historico(s).
[OK] Asignaciones activas consultadas por modelo: 4.
[OK] Dashboard de reportes responde con inventario, asignaciones, donaciones e historial.
[OK] Donaciones historicas visibles fuera del inventario operativo: 1.
[OK] Esquema de devoluciones formales disponible.
[OK] Esquema de asignaciones de licencias disponible.
[OK] QR publico consultado con precio y fecha de adquisicion.
[OK] Tablas avanzadas presentes: necesidades, historial, presupuestos, licencias e imagenes.
```

## Verificador de entorno/base real

Configurador de credenciales:

```bash
php -l database/tools/configure_mysql_credentials.php
printf 'localhost\ncmdb_integral\nlaravel_user\nclave_incorrecta\nutf8mb4\n' | php database/tools/configure_mysql_credentials.php
```

Resultado:

- Sintaxis del configurador: OK.
- Con password falso: MySQL devuelve `1045` y el script no guarda `app/Config/config.local.php`.

Dentro del sandbox:

```text
[OK] Extension PHP pdo_mysql cargada.
[OK] Extension PHP mbstring cargada.
[OK] Extension PHP gd cargada.
[OK] Extension PHP fileinfo cargada.
[OK] Extension PHP openssl cargada.
[OK] Extension PHP sodium cargada.
[ERROR] No se pudo conectar a la base real: SQLSTATE[HY000] [2002] Operation not permitted
```

Fuera del sandbox:

```text
[OK] Extension PHP pdo_mysql cargada.
[OK] Extension PHP mbstring cargada.
[OK] Extension PHP gd cargada.
[OK] Extension PHP fileinfo cargada.
[OK] Extension PHP openssl cargada.
[OK] Extension PHP sodium cargada.
[ERROR] No se pudo conectar a la base real: SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: NO)
```

## Validacion final con base real

Luego de configurar credenciales reales con:

```bash
php database/tools/configure_mysql_credentials.php
```

Se aplicaron las migraciones incrementales faltantes:

```bash
php database/tools/apply_incremental_migrations.php
```

Resultado:

- Respaldo previo: `database/backups/cmdb_integral_20260714_105007_pre_migrations.sql`.
- Respaldo adicional: `database/backups/cmdb_integral_20260714_115000_pre_migrations.sql`.
- Migraciones aplicadas: `2026_07_13_0003` a `2026_07_13_0013` y `2026_07_14_0002`.
- Verificador final: OK.

```text
Verificacion completa: entorno y esquema principal listos.
[OK] Migraciones aplicadas y esquema real verificado.
```

## Requisito 21 - devolucion con recepcion independiente

Comando ejecutado contra MySQL real:

```bash
php database/tools/verify_return_flow_real_db.php
```

Resultado:

```text
[OK] Asignacion temporal creada y activo queda ASIGNADO.
[OK] Solicitud registrada sin cerrar asignacion y sin receptor fisico.
[OK] Recepcion por el mismo solicitante rechazada.
[OK] Recepcion fisica independiente registrada con condicion y accesorios.
[OK] Revision tecnica cierra asignacion y libera el activo segun resultado.

Verificacion completa: flujo de devolucion independiente cumple requisito 21.
```

## Politica de imagenes para hardware

Comandos ejecutados:

```bash
php tests/Phase7AImagesLocationTest.php
php database/tools/verify_hardware_image_policy_real_db.php
```

Resultado:

```text
OK Phase7AImagesLocationTest
[OK] Hardware con cero imagenes rechazado antes de persistir.
[OK] Hardware con una sola imagen rechazado antes de persistir.
[OK] Hardware con dos imagenes fue persistido y consultado con dos rutas en MySQL real.

Verificacion completa: politica de dos imagenes para hardware cumple en base real.
```

## QR publico academico

Comandos ejecutados:

```bash
php tests/Phase5BQrSecurityTest.php
php database/tools/verify_qr_public_payload_real_db.php
```

Resultado:

```text
OK Phase5BQrSecurityTest
[OK] Payload publico incluye codigo, nombre, categoria, marca, estado, precio y fecha de adquisicion sin secretos.
[OK] Acceso publico registra contador y fecha.
[OK] Token revocado queda bloqueado.

Verificacion completa: QR publico cumple campos academicos y controles de seguridad en base real.
```

## Donacion fuera del inventario operativo

Comandos ejecutados:

```bash
php tests/Phase6CReportsExportTest.php
php database/tools/verify_donation_retirement_real_db.php
```

Resultado:

```text
OK Phase6CReportsExportTest
[OK] Base real no contiene donaciones activas en inventario operativo.
[OK] Flujo formal de donacion deja estado DONADO y activo = 0.
[OK] Inventario general excluye el activo donado por activo = 0.
[OK] Reporte de donaciones usa historico independiente e incluye donados inactivos.

Verificacion completa: donacion retira del inventario operativo y conserva reporte historico en base real.
```

## Ciclo final posterior a MySQL real

Comandos ejecutados:

```bash
find app public tests database/tools -name '*.php' -print0 | xargs -0 -n1 php -l
for f in tests/*.php; do php "$f" || exit 1; done
node --check public/assets/js/app.js
php database/tools/verify_environment.php
```

Resultado:

- Lint PHP completo: OK.
- Pruebas PHP: OK.
- JavaScript: OK.
- Verificador real MySQL/MariaDB: OK.

## Alcance de certificacion

Las pruebas anteriores certifican sintaxis, reglas de negocio automatizadas, verificadores CLI y persistencia real disponible en este workspace. No sustituyen una validacion final por navegador si el entorno auditor no tiene MySQL/MariaDB ni extensiones `pdo_mysql`, `mbstring` y `gd`.

Cuando falte ese entorno, los siguientes flujos quedan pendientes de certificacion final en XAMPP/WampServer limpio:

- Login.
- CRUD completos.
- Carga de imagenes.
- Descargas y reportes.
- Devoluciones.
- Donaciones.
- Presupuestos.
- Flujos completos por rol.

Guia de validacion final:

```text
docs/VALIDACION_FINAL_XAMPP_WAMPSERVER.md
```

## Correccion mbstring, env y documentacion

Comandos ejecutados:

```bash
php tests/Phase1SecurityTest.php
php tests/Phase7AImagesLocationTest.php
php database/tools/verify_environment.php
```

Resultado:

```text
OK Phase1SecurityTest
OK Phase7AImagesLocationTest
[OK] Extension PHP pdo_mysql cargada.
[OK] Extension PHP mbstring cargada.
[OK] Extension PHP gd cargada.
[OK] Extension PHP fileinfo cargada.
[OK] Extension PHP openssl cargada.
[OK] Extension PHP sodium cargada.

Verificacion completa: entorno y esquema principal listos.
```

Notas:

- `Validator`, `Sanitizer` y filtros de reportes tienen fallback para no producir error fatal si falta `mbstring`.
- La instalacion documenta y verifica `pdo_mysql`, `mbstring`, `gd`, `fileinfo`, `openssl` y `sodium`.
- `.env.example` indica que no se carga automaticamente; se recomienda `config.local.php` o el configurador CLI.
- La documentacion tecnica fue actualizada para QR, presupuesto, recepcion formal, imagenes obligatorias y donaciones.

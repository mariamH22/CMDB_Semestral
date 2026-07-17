# Validacion final en XAMPP o WampServer

Fecha: 2026-07-14

## Limitacion de certificacion

La auditoria ejecutada en un entorno sin MySQL/MariaDB operativo y sin extensiones `pdo_mysql`, `mbstring` y `gd` no puede certificar por navegador ni por persistencia real los flujos completos del sistema.

En ese entorno limitado no deben marcarse como completamente verificados:

- Login.
- CRUD completos.
- Carga de imagenes.
- Descargas.
- Reportes.
- Devoluciones.
- Donaciones.
- Presupuestos.
- Flujos completos por rol.

Esto no demuestra que esos modulos fallen; solo significa que la certificacion final debe ejecutarse en una instalacion local completa.

## Entorno requerido

Antes de abrir el navegador, PHP debe tener activas estas extensiones:

```text
pdo_mysql
mbstring
gd
fileinfo
openssl
sodium
```

MySQL o MariaDB debe estar iniciado y debe existir una base limpia creada desde:

```text
database/install/cmdb_integral_full_install.sql
```

Tambien puede usarse:

```text
database/install/fresh_install.sql
database/cmdb_integral.sql
```

Estos archivos son instaladores limpios, no backups ni migraciones.

## Verificacion tecnica previa

Desde la carpeta del proyecto, ejecutar:

```bash
php database/tools/verify_environment.php
php database/tools/verify_install_sql_against_live_schema.php database/install/cmdb_integral_full_install.sql
php database/tools/verify_clean_install_app_smoke.php database/install/cmdb_integral_full_install.sql
find app public tests database/tools -name '*.php' -print0 | xargs -0 -n1 php -l
for test in tests/*.php; do php "$test" || exit 1; done
node --check public/assets/js/app.js
```

Si cualquiera de estos comandos falla por extensiones ausentes, base no disponible o credenciales incorrectas, la validacion final queda pendiente.

## Validacion por navegador

Usar una base limpia y registrar evidencia con fecha, usuario y captura o nota del resultado.

| Flujo | Resultado minimo esperado |
| --- | --- |
| Login | Inicio de sesion, cierre de sesion, bloqueo por intentos y desbloqueo administrativo. |
| Usuarios | Alta, edicion, baja logica y validaciones. |
| Categorias | Alta, edicion, baja logica y validaciones. |
| Colaboradores | Alta/edicion con foto opcional e historial de ubicacion. |
| Inventario | Hardware rechaza cero/una imagen y acepta dos imagenes; software puede quedar sin imagen. |
| Imagenes | Miniatura real generada con GD; imagen invalida rechazada. |
| Asignaciones | Activo disponible pasa a asignado y aparece en portal del colaborador. |
| Devoluciones | Solicitud, recepcion fisica independiente, revision tecnica y cierre. |
| Donaciones | Activo queda `DONADO`, `activo = 0`, fuera del inventario operativo y visible en reporte historico. |
| Necesidades | Solicitud desde portal, procesamiento administrativo e historial. |
| Presupuestos | Presupuesto anual y quinquenal con exportacion. |
| Reportes | Descarga Excel de inventario, asignaciones, licencias, vencimientos, solicitudes, devoluciones y revisiones. |
| QR | Generacion, consulta publica con precio/fecha, descarga SVG, revocacion y regeneracion. |
| Auditoria/RSA | Bitacora visible; RSA solo si las llaves externas estan configuradas. |

## Criterio de cierre

El proyecto solo debe marcarse como validado finalmente cuando:

```text
XAMPP/WampServer probado: SI
Base limpia importada: SI
verify_environment.php: OK
Suite tests/*.php: OK
Flujos por navegador: OK
Reportes descargados y abiertos: OK
Imagenes probadas con GD: OK
Evidencia guardada fuera del repositorio: SI
```

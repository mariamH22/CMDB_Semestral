# CMDB Integral - Inventario de Hardware y Software

> Proyecto semestral de **Desarrollo Web VII - I Semestre 2026**.  
> Tema: **CMDB (Configuration Management Database)**.
> Integrantes
Jorge Osorio — 3-754-696
Ahmed Díaz — 8-974-474
Obed Alvarado — 8-1015-90
Christian Domínguez — 8-999-892
Mariam Harris — 1-756-2331

Grupo: 1GS132
I Semestre 2026
Panamá, 20 de julio de 2026

VIDEO!!!!!!!------>>>    https://drive.google.com/file/d/14eCw15HJCfSoKaqyt5iwfXnNSatpok1L/view?usp=drivesdk      <<-----ENLACE DE VIDEO 

## 1. Propósito

CMDB Integral administra activos de hardware, software y licencias. El sistema permite conocer:

- Qué equipos existen, su costo, serie, marca, fecha de ingreso y vida útil.
- Qué colaborador tiene cada equipo, desde cuándo, en qué departamento, ubicación e IP.
- Qué activos están disponibles, dañados, en descarte, donados o cerca de depreciación.
- Qué licencias de software están disponibles sin asignar.
- Qué necesidades de tecnología solicitan los colaboradores.
- Qué cambios y eventos de seguridad se registran en la bitácora.

## 2. Funcionalidades implementadas

| Módulo | Funcionalidad |
|---|---|
| Login y seguridad | Hash BCRYPT, políticas de contraseña de 8 a 64 caracteres, bloqueo automático en el tercer intento fallido, bitácora de intentos con IP y fecha. |
| Usuarios | Alta, consulta, edición, baja lógica mediante el campo `activo`, desbloqueo de cuentas. |
| Categorías | Categorías de Hardware, Software, Equipo de Red, Equipo de Cómputo, Telefonía y Licencias. |
| Colaboradores | Datos personales, identificación única, departamento, ubicación, contacto y foto. |
| Inventario | CRUD de activos y software: costo, marca, modelo, serie, fecha, imágenes validadas, miniatura, vida útil, estado y licencias. |
| Integridad | Firma HMAC almacenada en la base de datos para detectar manipulación de los campos críticos de cada activo. |
| Asignaciones | Custodia de activos a colaboradores con IP, fecha de asignación, devolución formal y revisión técnica. |
| Estados especiales | Equipo disponible, asignado, dañado, descarte, donado o en mantenimiento. |
| Licencias | Registro de licencias, proveedor, vencimiento, URL, observaciones y control de cupos por colaborador. |
| Necesidades | Solicitudes formales de equipo, software o licencia con justificación, cantidad, costo unitario estimado, año objetivo, historial, procesador y respuesta administrativa. |
| QR | QR seguro por activo con token aleatorio, consulta pública limitada, descarga SVG, impresión, revocación y regeneración. |
| Presupuesto | Presupuesto anual o quinquenal con cantidad real, costo unitario estimado, inflación, crecimiento, filtros, proyección por año y separación de registros sin costo. |
| Reportes | Inventario, responsables, categorías, asignados por categoría, disponibles, reparación, donaciones, descartes, licencias, cupos, vencimientos, depreciación, solicitudes, devoluciones, revisiones e historial de estados exportables a Excel compatible (`.xls`). |
| Portal del Colaborador | Consulta de equipos, solicitudes, historial de accesos, cambio y recuperación de contraseña. |
| Noticias | Página pública con noticias sobre hardware, software e inventario. |

## 3. Tecnologías

- PHP 8.1 o superior.
- MySQL / MariaDB.
- Apache con `mod_rewrite`.
- PDO con consultas preparadas.
- HTML, CSS y JavaScript nativo.
- WampServer para ejecución local.

### Extensiones PHP obligatorias

Antes de importar la base o iniciar sesión, confirme que PHP tenga activas estas extensiones:

```text
pdo_mysql
mbstring
gd
fileinfo
openssl
sodium
```

El verificador del proyecto las comprueba junto con el esquema real:

```bash
php database/tools/verify_environment.php
```

`mbstring` se usa para manejo seguro de cadenas en validaciones y formularios. `gd` es obligatorio para decodificar imágenes y generar miniaturas reales.

## 4. Instalación en WampServer

1. Copie la carpeta `CMDB_Semestral` dentro de:

   ```text
   C:\wamp64\www\
   ```

2. Abra phpMyAdmin:

   ```text
   http://localhost/phpmyadmin/
   ```

3. Abra la pestaña **SQL** en `phpMyAdmin` y ejecute el archivo de instalación limpia **solo si la base no existe o está vacía**:

   ```text
   database/install/fresh_install.sql
   ```

   También existe una copia con nombre explícito para instalación completa:

   ```text
   database/install/cmdb_integral_full_install.sql
   ```

   Si no puedes o no quieres usar `fresh_install.sql`, puedes usar `database/cmdb_integral.sql`.
   En todos los casos, son archivos de **instalación nueva**: no son backups, no son migraciones y no deben ejecutarse sobre una base con datos.

   Para auditar que el instalador recomendado coincide con el esquema real antes de entregarlo o moverlo a XAMPP:

   ```bash
   php database/tools/verify_install_sql_against_live_schema.php database/install/cmdb_integral_full_install.sql
   php database/tools/verify_clean_install_app_smoke.php database/install/cmdb_integral_full_install.sql
   ```

   El primer comando importa el SQL en una base temporal, compara tablas, columnas, tipos e índices contra `cmdb_integral` y elimina la base temporal al finalizar. El segundo crea otra base temporal y consulta modelos reales de inventario, dashboard, donaciones, devoluciones, licencias, QR, necesidades y presupuestos.

4. Configure secretos operativos y, si hace falta, la conexión local:

   - Copie `app/Config/config.local.example.php` como `app/Config/config.local.php`.
   - También puede ejecutar `php database/tools/configure_mysql_credentials.php` para guardar las credenciales reales con contraseña oculta.
   - `.env.example` es solo una referencia de variables de servidor. Copiarlo como `.env` en XAMPP/WampServer no cambia la configuración porque el proyecto no carga archivos `.env` automáticamente.
   - En WampServer predeterminado puede dejar:

     ```php
     'db' => [
         'host' => 'localhost',
         'database' => 'cmdb_integral',
         'user' => 'root',
         'password' => '',
         'charset' => 'utf8mb4',
     ],
     ```

   - Asigne un valor aleatorio fuerte a `security.integrity_key`.
   - Para firmas RSA reales, configure además `security.key_store_path` y `security.key_encryption_key` fuera del repositorio.
   - Para cifrar claves de licencia nuevas, configure `security.license_key_encryption_key` o la variable externa `CMDB_LICENSE_KEY_ENCRYPTION_KEY`.
   - Mantenga `config.local.php` fuera del repositorio.
   - Si la clave HMAC no está configurada, el sistema no usará una clave predeterminada: registrará el activo, omitirá la firma nueva y mostrará el estado como pendiente de configuración.

5. Revise `app/Config/config.php`. Para WampServer con configuración predeterminada se utiliza:

   ```php
   'host' => 'localhost',
   'database' => 'cmdb_integral',
   'user' => 'root',
   'password' => '',
   ```

   Si otro entorno necesita credenciales distintas, no edite `config.php`; use `config.local.php` o variables de entorno.

6. Active `mod_rewrite` en Apache si las rutas muestran error 404. El archivo `public/.htaccess` ya está incluido.

7. Abra la aplicación:

   ```text
   http://localhost/CMDB_Semestral/public/
   ```

> Si renombra la carpeta, la URL debe tener el nuevo nombre. Ejemplo:  
> `http://localhost/NuevaCarpeta/public/`

8. Verifique entorno y esquema real:

   ```bash
   php database/tools/verify_environment.php
   ```

   Guías detalladas: `INSTALACION_XAMPP_WINDOWS.md` e `INSTALACION_NGINX_UBUNTU.md`.

## 5. Credenciales de prueba

| Rol | Correo | Contraseña | Acceso |
|---|---|---|---|
| Administrador | `admin@cmdb.local` | `Admin123*` | Control total, bitácora, usuarios, firmas y reportes. |
| Operador | `operador@cmdb.local` | `Operador123*` | CRUD de inventario, operación y consultas. |
| Colaborador | `sofia.martinez@cmdb.local` | `Colab123*` | Portal de colaborador, equipos asignados y solicitudes. |

> Las contraseñas del SQL están guardadas con `password_hash()` y BCRYPT; nunca se guardan en texto plano.

## 5.1. Ejecución local con Ubuntu, Nginx o MySQL con contraseña

La configuración local debe hacerse sin cambiar el código compartido ni romper WampServer.

1. Ejecute el configurador interactivo:

   ```bash
   php database/tools/configure_mysql_credentials.php
   ```

   El script solicita host, base, usuario y contraseña. La contraseña no se imprime, valida la conexión antes de guardar y ejecuta `database/tools/verify_environment.php`.

2. Si necesita hacerlo manualmente, copie la plantilla local:

   ```bash
   cp app/Config/config.local.example.php app/Config/config.local.php
   ```

   Edite solo `app/Config/config.local.php` y coloque las credenciales de su MySQL local:

   ```php
   <?php
   return [
       'db' => [
           'host' => 'localhost',
           'database' => 'cmdb_integral',
           'user' => 'TU_USUARIO_LOCAL',
           'password' => 'TU_PASSWORD_LOCAL',
           'charset' => 'utf8mb4',
       ],
       'security' => [
           'integrity_key' => '',
           'key_store_path' => '',
           'key_encryption_key' => '',
           'license_key_encryption_key' => '',
       ],
   ];
   ```

3. No suba `app/Config/config.local.php`; ya está excluido por `.gitignore`.

4. Si aparece este error:

   ```text
   SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: NO)
   ```

   significa que MySQL no acepta `root` sin contraseña. Corríjalo en `config.local.php` con un usuario/contraseña válidos.

5. Si prefiere variables de entorno, también puede definir:

   ```bash
   DB_HOST=localhost
   DB_NAME=cmdb_integral
   DB_USER=TU_USUARIO_LOCAL
   DB_PASSWORD=TU_PASSWORD_LOCAL
   ```

## 5.2. Inventario y CRUD

El módulo **Inventario** aparece en el menú para usuarios internos:

- `ADMIN`
- `OPERADOR`

El rol `COLABORADOR` solo ve el portal del colaborador y no ve el CRUD de inventario.

Para probar el CRUD:

1. Inicie sesión como administrador u operador.
2. Abra **Inventario** desde el menú lateral.
3. Use **Registrar activo** para crear hardware, software o licencias.
4. Use **Ver** para abrir el detalle.
5. Use **Editar** desde el detalle para modificar datos generales.
6. Los cambios de estado sensibles no se hacen desde edición general; se hacen desde los flujos formales de asignación, devolución, revisión, descarte o donación.

Si Inventario abre pero aparece vacío:

1. Confirme que ejecutó `database/install/fresh_install.sql` o `database/cmdb_integral.sql` en una base vacía.
2. En phpMyAdmin ejecute:

   ```sql
   SELECT COUNT(*) AS total FROM inventario;
   ```

3. Si `total` es `0`, faltan datos de instalación o demo.
4. Si sale error de columna o tabla, falta aplicar migraciones incrementales con backup previo.

Para una instalación local nueva y completa, use preferiblemente:

```text
database/install/cmdb_integral_full_install.sql
```

## 5.3. Cambios recientes importantes

- `config.local.php` ahora puede sobrescribir la conexión de base de datos local sin modificar `config.php`.
- WampServer sigue usando por defecto `localhost`, base `cmdb_integral`, usuario `root` y contraseña vacía.
- `OPERADOR` ahora puede gestionar el CRUD de inventario además de visualizarlo.
- `COLABORADOR` mantiene acceso limitado al portal.
- La política de contraseñas acepta de 8 a 64 caracteres con mayúscula, minúscula, número y símbolo. La creación y edición de usuarios pide confirmación de contraseña.
- El registro público sigue deshabilitado por seguridad; los usuarios nuevos se crean desde el módulo **Usuarios** con una cuenta `ADMIN`.
- Si `security.license_key_encryption_key` no está configurada, puede registrar licencias dejando vacío el campo de clave/serial. Para guardar claves de licencia reales, configure esa clave maestra solo en `config.local.php` o en una variable externa.
- No se agregaron archivos de Nginx, dominios locales, puertos alternos ni rutas absolutas al repositorio.

## 5.4. Flujo recomendado con GitHub y pruebas en Ubuntu/Nginx

El repositorio original es la fuente de verdad. Las mejoras de código se hacen allí y la carpeta de Nginx se usa solo como copia local de prueba.

1. Trabaje en la carpeta real del proyecto:

   ```bash
   cd /ruta/al/repositorio/CMDB_Semestral
   ```

2. Antes de subir cambios a GitHub, confirme que está dentro de un repositorio Git válido:

   ```bash
   git status --short
   git diff --stat
   ```

   Si aparece `fatal: not a git repository`, esa carpeta no es el repositorio Git correcto. Ubique la carpeta clonada real antes de hacer commit o push.

3. Verifique que no se estén agregando archivos locales o secretos:

   ```bash
   git status --short
   git ls-files app/Config/config.local.php
   git ls-files | grep -E '(^|/)(nginx\.conf|default|sites-available|sites-enabled)(/|$)'
   ```

   `app/Config/config.local.php` no debe aparecer como archivo versionado. Si aparece, retírelo del índice con el procedimiento acordado por el equipo antes de subir.

4. Para probar en Ubuntu/Nginx, sincronice una copia local hacia `/var/www/html/CMDB_Semestral` sin convertir esa copia en el lugar de desarrollo:

   ```bash
   sudo rsync -a \
     --exclude='.git' \
     --exclude='app/Config/config.local.php' \
     --exclude='database/backups/*' \
     --exclude='storage/logs/*' \
     --exclude='storage/cache/*' \
     --exclude='storage/security/*' \
     /ruta/al/repositorio/CMDB_Semestral/ \
     /var/www/html/CMDB_Semestral/
   ```

5. Si la copia de Nginx necesita credenciales distintas, cree o edite solo su archivo local:

   ```text
   /var/www/html/CMDB_Semestral/app/Config/config.local.php
   ```

   Esa configuración local no se sube a GitHub y no cambia el comportamiento de WampServer.

6. Los compañeros en Windows siguen usando:

   ```text
   http://localhost/CMDB_Semestral/public/
   ```

   Ubuntu/Nginx puede usar la URL que tenga configurada localmente, por ejemplo `http://localhost/CMDB_Semestral/`, sin introducir dependencias de Nginx dentro del código.

## 6. Arquitectura

La aplicación utiliza una estructura MVC:

```text
app/
├── Config/       Configuración local.
├── Core/         Router, PDO, CSRF, autenticación, validación, errores y firmas.
├── Controllers/  Coordinan cada caso de uso.
├── Models/       Operaciones PDO por entidad.
└── Views/        Formularios, pantallas y componentes visuales.

public/
├── index.php     Front Controller.
├── assets/       CSS y JavaScript.
└── uploads/      Imágenes de equipos y colaboradores.

database/
├── cmdb_integral.sql  Script principal de instalación limpia; no es backup.
├── install/
│   └── fresh_install.sql  Recomendado para instalaciones nuevas.
├── migrations/        Migraciones incrementales para bases ya instaladas.
└── backups/           Documentación de respaldo y restauración.
```

Los diagramas técnicos se resumen en `docs/DIAGRAMAS.md` e incluyen casos de uso, clases, componentes, MVC, entidad-relación, login, asignación, devolución, revisión, descarte, donación, RSA, rotación, revocación, estados, solicitudes, presupuesto, QR y auditoría.

## 7. Seguridad y calidad

- **CSRF:** todos los formularios que escriben datos incluyen un token CSRF.
- **PDO:** todas las consultas se ejecutan con sentencias preparadas.
- **Hash:** las contraseñas se guardan con BCRYPT.
- **Bloqueo:** tras tres credenciales erróneas el usuario cambia a `BLOQUEADO`.
- **Bitácora:** se guardan inicios de sesión, altas, cambios, asignaciones y solicitudes.
- **Audit Trail:** cuando la migración incremental está aplicada, la bitácora guarda old/new saneado, correlación, user-agent y una cadena `SHA-256(previous_hash + payload_canónico)`. Esto ofrece trazabilidad con detección criptográfica de alteraciones, no una base completamente inmutable.
- **Ciclo de vida de activos:** los cambios de estado pasan por una máquina formal. No se permite saltar de `ASIGNADO` a `DISPONIBLE`, ni donar o descartar desde el formulario general; esos cierres salen de devolución y revisión técnica.
- **Donaciones:** al cerrar una revisión como `DONADO`, el activo queda con `activo = 0` y sale del inventario operativo. El reporte de donaciones usa una consulta histórica independiente para seguir mostrando los equipos donados.
- **Licencias:** las claves nuevas se cifran con Sodium `secretbox` cuando está disponible y AES-256-GCM como respaldo. Si falta la clave maestra externa, se puede guardar la licencia sin clave/serial, pero no se guarda texto plano nuevo.
- **Portal:** los colaboradores solo pueden operar sus propios equipos/licencias. La solicitud de devolución valida pertenencia de la asignación en backend antes de registrar el flujo formal.
- **QR seguro:** la consulta pública usa token aleatorio, valida hash interno y muestra código, nombre, categoría, marca, estado, precio y fecha de adquisición. No expone token, colaborador ni claves; la revocación y regeneración quedan auditadas.
- **Solicitudes:** los estados formales son `EN_ESPERA`, `EN_TRAMITE`, `APROBADA` y `RECHAZADA`; aprobación y rechazo registran usuario procesador, respuesta administrativa, auditoría y firma RSA cuando esté configurada.
- **Presupuesto:** el total anual se calcula con `cantidad × costo_unitario_estimado`; el quinquenal proyecta cinco años con crecimiento e inflación, separando solicitudes sin costo.
- **Validación:** una clase central valida campos requeridos, correos, números, fechas, URLs, imágenes y contraseñas.
- **Imágenes:** hardware requiere mínimo dos imágenes; software puede registrarse sin imagen. Toda imagen se valida por extensión, MIME real, tamaño, dimensiones, decodificación y doble extensión peligrosa. Las miniaturas se generan con GD; si GD no está disponible, la carga falla en vez de reutilizar el original como miniatura.
- **Ubicación:** la ubicación del colaborador es opcional y los cambios se registran en historial con usuario, motivo, fechas y auditoría cuando la migración está aplicada.
- **DRY y SOLID:** clases separadas para conexión, autenticación, firma, validación, errores, respuestas y operaciones de cada modelo.
- **Integridad:** el inventario genera una firma HMAC con `serie + tipo_activo + estado + fecha_ingreso`. Si se altera el registro directamente en phpMyAdmin, el detalle del activo muestra una alerta.
- **HMAC operativo:** la clave de integridad no está en texto fijo en este repositorio. Se carga desde `app/Config/config.local.php` (ignorado por Git) o de la variable de entorno `CMDB_INTEGRITY_KEY`.
- **RSA:** las acciones sensibles se pueden registrar en `firmas_digitales` con RSA-SHA256 cuando exista una llave activa por usuario y su llave privada esté cifrada en un almacén local configurado fuera de `public/` y fuera del proyecto/repositorio. La bitácora permite revisar firmas recientes, validar resultados (`VALIDA`, `INVALIDA`, `LLAVE_REVOCADA`, `NO_VERIFICABLE`, `ERROR`) y administrar generación, rotación y revocación de llaves.
- **Alcance criptográfico:** la firma HMAC protege integridad técnica de activos, pero no equivale por sí sola a no repudio jurídico. El no repudio fuerte requiere política formal de custodia, rotación y protección de llaves privadas.
- **Errores:** existen respuestas específicas para `403`, `404`, `419` y `500`; los errores internos no deben mostrar rutas locales, SQL ni trazas al usuario.
- **Excel:** las exportaciones neutralizan celdas que empiezan por `=`, `+`, `-` o `@` para reducir riesgo de Formula Injection.
- **Dependencias:** el inventario de dependencias y alcance CVE del repositorio se documenta en `docs/DEPENDENCIAS_SEGURIDAD.md`.

## 7.1. Depreciación

El sistema usa `fecha_ingreso` y `vida_util_meses` para calcular la fecha límite estimada de depreciación:

```text
fecha_limite_depreciacion = fecha_ingreso + vida_util_meses
```

La alerta de próximos a depreciarse usa un umbral de 90 días. Esta es una estimación operativa para planificación de renovación; no reemplaza una política contable formal.

Para una depreciación contable completa se recomienda documentar y aplicar:

```text
depreciacion_anual = (valor_adquisicion - valor_residual) / vida_util_en_anios
depreciacion_acumulada = depreciacion_anual * anios_transcurridos
valor_en_libros = valor_adquisicion - depreciacion_acumulada
```

## 8. Reportes

Desde **Reportes** se pueden descargar:

1. Inventario filtrable: hardware, software, categorías, estado, responsable, depreciación y firma.
2. Custodia de equipos: colaborador, departamento, ubicación, IP y fecha de asignación.
3. Resumen por categoría y asignados por categoría con totales, responsables y costo.
4. Activos disponibles y activos en reparación.
5. Donaciones, descartes, licencias, cupos, vencimientos, depreciación, solicitudes, devoluciones, revisiones técnicas e historial de estados.

Los archivos son compatibles con Microsoft Excel y se generan con extensión `.xls`.
La pantalla de reportes y las exportaciones filtrables comparten un servicio central con filtros básicos de tipo, estado, categoría, texto, fechas, activos sin asignar y licencias disponibles. Cada Excel incluye título, usuario, fecha, filtros y totales.

La gestión operativa de llaves RSA se documenta en `docs/GESTION_LLAVES_RSA.md`. Las llaves privadas nunca deben guardarse dentro del proyecto.

## 8.1. Copias de seguridad y restauración

Para generar un respaldo sin exponer credenciales:

```text
mysqldump -u <usuario> -p cmdb_integral > /ruta/segura/fuera-del-repo/cmdb_integral_YYYYMMDD.sql
```

Para restaurar en un entorno controlado:

```text
mysql -u <usuario> -p cmdb_integral < /ruta/segura/fuera-del-repo/cmdb_integral_YYYYMMDD.sql
```

No se incluye ninguna contraseña en versión de texto. Los backups reales deben mantenerse fuera del repositorio; `database/backups/` solo contiene documentación.

## 8.2. Migraciones

El archivo `database/migrations/2026_07_13_0001_seguridad_funcionalidades_pendientes.sql` contiene cambios incrementales para bases ya instaladas.

Las fases posteriores agregan migraciones incrementales adicionales. Para licencias y portal, revisar:

```text
database/migrations/2026_07_13_0008_licencias_portal_cifrado.sql
docs/LICENCIAS_PORTAL.md
```

Para QR seguro por activo:

```text
database/migrations/2026_07_13_0009_qr_seguro_activo.sql
docs/QR_SEGURO.md
```

Para solicitudes e historial formal:

```text
database/migrations/2026_07_13_0010_solicitudes_historial_formal.sql
docs/SOLICITUDES_HISTORIAL.md
```

Para presupuesto anual y quinquenal:

```text
database/migrations/2026_07_13_0011_presupuesto_anual_quinquenal.sql
docs/PRESUPUESTO_ANUAL_QUINQUENAL.md
```

Para reportes y exportaciones:

```text
docs/REPORTES_EXPORTACIONES.md
```

Para imágenes e historial de ubicaciones:

```text
database/migrations/2026_07_13_0012_imagenes_ubicaciones.sql
docs/IMAGENES_UBICACIONES.md
```

Para cargar contenido demo ampliado y conectado entre módulos:

```text
database/migrations/2026_07_14_0001_contenido_demo_completo.sql
```

Si al solicitar una devolución aparece “No fue posible guardar los datos” o el portal indica que la migración de ciclo de vida está pendiente, aplicar:

```text
database/migrations/2026_07_13_0007_ciclo_vida_activo_formal.sql
```

Si la base ya existía, aplicar migración tras respaldar. No usar scripts de instalación limpia para actualizar bases existentes.

## 9. Enlaces de entrega

Repositorio local inicializado en la rama `main` para la entrega `CMDB_Semestral`. Aun no se configura un remoto GitHub verificable; no se inventan enlaces.

- Repositorio GitHub: pendiente externo hasta tener URL real.
- Video demostrativo: pendiente externo hasta grabar y publicar URL real.
- Backup de base de datos: pendiente externo; los respaldos reales deben guardarse fuera del repositorio.

La guía de cierre antes de entregar está en `docs/ENTREGA_FINAL.md`.
La lista paso a paso para validar WampServer, Ubuntu/Nginx y los modulos principales está en `docs/PRUEBAS_MANUALES_ENTREGA.md`.
La validación final con base limpia en XAMPP/WampServer está en `docs/VALIDACION_FINAL_XAMPP_WAMPSERVER.md`.
El orden de corrección aplicado y su evidencia están en `docs/ORDEN_CORRECCION_VERIFICADO.md`.

## 10. Evidencias recomendadas para el video

1. Ejecutar el script en phpMyAdmin.
2. Mostrar login exitoso con administrador.
3. Mostrar tres intentos fallidos y bloqueo.
4. Registrar o editar un colaborador.
5. Registrar activo hardware con dos imágenes y ver la firma de integridad.
6. Asignar un equipo y mostrarlo en el portal de Sofía.
7. Crear una necesidad desde el portal.
8. Descargar ambos reportes de Excel.
9. Mostrar bitácora y pantalla de noticias.

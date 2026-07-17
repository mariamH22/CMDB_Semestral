# Auditoria Tecnica CMDB Integral

Fecha de auditoria: 2026-07-13

## Alcance

Esta auditoria revisa el proyecto ubicado en:

```text
/home/mrmop/Downloads/Semestral/CMDB_Semestral
```

Reglas respetadas:

- No se cambio configuracion de WampServer.
- No se modifico `public/.htaccess`.
- No se agrego configuracion de Nginx al repositorio.
- No se cambiaron credenciales compartidas.
- No se agregaron rutas absolutas especificas de Ubuntu dentro del codigo.
- La copia en `/var/www/html/CMDB_Semestral` se usa solo para pruebas locales.

## Entendimiento del Proyecto

| Area | Resultado |
|---|---|
| Lenguaje | PHP 8.x |
| Arquitectura | MVC propio con controladores, modelos, vistas y core compartido |
| Rutas | Front controller en `public/index.php` con `App\Core\Router` |
| Base de datos | MySQL/MariaDB con PDO y consultas preparadas |
| Autenticacion | Sesiones PHP, hash de contrasenas, bloqueo por intentos fallidos |
| Roles | `ADMIN`, `OPERADOR`, `COLABORADOR` mediante `App\Core\Authorization` |
| Frontend | HTML, CSS y JavaScript nativo |
| Compatibilidad | WampServer/Apache por `.htaccess`; Ubuntu/Nginx solo por configuracion local externa |
| Dependencias | No hay Composer/npm obligatorios versionados |
| Pruebas | Tests PHP en `tests/*.php`; validacion JS con `node --check` |

## Evidencia de Verificacion

| Verificacion | Resultado |
|---|---|
| `php -l` sobre `app`, `public`, `tests`, `database` | OK |
| `php tests/*.php` | OK, 11/11 |
| `node --check public/assets/js/app.js` | OK |
| Cruce de rutas `url()` contra `public/index.php` | Sin rutas estaticas muertas |
| Busqueda de archivos Nginx en repo | Sin resultados |
| `git status --short` | BLOQUEADO: esta carpeta no es reconocida como repo Git |

## Tabla de Auditoria Funcional

| Modulo | Funcion | Estado | Problema | Causa tecnica | Archivos relacionados | Prioridad | Correccion |
|---|---|---|---|---|---|---|---|
| Autenticacion | Login valido | FUNCIONAL | Carga y redirecciona correctamente con usuario demo | Ruta, CSRF, modelo y sesion conectados | `AuthController.php`, `User.php`, `auth/login.php` | Alta | Sin cambio |
| Autenticacion | Login invalido | DEFECTUOSA | Usuario inexistente mostraba mensaje diferente | Mensaje especifico permitia enumeracion basica | `app/Controllers/AuthController.php` | Alta | Corregido con mensaje generico |
| Autenticacion | Logout | FUNCIONAL | Boton conectado con CSRF y ruta real | Formulario POST hacia `logout` | `layout/header.php`, `AuthController.php` | Media | Sin cambio |
| Recuperacion | Solicitud de recuperacion | FUNCIONAL | Usa token y no revela si correo existe | Flujo controlado por `PasswordReset` | `AuthController.php`, `PasswordReset.php` | Media | Sin cambio |
| Usuarios | Listar | FUNCIONAL | Tabla carga desde base de datos | Modelo `User::all()` | `UsersController.php`, `users/index.php` | Media | Sin cambio |
| Usuarios | Crear/editar | FUNCIONAL | Formulario conectado y validado | Controlador y modelo conectados | `UsersController.php`, `User.php`, `users/form.php` | Alta | Ya tenia confirmacion y preservacion de campos no sensibles |
| Usuarios | Eliminar | INCOMPLETA | No hay eliminacion fisica | Diseno usa baja logica por `activo` | `UsersController.php`, `users/index.php` | Baja | Mantener baja logica; documentar como no aplicable a borrado fisico |
| Categorias | Crear/editar | INCOMPLETA | Al fallar validacion podia perder campos enviados | Vista no usaba `_old_input` | `categories/form.php` | Media | Corregido con `old_value()` y `old_checked()` |
| Categorias | Baja | FUNCIONAL | Baja logica con confirmacion | POST `categories/deactivate` con CSRF | `CategoriesController.php`, `categories/index.php` | Media | Sin cambio |
| Colaboradores | Crear/editar | INCOMPLETA | Al fallar validacion podia perder campos enviados | Vista no usaba `_old_input` | `collaborators/form.php` | Media | Corregido con `old_value()` y `old_checked()` |
| Colaboradores | Historial ubicacion | FUNCIONAL | Depende de migracion aplicada | Modelo verifica tabla antes de usarla | `Collaborator.php`, `collaborators/form.php` | Media | Sin cambio |
| Inventario | Listar/buscar/filtrar | FUNCIONAL | Filtros conectados a modelo | `InventoryItem::all()` recibe filtros | `InventoryController.php`, `inventory/index.php` | Alta | Sin cambio |
| Inventario | Crear/editar | FUNCIONAL | Formulario conserva datos y valida backend | Controlador, modelo, imagenes y firma conectados | `InventoryController.php`, `InventoryItem.php`, `inventory/form.php` | Alta | Sin cambio en esta pasada |
| Inventario | Detalle | FUNCIONAL | Ruta y botones conectados | `inventory/detail` valida item antes de renderizar | `InventoryController.php`, `inventory/detail.php` | Alta | Sin cambio |
| Inventario | Eliminar | INCOMPLETA | No hay eliminacion fisica | Ciclo de vida usa estados y baja logica | `InventoryController.php`, `InventoryStatus.php` | Baja | Mantener flujo formal de estados |
| Inventario | QR | FUNCIONAL | Generar, regenerar, revocar, descargar y vista publica tienen rutas | Modelo `InventoryQr` y acciones POST/GET conectadas | `InventoryController.php`, `InventoryQr.php`, `inventory/detail.php` | Alta | Sin cambio |
| Inventario | Licencias | FUNCIONAL | Asignar/liberar cupos, revelar clave con permiso | Modelo `LicenseAssignment`, protector de clave y CSRF | `InventoryController.php`, `LicenseAssignment.php` | Alta | Sin cambio |
| Asignaciones | Crear | INCOMPLETA | Al fallar validacion podia perder seleccion y campos | Vista no usaba `_old_input` | `assignments/form.php` | Media | Corregido con `old_value()` y `selected()` |
| Asignaciones | Devolucion/revision | FUNCIONAL | Botones POST con confirmacion y CSRF | Controlador y modelos de devolucion conectados | `AssignmentsController.php`, `ReturnReview.php` | Alta | Sin cambio |
| Necesidades | Listar/procesar | FUNCIONAL | Estados, historial y respuesta conectados | `NeedRequest` soporta esquema incremental | `NeedsController.php`, `NeedRequest.php`, `needs/index.php` | Alta | Sin cambio |
| Portal | Solicitudes | FUNCIONAL | Formulario crea necesidades reales | POST `portal/needs/store` | `PortalController.php`, `NeedsController.php`, `portal/index.php` | Alta | Sin cambio |
| Portal | Devolucion | FUNCIONAL | Formulario conectado con confirmacion | POST `portal/returns/store` | `PortalController.php`, `Assignment.php` | Alta | Sin cambio |
| Presupuesto | Generar | INCOMPLETA | Al fallar validacion podia perder filtros y supuestos | Vista no usaba `_old_input` | `budgets/index.php` | Media | Corregido con `old_value()` y `selected()` |
| Presupuesto | Exportar Excel | FUNCIONAL | Boton apunta a ruta real | `BudgetsController::excel()` | `budgets/index.php`, `BudgetsController.php` | Media | Sin cambio |
| Reportes | Filtros/exportaciones | FUNCIONAL | Botones Excel conectados a rutas reales | `ReportsController` y `ReportService` | `reports/index.php`, `ReportsController.php` | Alta | Sin cambio |
| Noticias | Crear/editar | INCOMPLETA | Al fallar validacion podia perder titulo/resumen/contenido | Vista no usaba `_old_input` | `news/form.php` | Media | Corregido con `old_value()` y `old_checked()` |
| Noticias | Listar publico/admin | FUNCIONAL | Usa datos reales desde tabla `noticias` | Modelo `News` conectado | `NewsController.php`, `News.php` | Media | Sin cambio |
| Auditoria | Bitacora | FUNCIONAL | Lista eventos desde base de datos | Modelo `AuditLog` | `AuditController.php`, `AuditLog.php` | Alta | Sin cambio |
| Auditoria | Llaves RSA | FUNCIONAL CON BLOQUEO EXTERNO | Requiere configurar almacen y llaves fuera del repo | Seguridad falla seguro si no hay configuracion | `RsaKey.php`, `FileKeyStore.php`, `audit/keys.php` | Alta | Pendiente de configurar secretos locales fuera del repo |
| Dashboard | Metricas | FUNCIONAL | Datos reales desde modelos | `dashboardCounts`, asignaciones, necesidades | `DashboardController.php`, `InventoryItem.php` | Alta | Mejorado visualmente |
| Dashboard | Accesos rapidos | INCOMPLETA | Requeria mejor organizacion y respeto de permisos | UI previa era simple | `dashboard/index.php`, `app.css` | Media | Corregido con accesos por permiso |
| Frontend | Confirmaciones/carga | FUNCIONAL | JS maneja confirmaciones, cierre de alertas, submit loading, responsive tables | `public/assets/js/app.js` | Media | Sin cambio |
| Seguridad | CSRF | FUNCIONAL | Formularios POST incluyen token | `csrf_field()`, `Controller::csrf()` | Alta | Sin cambio |
| Seguridad | XSS | FUNCIONAL | Vistas usan `e()`/helpers | `helpers.php`, vistas | Alta | Sin cambio |
| Seguridad | Secretos | FUNCIONAL CON BLOQUEO EXTERNO | HMAC/RSA/licencias dependen de config local externa | No hay secretos reales en repo | `config.php`, `config.local.example.php`, `.gitignore` | Alta | Configurar fuera del repo |
| Base de datos | Migraciones | FUNCIONAL CON ADVERTENCIA | Varias migraciones usan `ADD COLUMN IF NOT EXISTS` | Compatible con MySQL moderno; revisar version en Wamp si falla | `database/migrations/*.sql` | Media | Documentar y aplicar con respaldo |
| Git | Estado del repositorio | BLOQUEADO | `git status` falla | Carpeta actual no tiene repo Git valido | `.git` local | Alta | Ubicar/clonar repo Git real antes de subir |

## Problemas Corregidos en Esta Pasada

1. Login con mensaje distinto para usuario inexistente.
2. Formularios de categorias, colaboradores, noticias, asignaciones y presupuesto no preservaban campos tras errores.
3. Dashboard tenia jerarquia visual limitada y pocos accesos operativos.
4. Accesos rapidos del dashboard ahora respetan permisos del rol.

## Pendientes No Cerrados

- Prueba de mutacion completa de todos los CRUD sobre una base de datos de pruebas controlada.
- Configuracion local de HMAC/RSA/licencias fuera del repositorio.
- Resolver el repositorio Git real antes de subir cambios.
- Verificar permisos de `storage` y `public/uploads` en la copia Nginx local si se suben archivos.


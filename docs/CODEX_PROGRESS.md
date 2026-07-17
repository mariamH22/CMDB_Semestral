# Seguimiento de implementación

## Fase actual
Auditoría final y cierre de instrucciones.

## Estado
LISTO CON OBSERVACIONES.

## Cierre de repositorio
- Repositorio local inicializado en `main` para `CMDB_Semestral`.
- `git status --short` ya funciona en esta carpeta.
- Remoto GitHub pendiente hasta crear o confirmar la URL real.
- La verificacion final de esta sesion mantiene como bloqueo externo la conexion a MySQL real: `SQLSTATE[HY000] [2002] Operation not permitted`.

## Resumen de auditoría final
- Todas las fases técnicas del roadmap están marcadas como completadas.
- Matriz actual: `CUMPLE` 282, `NO VERIFICABLE` 5.
- No quedan requisitos con estado final `NO CUMPLE`.
- No quedan requisitos con estado final `CUMPLE PARCIALMENTE`.
- Los `NO VERIFICABLE` dependen de evidencias externas reales: video, enlaces del video, URL real de GitHub y URL real del backup.
- Se reforzó la documentación del flujo GitHub + pruebas Ubuntu/Nginx sin cambiar código ejecutable.
- Se agregó `docs/PRUEBAS_MANUALES_ENTREGA.md` para validar manualmente WampServer, Ubuntu/Nginx, CRUD principal, inventario, licencias, reportes, QR, auditoría y evidencias externas.
- No se modificó configuración de Ubuntu/Nginx, WampServer, Apache, PHP-FPM, MySQL ni `public/.htaccess`.
- No se ejecutó SQL sobre la base real.

## Resumen post-auditoría
- Se corrigieron requisitos finales que seguían como `NO CUMPLE` o parciales aunque ya tenían implementación real.
- Matriz actual: `CUMPLE` 282, `NO VERIFICABLE` 5.
- No quedan requisitos con estado final `NO CUMPLE`.
- No quedan requisitos con estado final `CUMPLE PARCIALMENTE`.
- Los `NO VERIFICABLE` dependen de evidencias externas: video real, enlaces reales del video, URL real de GitHub y URL real del backup.
- No se modificó configuración de Ubuntu/Nginx, credenciales locales ni `public/.htaccess`.
- No se ejecutó SQL sobre la base real.

## Correcciones post-auditoría completadas
- Endurecido `FileKeyStore`: el almacén de llaves RSA privadas ahora debe estar fuera del proyecto/repositorio; si apunta al repo falla de forma segura.
- Login endurecido contra enumeración: la pantalla usa mensaje genérico para usuario inactivo, bloqueado o credencial inválida; la bitácora conserva el detalle interno.
- Validación backend ampliada para fechas y URLs.
- Depreciación centralizada en `DepreciationCalculator`.
- Asignaciones ampliadas para guardar `usuario_asignador_id`, `audit_id` y `firma_id` cuando la migración está aplicada.
- Agregada migración no destructiva `2026_07_13_0013_asignador_validaciones.sql`.
- Inventario e imágenes extra se guardan en transacción para evitar cambios compuestos inconsistentes.
- `Database` traduce `PDOException` a `DatabaseException` con mensaje genérico.
- Inyección de dependencias ligera agregada con `ModelFactory` y fábrica reemplazable en `Router`.
- Documentación SOLID agregada para SRP, OCP, LSP y DIP.
- Claves de licencia demo retiradas de datos semilla; las claves operativas quedan fuera del repositorio.
- Inventario de dependencias agregado; no hay Composer/npm/vendor/node_modules versionados y las dependencias de runtime quedan fuera del repo.
- Guia final de entrega agregada para cerrar observaciones externas sin cambiar codigo ni configuracion local.
- Configuracion local ampliada: `config.local.php` puede sobrescribir credenciales de base de datos sin cambiar defaults de WampServer.
- Permiso de inventario ajustado: `OPERADOR` puede usar CRUD de inventario; `COLABORADOR` sigue limitado al portal.
- Home reforzado con definición de CMDB, alcance, gobierno, seguridad, OWASP, trazabilidad e integridad.
- README actualizado con validación de fechas/URLs y almacén RSA fuera del proyecto.
- `docs/MATRIZ_CUMPLIMIENTO.md` actualizado con evidencias reales.

## Verificación post-auditoría
- `php -l` en `app`, `public` y `tests`: sin errores.
- `node --check public/assets/js/app.js`: sin errores.
- `php tests/*.php`: todos OK.
- `rg -n "DROP |TRUNCATE|DELETE FROM" database/migrations`: sin resultados.
- No se agregaron archivos Nginx al repositorio.

## Verificación final ejecutada
- `find app public tests database -name '*.php' -print0 | xargs -0 -n1 php -l`: sin errores.
- `node --check public/assets/js/app.js`: sin errores.
- `for test in tests/*.php; do php "$test" || exit 1; done`: todos OK.
- Revisión automática de rutas: 90 rutas apuntan a métodos existentes.
- Revisión de formularios POST: vistas con `csrf_field()` y controladores con validación CSRF.
- `find` de archivos Nginx dentro del repo: sin resultados.
- `public/.htaccess`: presente y sin cambios.
- Búsqueda de rutas absolutas de servidor en código ejecutable: sin dependencia de `/var/www`, `/etc/nginx` ni puertos locales.
- `rg -n "DROP |TRUNCATE|DELETE FROM" database/migrations database/install database/cmdb_integral.sql`: sin resultados.
- `git status --short` y `git diff --stat`: bloqueados porque esta carpeta no es reconocida como repositorio Git.

## Pendientes restantes
- No quedan parciales técnicos dentro del repositorio.
- 5 no verificables externos: video, enlaces del video, URL real de repo y URL real de backup.
- Pruebas manuales recomendadas antes de entregar: login, CRUD principal, inventario, reportes, subida opcional de imágenes y prueba en WampServer real.
- Ejecutar y marcar `docs/PRUEBAS_MANUALES_ENTREGA.md` en los entornos reales antes de declarar entrega final sin observaciones.

---

## Fase anterior
Documentación, UML y matriz.

## Tareas completadas
- README actualizado sin enlaces falsos de repositorio/video/backup.
- El repositorio Git remoto no se pudo detectar porque `git remote -v` falla con `fatal: not a git repository`.
- Video, repositorio GitHub y backup real quedaron marcados como pendientes externos cuando no existe URL verificable.
- Documentación enlazada por funciones reales: seguridad, RSA, HMAC, llaves, auditoría, estados, devoluciones, licencias, QR, presupuestos, reportes, imágenes y ubicaciones.
- Creado índice `docs/DIAGRAMAS.md`.
- Agregados diagramas PlantUML de componentes, MVC, asignación/devolución, revisión/descarte/donación, RSA/rotación/revocación, estados, solicitudes/presupuesto y QR/auditoría.
- Actualizados diagramas DOT principales de casos de uso, clases y entidad-relación.
- No se regeneraron PNG/SVG porque Graphviz `dot` no está instalado en este entorno.
- Actualizada `docs/MATRIZ_CUMPLIMIENTO.md`; ya no quedan filas con estado final `Pendiente`.
- Actualizada matriz corta `docs/Matriz_Cumplimiento_CMDB.md`.
- Eliminados marcadores falsos tipo `REEMPLAZAR_*` del README.

## Tareas pendientes
- Ninguna dentro de Fase 7B.
- Pendiente externo: publicar URL real de GitHub si aplica.
- Pendiente externo: grabar/publicar video demostrativo si la entrega lo exige.
- Pendiente externo: generar y guardar backup real fuera del repositorio.
- Pendiente externo: aplicar manualmente migraciones nuevas con respaldo previo.
- Pendiente externo: sincronizar manualmente la copia de Nginx en `/var/www/html/CMDB_Semestral` si se quiere probar en `localhost`.

## Archivos modificados
- `README.md`
- `docs/CODEX_PROGRESS.md`
- `docs/DIAGRAMAS.md`
- `docs/Guion_Video.md`
- `docs/MATRIZ_CUMPLIMIENTO.md`
- `docs/Matriz_Cumplimiento_CMDB.md`
- `docs/diagrams/casos_uso.dot`
- `docs/diagrams/clases.dot`
- `docs/diagrams/der.dot`
- `docs/uml/componentes.puml`
- `docs/uml/estados_activo.puml`
- `docs/uml/mvc.puml`
- `docs/uml/qr_auditoria.puml`
- `docs/uml/rsa_rotacion_revocacion.puml`
- `docs/uml/secuencia_asignacion_devolucion.puml`
- `docs/uml/secuencia_revision_descarte_donacion.puml`
- `docs/uml/solicitudes_presupuesto.puml`

## Migraciones creadas
- Ninguna dentro de Fase 7B.

No se ejecutó SQL sobre la base real.

## Pruebas y verificaciones ejecutadas
- `git remote -v`: falla con `fatal: not a git repository`.
- `rg -n "REEMPLAZAR|placeholder|Pendiente" README.md docs/MATRIZ_CUMPLIMIENTO.md docs/Guion_Video.md docs/DIAGRAMAS.md`: sin resultados.
- `rg -n "TODO|FIXME|XXX" app public tests docs --glob '!docs/ROADMAP_PENDIENTES.md'`: sin resultados.
- `command -v dot`: Graphviz no disponible.
- `php -l app/Core/Validator.php`: sin errores de sintaxis.
- `php -l app/Controllers/CollaboratorsController.php`: sin errores de sintaxis.
- `php -l app/Models/Collaborator.php`: sin errores de sintaxis.
- `php -l app/Core/InventoryImagePolicy.php`: sin errores de sintaxis.
- `php -l app/Core/ReportService.php`: sin errores de sintaxis.
- `php -l app/Controllers/ReportsController.php`: sin errores de sintaxis.
- `php tests/Phase7AImagesLocationTest.php`: `OK Phase7AImagesLocationTest`.
- `php tests/Phase6CReportsExportTest.php`: `OK Phase6CReportsExportTest`.
- `php tests/Phase6BBudgetProjectionTest.php`: `OK Phase6BBudgetProjectionTest`.
- `php tests/Phase6ANeedWorkflowTest.php`: `OK Phase6ANeedWorkflowTest`.
- `php tests/Phase5BQrSecurityTest.php`: `OK Phase5BQrSecurityTest`.

## Bloqueos externos
- Git no está disponible en esta carpeta: `git status --short` y `git remote -v` fallan con `fatal: not a git repository`.
- No se modificó configuración de Ubuntu/Nginx ni credenciales.
- La sincronización a `/var/www/html/CMDB_Semestral` requiere `sudo` interactivo fuera de Codex.

---

## Corrección posterior: contraseñas de usuarios

## Tareas completadas
- Ampliada la política de contraseñas: el máximo permitido sube a 64 caracteres.
- Agregada confirmación de contraseña en creación y edición de usuarios.
- Actualizadas pantallas de restablecimiento y cambio de contraseña con los límites reales.
- Documentado que el registro público permanece deshabilitado y que las altas se hacen desde el módulo Usuarios.

## Archivos modificados
- `app/Config/config.php`
- `app/Core/Validator.php`
- `app/Controllers/UsersController.php`
- `app/Views/users/form.php`
- `app/Views/auth/reset.php`
- `app/Views/auth/register.php`
- `app/Views/portal/password.php`
- `tests/Phase1SecurityTest.php`
- `README.md`
- `docs/CODEX_PROGRESS.md`

No se modificó configuración de Ubuntu/Nginx, credenciales ni base de datos.

---

## Corrección posterior: claves de licencia sin cifrado local

## Tareas completadas
- El formulario de inventario detecta si el cifrado local de claves de licencia está configurado.
- Si falta la clave maestra, desactiva solo el campo de clave/serial y permite guardar los demás datos de licencia.
- El backend mantiene la regla segura: no guarda claves de licencia en texto plano.
- Documentado el flujo correcto para guardar licencias sin clave o configurar una clave maestra local fuera del repositorio.

## Archivos modificados
- `app/Controllers/InventoryController.php`
- `app/Views/inventory/form.php`
- `README.md`
- `docs/LICENCIAS_PORTAL.md`
- `docs/CODEX_PROGRESS.md`

No se modificó configuración de Ubuntu/Nginx, credenciales ni base de datos.

---

## Corrección posterior: formulario de inventario

## Tareas completadas
- Las imágenes principal y adicional del inventario quedan opcionales.
- Si una validación falla, el sistema conserva temporalmente los campos enviados para no vaciar el formulario.
- No se conservan contraseñas, tokens, claves de licencia ni otros campos sensibles en la memoria temporal del formulario.
- Actualizado el texto del formulario y la documentación de imágenes.

## Archivos modificados
- `app/Core/Controller.php`
- `app/Core/View.php`
- `app/Core/helpers.php`
- `app/Core/InventoryImagePolicy.php`
- `app/Views/inventory/form.php`
- `tests/Phase7AImagesLocationTest.php`
- `README.md`
- `docs/MATRIZ_CUMPLIMIENTO.md`
- `docs/CODEX_PROGRESS.md`

No se modificó configuración de Ubuntu/Nginx, credenciales ni base de datos.

## Próxima fase permitida
Todas las fases del roadmap y la auditoría final quedaron completadas. No hay siguiente fase técnica dentro del código; quedan observaciones externas y pruebas manuales de entrega.

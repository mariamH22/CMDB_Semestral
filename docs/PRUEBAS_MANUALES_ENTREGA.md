# Checklist de pruebas manuales de entrega

Este documento sirve para validar el sistema antes de entregar o subir cambios. No requiere modificar codigo, credenciales, WampServer, Nginx ni archivos del servidor.

## Reglas antes de probar

1. Probar siempre desde una base respaldada o una base local de pruebas.
2. No ejecutar scripts de instalacion limpia sobre una base con datos reales.
3. No cambiar `app/Config/config.php` para ajustar credenciales locales.
4. Usar `app/Config/config.local.php` solo en el entorno local y no subirlo a Git.
5. No editar `public/.htaccess`; WampServer lo necesita.
6. No agregar archivos de Nginx al repositorio.
7. Registrar evidencia con capturas o notas: usuario usado, modulo, accion, resultado y fecha.

## Preparacion comun

Ejecutar validaciones desde la carpeta del proyecto:

```bash
find app public tests database -name '*.php' -print0 | xargs -0 -n1 php -l
node --check public/assets/js/app.js
for test in tests/*.php; do php "$test" || exit 1; done
```

Confirmar que no se agregaron archivos locales:

```bash
git status --short
```

Si Git responde `fatal: not a git repository`, esa carpeta no es el repositorio Git correcto. Ubicar el repositorio real antes de subir cambios.

## Prueba en WampServer

URL esperada:

```text
http://localhost/CMDB_Semestral/public/
```

Validar:

- `php database/tools/verify_environment.php` devuelve OK antes de abrir el navegador.
- La pagina principal carga sin error 404 o 500.
- `public/.htaccess` sigue presente.
- Login carga desde `http://localhost/CMDB_Semestral/public/login`.
- Los enlaces internos no redirigen a rutas de Ubuntu ni Nginx.
- Las imagenes y CSS cargan correctamente.

## Prueba en Ubuntu/Nginx

La copia local de Nginx debe ser solo para pruebas. No editar alli como fuente principal.

URL comun de prueba:

```text
http://localhost/CMDB_Semestral/
```

Validar:

- La pagina principal carga sin error 404 o 500.
- Login carga correctamente.
- La base de datos conecta usando configuracion local externa.
- No fue necesario modificar codigo del repositorio para que Nginx funcione.

## Datos minimos esperados

En la base de pruebas deben existir:

- Al menos un usuario `ADMIN`.
- Al menos un usuario `OPERADOR`.
- Al menos un usuario `COLABORADOR`.
- Categorias activas.
- Colaboradores activos.
- Activos de inventario.
- Al menos una licencia si se probara el modulo de licencias.

Consultas utiles:

```sql
SELECT COUNT(*) AS usuarios FROM usuarios;
SELECT COUNT(*) AS categorias FROM categorias;
SELECT COUNT(*) AS colaboradores FROM colaboradores;
SELECT COUNT(*) AS inventario FROM inventario;
```

## Flujo 1: login y seguridad

1. Abrir login.
2. Iniciar sesion con `admin@cmdb.local`.
3. Confirmar que abre el dashboard.
4. Cerrar sesion.
5. Intentar login con usuario o contrasena incorrectos.
6. Confirmar que el mensaje es generico y no revela si el usuario existe.
7. Hacer tres intentos fallidos sobre una cuenta de prueba.
8. Confirmar bloqueo de cuenta.
9. Entrar como `ADMIN` y desbloquear la cuenta desde Usuarios.

Resultado esperado: login correcto, logout correcto, bloqueo y desbloqueo funcionales, sin mensajes que enumeren usuarios.

## Flujo 2: usuarios

1. Entrar como `ADMIN`.
2. Abrir Usuarios.
3. Crear usuario de prueba con rol permitido.
4. Intentar crear usuario con datos incompletos.
5. Confirmar que el formulario muestra error y conserva campos no sensibles.
6. Editar el usuario.
7. Desactivar el usuario.
8. Confirmar que el usuario desactivado no puede iniciar sesion.

Resultado esperado: alta, edicion, baja logica y validaciones funcionales.

## Flujo 3: categorias

1. Abrir Categorias como `ADMIN`.
2. Crear categoria de prueba.
3. Editar nombre, tipo o descripcion.
4. Probar validacion dejando un campo requerido vacio.
5. Desactivar la categoria.

Resultado esperado: CRUD operativo con baja logica y preservacion de datos tras errores.

## Flujo 4: colaboradores

1. Abrir Colaboradores.
2. Registrar un colaborador sin foto.
3. Registrar o editar un colaborador con foto valida.
4. Probar una imagen no valida.
5. Editar ubicacion y registrar motivo de cambio.

Resultado esperado: la foto es opcional, la imagen invalida se rechaza y el historial de ubicacion se conserva si la migracion fue aplicada.

## Flujo 5: inventario

1. Abrir Inventario como `ADMIN` u `OPERADOR`.
2. Intentar crear activo fisico sin imagen y confirmar rechazo.
3. Crear activo fisico con imagen principal y adicional validas.
4. Probar imagen invalida.
5. Editar datos generales del activo.
6. Abrir Detalle.
7. Confirmar estado, firma de integridad, categoria y datos principales.
8. Filtrar por texto, tipo, estado y categoria.

Resultado esperado: inventario lista datos reales, permite crear/editar, rechaza hardware con menos de dos imagenes y permite software sin imagen.

## Flujo 6: licencias

1. Crear o abrir una licencia.
2. Si no hay clave maestra local, dejar vacio el campo de clave o serial sensible.
3. Guardar proveedor, vencimiento, URL, observaciones y cupos.
4. Asignar un cupo a colaborador.
5. Liberar el cupo.
6. Intentar revelar clave con un rol sin permiso.

Resultado esperado: no se guarda texto plano sensible sin clave maestra; los cupos se controlan y la revelacion queda restringida a `ADMIN`.

## Flujo 7: asignaciones, devolucion y revision

1. Crear asignacion de un activo disponible a un colaborador.
2. Confirmar que el activo cambia a asignado.
3. Entrar al portal del colaborador.
4. Solicitar devolucion con motivo y evidencia.
5. Entrar como `ADMIN`.
6. Revisar la devolucion.
7. Registrar estado fisico, diagnostico, recomendacion y decision.
8. Confirmar que el activo vuelve a disponible o pasa a descarte/donacion segun decision.

Resultado esperado: no hay saltos manuales de estado; el flujo formal deja trazabilidad.

## Flujo 8: solicitudes del colaborador

1. Entrar como `COLABORADOR`.
2. Abrir Mi portal.
3. Crear solicitud de equipo, software o licencia.
4. Entrar como `ADMIN` u `OPERADOR`.
5. Abrir Necesidades.
6. Cambiar estado y registrar respuesta administrativa.

Resultado esperado: la solicitud aparece con historial y estado actualizado.

## Flujo 9: presupuesto

1. Abrir Presupuesto.
2. Generar presupuesto anual.
3. Generar presupuesto quinquenal.
4. Cambiar inflacion, crecimiento, anio y filtros.
5. Exportar Excel.

Resultado esperado: calculos visibles, totales coherentes y archivo Excel descargable.

## Flujo 10: reportes

1. Abrir Reportes.
2. Aplicar filtros por tipo, estado, categoria, texto y fechas.
3. Descargar reportes de inventario.
4. Descargar reportes de asignaciones.
5. Descargar reportes de licencias, vencimientos, depreciacion, solicitudes, devoluciones y revisiones.
6. Abrir los archivos en Excel o LibreOffice.

Resultado esperado: los archivos descargan y no contienen errores visibles de formato.

## Flujo 11: QR

1. Abrir detalle de un activo.
2. Generar QR.
3. Descargar SVG.
4. Abrir consulta publica del QR.
5. Regenerar QR.
6. Confirmar que el token anterior deja de ser valido si fue revocado o reemplazado.

Resultado esperado: QR muestra solo informacion publica limitada y las acciones quedan auditadas.

## Flujo 12: auditoria y RSA

1. Abrir Bitacora como `ADMIN`.
2. Confirmar eventos de login, inventario, asignaciones y solicitudes.
3. Abrir Verificar cadena.
4. Abrir Gestionar llaves RSA.
5. Si hay almacen de llaves configurado fuera del repo, generar o rotar llave de prueba.
6. Verificar una firma.

Resultado esperado: la bitacora carga y RSA funciona solo si los secretos locales externos fueron configurados.

## Resultado de cierre

Marcar el resultado final:

```text
WampServer probado: SI / NO
Base limpia XAMPP/WampServer probada: SI / NO
Ubuntu/Nginx probado: SI / NO
Base respaldada: SI / NO
verify_environment.php OK: SI / NO
Migraciones aplicadas en base de prueba: SI / NO
CRUD principal probado: SI / NO
Reportes Excel probados: SI / NO
Video grabado: SI / NO
URL real de GitHub agregada: SI / NO
Backup real guardado fuera del repo: SI / NO
```

El proyecto puede entregarse como `LISTO PARA ENTREGAR` solo si las pruebas tecnicas, la evidencia externa, el backup real y la URL real del repositorio fueron confirmadas.

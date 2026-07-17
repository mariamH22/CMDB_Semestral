1. Cabecera obligatoria para cada ejecución

Pega esta cabecera al inicio de cada prompt de fase:

Actúa como desarrollador PHP senior, arquitecto de software y especialista en
seguridad OWASP, MySQL, MVC, SOLID, DRY y compatibilidad WampServer.

Esta ejecución debe completar UNA SOLA FASE del proyecto. No debes intentar
resolver el proyecto completo ni comenzar la fase siguiente.

REPOSITORIO ORIGINAL:

/home/mrmop/Downloads/Semestral/CMDB_Semestral

Trabaja exclusivamente en ese repositorio.

La carpeta:

/var/www/html/CMDB_Semestral

es únicamente una copia para pruebas. No la edites directamente.

REGLAS OBLIGATORIAS:

1. No modificar credenciales de MySQL.
2. No modificar usuario, contraseña, host, puerto ni nombre de la base.
3. No modificar variables de entorno locales.
4. No modificar /etc/nginx, /etc/php, /etc/mysql ni /etc/apache2.
5. No modificar php.ini.
6. No eliminar public/.htaccess.
7. No introducir rutas absolutas dentro del código.
8. Mantener compatibilidad con Windows, WampServer, Apache, MySQL, Ubuntu y Nginx.
9. Utilizar url(), asset(), __DIR__, dirname() y rutas portables.
10. No editar directamente /var/www/html.
11. No ejecutar git init.
12. No ejecutar commit ni push.
13. No usar git reset --hard ni git clean.
14. No ejecutar migraciones sobre la base real.
15. No eliminar tablas, columnas ni datos.
16. No cambiar contraseñas existentes.
17. No almacenar secretos, llaves privadas o tokens en Git.
18. No deshacer cambios de fases anteriores.
19. No modificar la lógica que no pertenezca a esta fase.
20. No declarar una prueba como aprobada si no fue ejecutada.

FLUJO GITHUB Y PRUEBAS EN UBUNTU/NGINX:

1. El repositorio original es la fuente de verdad.
2. La carpeta `/var/www/html/CMDB_Semestral` es solo una copia local para probar en Nginx.
3. No editar directamente la copia de Nginx.
4. Si se necesita probar en Nginx, sincronizar desde el repositorio original hacia la copia local.
5. La sincronización no debe copiar `.git`, `config.local.php`, backups, logs, caches, llaves ni secretos.
6. Las credenciales locales de Ubuntu deben quedar en `config.local.php` de la copia local o en variables de entorno, nunca en el código compartido.
7. Antes de subir a GitHub, ejecutar `git status --short` y confirmar que no aparecen archivos de Nginx, `config.local.php`, `.env`, backups reales, llaves privadas ni tokens.
8. Si `git status --short` falla con `fatal: not a git repository`, no hacer commit ni push desde esa carpeta; primero ubicar el repositorio Git real.
9. Ninguna mejora funcional debe depender de `/var/www/html`, Nginx, PHP-FPM, puertos locales o rutas absolutas del equipo Ubuntu.
10. WampServer/Apache debe seguir usando `public/.htaccess` y los valores predeterminados compatibles con Windows.

PROTOCOLO DE CONTINUIDAD:

Antes de modificar:

cd /home/mrmop/Downloads/Semestral/CMDB_Semestral
pwd
git status --short
git diff --stat

Después:

1. Lee docs/CODEX_PROGRESS.md si existe.
2. Revisa los cambios no confirmados antes de editar.
3. No repitas tareas marcadas como COMPLETADAS.
4. Trabaja exclusivamente en la fase indicada.
5. Implementa código real; no te limites a redactar un plan.
6. No dejes métodos vacíos, botones simulados, TODO o rutas sin integrar.
7. No comiences la siguiente fase.
8. Si existe un bloqueo externo, completa todo lo demás de esta fase y
   documenta exactamente el bloqueo.

Crea o actualiza:

docs/CODEX_PROGRESS.md

Formato mínimo:

# Seguimiento de implementación

## Fase actual
Nombre de la fase.

## Estado
EN_PROGRESO, COMPLETADA o BLOQUEADA.

## Tareas completadas
Lista exacta.

## Tareas pendientes
Lista exacta.

## Archivos modificados
Rutas exactas.

## Migraciones creadas
Rutas exactas.

## Pruebas ejecutadas
Comandos y resultados reales.

## Bloqueos externos
Configuraciones, credenciales, servicios o datos que debe aportar el propietario.

## Próxima fase permitida
Nombre de la fase siguiente, pero no debes comenzarla en esta ejecución.

Al terminar esta fase:

1. Ejecuta php -l sobre todos los archivos PHP modificados.
2. Ejecuta las pruebas relacionadas con esta fase.
3. Ejecuta git status --short.
4. Ejecuta git diff --stat.
5. Actualiza docs/CODEX_PROGRESS.md.
6. Informa qué archivos fueron modificados.
7. Explica el impacto sobre WampServer.
8. Confirma que no cambiaste credenciales.
9. No hagas commit ni push.
2. Fase 1: riesgos críticos y protección de datos

Añade este bloque debajo de la cabecera:

FASE ACTUAL: PROTECCIÓN DE DATOS Y CONFIGURACIÓN SEGURA

Completa exclusivamente los siguientes puntos:

1. Revisar database/cmdb_integral.sql.

2. Evitar que un archivo presentado como backup o actualización ejecute:

DROP DATABASE IF EXISTS

3. No ejecutar el SQL.

4. Separar claramente:

- Instalación nueva y destructiva.
- Migraciones incrementales.
- Generación de backup.
- Restauración.
- Actualización de instalaciones existentes.

5. Renombrar o mover el instalador destructivo para que sea imposible
confundirlo con un backup.

6. Agregar advertencias claras en:

- README.
- database/README.md.
- Documentación de instalación.

7. No incluir logs reales, caché, llaves, backups ni archivos temporales
en la distribución.

8. Revisar y completar .gitignore para excluir:

- storage/logs/*
- storage/cache/*
- storage/security/*
- database/backups/*
- Llaves privadas.
- Tokens temporales.
- Archivos de recuperación.
- Backups reales.

9. Mantener archivos .gitkeep únicamente cuando sean necesarios.

10. No mostrar el contenido sensible de los logs en el informe.

11. Eliminar el secreto HMAC predeterminado del código.

12. La aplicación no debe utilizar automáticamente una clave fija cuando
la configuración externa no exista.

13. Cuando falte la clave HMAC:

- Fallar de forma segura.
- No firmar nuevos registros.
- No utilizar un secreto conocido.
- Registrar un error sin revelar secretos.
- Mostrar un mensaje administrativo comprensible.

14. Crear una plantilla de configuración sin valores reales.

15. No modificar variables de entorno locales.

16. Crear pruebas para:

- Configuración HMAC válida.
- Configuración HMAC ausente.
- Firma HMAC válida.
- Detección de alteración.
- Prohibición del fallback inseguro.

CRITERIOS DE TERMINACIÓN:

- No existe secreto HMAC real o predeterminado en el repositorio.
- El SQL destructivo no se presenta como backup o migración.
- README diferencia instalación, migración y backup.
- Los archivos de runtime están excluidos.
- Las pruebas de esta fase pasan.
- No se ejecutó ningún SQL sobre la base real.
- docs/CODEX_PROGRESS.md queda con esta fase en COMPLETADA.
3. Fase 2A: contratos criptográficos y almacenamiento de llaves
FASE ACTUAL: CONTRATOS CRIPTOGRÁFICOS Y ALMACÉN DE LLAVES

No integres todavía todas las acciones sensibles. En esta fase completa la
arquitectura criptográfica y la gestión básica de llaves.

Implementar contratos pequeños y coherentes equivalentes a:

- PasswordHasherInterface.
- IntegrityServiceInterface.
- DigitalSignatureInterface.
- SignatureVerifierInterface.
- KeyManagementInterface.
- KeyStoreInterface.
- CanonicalPayloadInterface.
- EncryptionServiceInterface.

Requisitos:

1. No crear una única interfaz gigante.

2. Los controladores no deben llamar directamente a:

- openssl_sign().
- openssl_verify().
- password_hash().
- Acceso directo a archivos privados.

3. Implementar composición mediante:

- Inyección por constructor.
- Fábrica central.
- Contenedor simple compatible con el MVC actual.

4. Implementar un almacén de llaves configurable.

5. Las llaves privadas:

- No deben almacenarse en public/.
- No deben rastrearse con Git.
- No deben almacenarse sin cifrar.
- No deben aparecer en logs.
- No deben enviarse a JavaScript.
- No deben utilizar credenciales de MySQL.
- No deben depender de una ruta absoluta escrita en el código.

6. Si no existe un almacén seguro configurado:

- Fallar de forma segura.
- No generar una llave desprotegida.
- Mostrar una configuración pendiente al administrador.

7. Implementar generación RSA por usuario:

- 2048 bits o superior.
- SHA-256 o superior.
- Llave pública.
- Llave privada cifrada.
- Fingerprint.
- ID de usuario.
- Estado.
- Fecha de creación.

8. Implementar estados:

- ACTIVA.
- REVOCADA.
- REEMPLAZADA.

9. Crear migraciones incrementales y no destructivas para las llaves.

10. No ejecutar las migraciones sobre la base real.

11. Implementar PasswordHasherInterface usando:

- Argon2id cuando esté disponible.
- Bcrypt como fallback.
- Rehash compatible con contraseñas existentes.

12. Crear pruebas con un directorio temporal para:

- Generación.
- Almacenamiento cifrado.
- Recuperación.
- Fingerprint.
- Usuario sin almacén configurado.
- Imposibilidad de escribir dentro de public/.
- Hash y verificación de contraseñas.

CRITERIOS DE TERMINACIÓN:

- Las interfaces existen y están integradas.
- Existe gestión de llaves por usuario.
- No existe una selección global de la última llave.
- Ninguna llave privada real está en Git.
- Las pruebas aisladas pasan.
- Las migraciones existen pero no fueron aplicadas a la base real.
- No comenzar todavía la integración de todas las acciones sensibles.
4. Fase 2B: firmas RSA, verificación, rotación y revocación
FASE ACTUAL: FIRMA RSA Y CICLO DE VIDA DE LLAVES

Utiliza los contratos y servicios implementados en la fase anterior.

Completar:

1. Serialización canónica recursiva.

El payload debe incluir:

- Versión.
- ID del usuario.
- Acción.
- Entidad.
- ID de entidad.
- Fecha y hora.
- Datos relevantes.
- Hash del contenido.
- ID de auditoría.
- Fingerprint.
- ID de correlación.

2. Implementar firma mediante openssl_sign() dentro del servicio concreto.

3. Implementar verificación mediante openssl_verify().

4. Guardar:

- Firma.
- Hash.
- Algoritmo.
- ID de llave.
- Fingerprint.
- Fecha.
- Versión del payload.
- ID de auditoría.
- Resultado inicial.

5. Implementar resultados:

- VALIDA.
- INVALIDA.
- LLAVE_REVOCADA.
- NO_VERIFICABLE.
- ERROR.

6. Implementar rotación:

- Generar nueva llave.
- Marcar la anterior como REEMPLAZADA.
- Conservar la llave pública anterior.
- Firmar acciones futuras con la nueva.

7. Implementar revocación:

- Motivo.
- Fecha.
- Usuario responsable.
- Bloqueo de firmas nuevas.
- Conservación de verificación histórica.

8. Crear pantallas administrativas utilizando el diseño existente para:

- Generar llave.
- Consultar fingerprint.
- Rotar.
- Revocar.
- Ver historial.
- Verificar una firma.

9. Proteger mediante:

- Autenticación.
- Rol.
- CSRF.
- Reautenticación o frase de firma cuando corresponda.
- Auditoría.

10. No almacenar la frase de firma.

11. Crear pruebas para:

- Firma válida.
- Payload alterado.
- Llave incorrecta.
- Llave revocada.
- Rotación.
- Firma histórica.
- Usuario sin llave.
- Serialización con distinto orden de propiedades.
- Imposibilidad de firmar con llave revocada.

12. Integrar firmas en estas acciones solamente:

- Cambio de rol.
- Activación o inactivación.
- Asignación.
- Recepción de devolución.
- Resultado de revisión técnica.
- Descarte.
- Donación.
- Aprobación o rechazo de solicitud.
- Cambios sensibles de costo.

No declarar no repudio jurídico absoluto.

CRITERIOS DE TERMINACIÓN:

- Existe openssl_verify() integrado.
- Las firmas se asocian con la llave del usuario correcto.
- La alteración invalida la firma.
- Rotación y revocación son funcionales.
- Las pruebas pasan.
- La documentación distingue RSA, HMAC e implicaciones legales.
5. Fase 3: Audit Trail
FASE ACTUAL: AUDITORÍA CON DETECCIÓN DE ALTERACIONES

Completar exclusivamente el Audit Trail.

Registrar:

- Usuario.
- Acción.
- Entidad.
- ID de entidad.
- Fecha y hora.
- IP.
- User-Agent.
- Resultado.
- Motivo.
- Datos anteriores.
- Datos posteriores.
- ID de correlación.
- Hash anterior.
- Hash actual.
- ID de firma.
- Fingerprint.
- Versión.

Requisitos:

1. Enmascarar y excluir:

- Contraseñas.
- Tokens.
- Llaves privadas.
- Frases de firma.
- Claves de licencia completas.
- Secretos.

2. Implementar cadena:

record_hash = SHA-256(previous_hash + payload_canónico)

3. Utilizar transacción y bloqueo cuando corresponda.

4. No proporcionar update o delete normal sobre auditoría.

5. Crear verificador de solo lectura.

6. El verificador debe detectar:

- Hash incorrecto.
- Cadena rota.
- Payload alterado.
- Firma inválida.
- Evento no verificable.

7. Integrar la auditoría en todas las acciones sensibles existentes.

8. No afirmar que la base es completamente inmutable.

Utilizar la expresión:

“Trazabilidad con detección criptográfica de alteraciones”.

9. Crear migraciones no destructivas.

10. Crear pruebas para:

- Cadena válida.
- Evento alterado.
- Previous hash alterado.
- Firma inválida.
- Exclusión de campos sensibles.
- Escritura concurrente simulada cuando sea posible.

CRITERIOS DE TERMINACIÓN:

- La bitácora incluye old/new de forma segura.
- Existe correlación.
- Existe cadena criptográfica.
- Existe verificador.
- No hay edición ni eliminación desde la aplicación.
- Las pruebas pasan.
6. Fase 4: estados, devolución, revisión, descarte y donación
FASE ACTUAL: CICLO DE VIDA FORMAL DEL ACTIVO

Completar una máquina central de estados.

Estados internos:

- DISPONIBLE.
- ASIGNADO.
- DEVOLUCION_REGISTRADA.
- REVISION_TECNICA.
- EN_REPARACION.
- DANADO.
- DESCARTE.
- DONADO.

La interfaz puede mostrar “Dañado”, pero el valor interno debe ser DANADO.

Implementar transiciones completas por origen y destino.

No permitir:

- Crear un activo como DESCARTE.
- Crear un activo como DONADO.
- Editar directamente el estado para saltar el flujo.
- ASIGNADO → DISPONIBLE directamente.
- DISPONIBLE → DESCARTE directamente.
- DISPONIBLE → DONADO directamente.

Todas las transiciones deben registrar:

- Usuario responsable.
- Estado anterior.
- Estado nuevo.
- Fecha.
- Motivo.
- Observación.
- Entidad origen.
- ID relacionado.
- Auditoría.
- Firma cuando corresponda.

Completar el flujo:

ASIGNADO
→ DEVOLUCION_REGISTRADA
→ REVISION_TECNICA
→ DISPONIBLE / EN_REPARACION / DESCARTE / DONADO

Devolución:

- Equipo.
- Asignación.
- Colaborador.
- Motivo.
- Estado físico.
- Observación.
- Evidencia.
- Usuario que recibe.
- Fecha.
- Firma.

Revisión:

- Técnico.
- Diagnóstico.
- Opinión.
- Recomendación.
- Resultado.
- Evidencia.
- Aprobador.
- Firma.

Descarte:

- Revisión obligatoria.
- Opinión técnica obligatoria.
- Motivo.
- Responsable.
- Fecha.
- Firma.
- Equipo no asignable.

Donación:

- Beneficiario.
- Responsable.
- Valor.
- Fecha.
- Motivo.
- Evidencia.
- Autorizador.
- Firma.
- Equipo no asignable.

Crear pruebas para todas las transiciones válidas e inválidas.

CRITERIOS DE TERMINACIÓN:

- No existen actualizaciones directas que evadan el servicio.
- Toda transición tiene actor.
- No se regresa directamente a disponible.
- Descarte y donación no pueden realizarse desde el formulario general.
- Las pruebas de estados pasan.
7. Fase 5: licencias, QR y portal del colaborador

Esta fase todavía puede ser grande. Es más fiable dividirla en 5A y 5B.

Fase 5A — Licencias y portal
FASE ACTUAL: LICENCIAS Y PORTAL DEL COLABORADOR

Completar licencias con:

- Proveedor.
- Tipo.
- URL.
- Fecha de adquisición.
- Vencimiento.
- Cantidad total.
- Cantidad asignada.
- Cantidad disponible.
- Observaciones.
- Estado.

Implementar:

cantidad_disponible =
cantidad_total - suma(asignaciones_activas)

Usar transacción y bloqueo de fila.

Impedir:

- Cantidad cero.
- Cantidad negativa.
- Sobreasignación.
- Asignación de licencia vencida o inactiva sin autorización.

Implementar cifrado autenticado para claves de licencia:

- Sodium secretbox cuando esté disponible.
- AES-256-GCM como fallback.

La clave maestra debe ser externa y no rastreada.

Si falta la clave:

- No guardar texto plano.
- Fallar de forma segura.

Crear herramienta de migración de texto plano a cifrado, pero no ejecutarla
sobre la base real.

Completar el portal para:

- Ver equipos propios.
- Ver detalle permitido.
- Ver licencias propias.
- Ver cupos.
- Ver vencimiento permitido.
- Consultar solicitudes.
- Consultar respuestas.
- Iniciar solicitud de devolución.
- Consultar estado de devolución.

Implementar controles IDOR en backend.

Crear pruebas para:

- Cifrado y descifrado.
- Clave maestra ausente.
- Cupos.
- Sobreasignación.
- Liberación.
- IDOR del portal.
Fase 5B — QR
FASE ACTUAL: QR SEGURO POR ACTIVO

Completar exclusivamente QR.

El QR debe contener una URL con token aleatorio y no:

inventory/detail?id=<ID>

Implementar:

- Token aleatorio.
- Hash del token en base de datos.
- Activo relacionado.
- Estado.
- Fecha.
- Revocación.
- Regeneración.
- Usuario creador.

Crear ruta pública limitada por token.

La vista pública solo puede mostrar:

- Etiqueta.
- Nombre.
- Categoría.
- Marca.
- Estado general.
- Precio.
- Fecha de adquisición.

No mostrar:

- Colaborador.
- Claves.
- Datos personales.
- Auditoría.
- Token.

La vista autenticada puede mostrar información adicional según rol.

Implementar:

- Generación.
- Visualización.
- Descarga real.
- Content-Disposition.
- Impresión.
- Revocación.
- Regeneración.
- Auditoría de acceso.
- Auditoría de revocación.
- Auditoría de regeneración.

Crear pruebas para:

- Token válido.
- Token incorrecto.
- Token revocado.
- Token regenerado.
- Token de otro activo.
- Información pública limitada.
- Autorización de información privada.
8. Fase 6: solicitudes, presupuestos, reportes y Excel

También debe dividirse.

Fase 6A — Solicitudes
FASE ACTUAL: SOLICITUDES E HISTORIAL

Completar solicitudes con:

- Tipo.
- Categoría.
- Justificación.
- Cantidad.
- Costo unitario estimado.
- Año objetivo.
- Prioridad.
- Estado.
- Usuario procesador.
- Respuesta administrativa.

Estados:

- EN_ESPERA.
- EN_TRAMITE.
- APROBADA.
- RECHAZADA.

Crear historial formal con:

- Estado anterior.
- Estado nuevo.
- Usuario.
- Fecha.
- Observación.
- Firma.
- Auditoría.

La aprobación o rechazo debe registrar quién procesó la solicitud.

El colaborador solo puede consultar sus solicitudes.

Crear pruebas de:

- Transiciones.
- Historial.
- Autorización.
- IDOR.
- Firma de aprobación y rechazo.
Fase 6B — Presupuesto
FASE ACTUAL: PRESUPUESTO ANUAL Y QUINQUENAL

Presupuesto anual:

total = suma(cantidad × costo_unitario_estimado)

No fijar cantidad en 1.

No convertir costos ausentes silenciosamente a cero.

Separar registros con costo y sin costo.

Permitir filtros y agrupación por:

- Año.
- Categoría.
- Tipo.
- Prioridad.
- Estado.

Proyección quinquenal:

presupuesto_n =
presupuesto_base
× (1 + crecimiento)^n
× (1 + inflación)^n

Generar cinco años reales.

Mostrar:

- Presupuesto base.
- Inflación.
- Crecimiento.
- Proyección por año.
- Total quinquenal.
- Registros sin costo.
- Supuestos.

Implementar exportación y autorización.

Crear pruebas matemáticas para:

- Cantidad.
- Costos.
- Registros sin costo.
- Cinco años.
- Inflación.
- Crecimiento.
- Total quinquenal.
Fase 6C — Reportes y Excel
FASE ACTUAL: REPORTES Y EXPORTACIONES

Crear un servicio central compartido por la vista y la exportación.

Completar reportes:

- Inventario.
- Categorías.
- Asignados por categoría.
- Disponibles.
- Asignados.
- Revisión.
- Reparación.
- Descartes.
- Donaciones.
- Licencias.
- Cupos.
- Vencimientos.
- Depreciación.
- Solicitudes.
- Devoluciones.
- Historial de estados.
- Presupuesto anual.
- Presupuesto quinquenal.

Los mismos filtros deben aplicarse en HTML y exportación.

La exportación debe:

- Tener formato real.
- Respetar filtros.
- Incluir título.
- Incluir usuario.
- Incluir fecha.
- Incluir filtros.
- Incluir totales.
- Registrar auditoría.
- Proteger contra Formula Injection.

Escapar valores iniciados por:

=
+
-
@

Crear pruebas que comparen el servicio del reporte con el contenido exportado.
9. Fase 7: imágenes, ubicaciones, documentación y UML

Divídela igualmente.

Fase 7A — Imágenes y ubicaciones
FASE ACTUAL: IMÁGENES E HISTORIAL DE UBICACIONES

Para equipos físicos nuevos:

- Exigir imagen principal e imagen adicional.
- Mantener compatibilidad con registros antiguos.
- Validar estrictamente las imágenes antes de guardarlas.

Validar:

- MIME real.
- Extensión.
- Peso.
- Dimensiones.
- Decodificación.
- Nombre aleatorio.
- Doble extensión.
- Imagen principal.
- Cantidad.

No modificar Nginx.

La ubicación debe poder ser opcional.

Implementar historial de ubicación:

- Colaborador.
- Ubicación anterior.
- Ubicación nueva.
- Tipo.
- Fecha inicial.
- Fecha final.
- Usuario.
- Motivo.
- Auditoría.

Crear pruebas para imágenes e historial.
Fase 7B — Documentación y UML
FASE ACTUAL: DOCUMENTACIÓN, UML Y MATRIZ

Actualizar documentación únicamente según funciones reales.

README:

- Obtener URL mediante git remote.
- No inventar URL.
- Eliminar placeholders falsos.
- Marcar video y backup como pendientes externos cuando no exista URL real.

Documentar:

- Instalación nueva.
- Migraciones.
- Backup.
- WampServer.
- RSA.
- HMAC.
- Gestión de llaves.
- Firmas.
- Limitaciones legales.
- Auditoría.
- Estados.
- Devoluciones.
- Licencias.
- QR.
- Presupuestos.
- Depreciación.
- Pruebas.

Crear o completar diagramas:

- Casos de uso.
- Clases.
- Componentes.
- MVC.
- Entidad-relación.
- Login.
- Asignación.
- Devolución.
- Revisión.
- Descarte.
- Donación.
- RSA.
- Rotación.
- Revocación.
- Estados.
- Solicitudes.
- Presupuesto.
- QR.
- Auditoría.

Actualizar los 287 requisitos en:

docs/MATRIZ_CUMPLIMIENTO.md

No dejar “Pendiente” cuando el requisito pueda evaluarse.

No marcar CUMPLE sin evidencia.
10. Prompt para continuar cuando Codex se detenga

Cuando una ejecución se interrumpa, no vuelvas a enviar el prompt gigante. Envía esto:

Continúa exclusivamente la fase que quedó incompleta.

Repositorio:

/home/mrmop/Downloads/Semestral/CMDB_Semestral

Antes de editar:

1. Lee docs/CODEX_PROGRESS.md.
2. Ejecuta git status --short.
3. Ejecuta git diff --stat.
4. Revisa los archivos ya modificados.
5. No repitas las tareas marcadas como COMPLETADAS.
6. No reviertas cambios existentes.
7. No comiences la siguiente fase.

Completa únicamente las tareas que aparecen en:

docs/CODEX_PROGRESS.md → Tareas pendientes

Verifica que no existan:

- Métodos vacíos.
- TODO.
- Rutas sin integrar.
- Migraciones incompletas.
- Botones simulados.
- Pruebas inventadas.

Al terminar:

- Ejecuta php -l en los archivos modificados.
- Ejecuta las pruebas de esta fase.
- Actualiza docs/CODEX_PROGRESS.md.
- Marca la fase como COMPLETADA solamente si satisface todos sus criterios.
- Muestra git status --short.
- Muestra git diff --stat.
- No hagas commit ni push.
- No cambies credenciales.
- No edites /var/www/html.
11. Prompt para comprobar una fase terminada

Antes de pasar a la siguiente, usa:

Audita únicamente la última fase marcada como COMPLETADA en:

docs/CODEX_PROGRESS.md

No modifiques archivos durante esta revisión.

Verifica:

1. Que todas las tareas de la fase estén realmente integradas.
2. Que no existan métodos vacíos o TODO.
3. Que las rutas apunten a métodos existentes.
4. Que los formularios tengan CSRF.
5. Que exista autorización.
6. Que las migraciones sean no destructivas.
7. Que las pruebas hayan sido ejecutadas realmente.
8. Que php -l pase.
9. Que no se hayan introducido secretos.
10. Que no se hayan introducido rutas absolutas.
11. Que public/.htaccess siga presente.
12. Que no se rompa WampServer.

Entrega:

- APROBADA PARA CONTINUAR, o
- REQUIERE CORRECCIÓN.

Si requiere corrección, indica archivo, línea, impacto y tarea exacta.
No empieces la siguiente fase.
12. Auditoría final

Solo después de que todas las fases estén marcadas como COMPLETADA, envía:

Realiza la auditoría final del proyecto.

No modifiques código durante esta auditoría.

Lee:

- docs/CODEX_PROGRESS.md
- docs/MATRIZ_CUMPLIMIENTO.md
- README
- Migraciones
- Pruebas
- Código actual

Ejecuta:

- php -l en todos los PHP.
- Validación JavaScript disponible.
- Todas las pruebas automatizadas.
- Revisión de rutas.
- Revisión de formularios POST y CSRF.
- Búsqueda de secretos.
- Búsqueda de rutas absolutas.
- Revisión de public/.htaccess.
- Revisión de compatibilidad WampServer.

Evalúa individualmente los 287 requisitos.

Compara con la auditoría anterior:

- CUMPLE: 187.
- PARCIAL: 71.
- NO CUMPLE: 28.
- NO VERIFICABLE: 1.
- Cumplimiento ponderado: 77.8%.

Separa:

1. Implementación estática.
2. Pruebas automatizadas.
3. Verificación en ejecución.
4. Pendientes externos.

No declares 100% si falta:

- Video real.
- URL real.
- Backup real.
- Migraciones aplicadas.
- Configuración externa de secretos.
- Prueba real en WampServer.
- Cualquier requisito no verificable.

Concluye con:

- LISTO PARA ENTREGAR.
- LISTO CON OBSERVACIONES.
- NO LISTO PARA ENTREGAR.

No hagas commit ni push.
Flujo recomendado

Usa una ejecución nueva de Codex para cada fase. La secuencia sería:

Fase 1
Fase 2A
Fase 2B
Fase 3
Fase 4
Fase 5A
Fase 5B
Fase 6A
Fase 6B
Fase 6C
Fase 7A
Fase 7B
Auditoría final

El archivo docs/CODEX_PROGRESS.md permite que una ejecución nueva sepa qué se terminó y qué continúa pendiente. Así Codex no necesita conservar en memoria todo el prompt anterior y es mucho menos probable que se detenga sin completar una parte funcional.

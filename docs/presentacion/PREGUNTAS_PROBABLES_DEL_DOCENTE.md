# Preguntas Probables del Docente

## Por que usar MVC?

Porque separa entrada/rutas, reglas de negocio, acceso a datos y vistas. Facilita mantenimiento, pruebas y extension sin mezclar HTML con consultas SQL.

## Donde se aplica OWASP?

En CSRF, sesiones, password hashing, validacion, escape de salida, PDO preparado, autorizacion por rol y control de errores.

## Como evitan eliminar usuarios?

El modulo usa baja logica mediante `activo` y estados de cuenta, no eliminacion fisica.

## Como se protege la integridad?

Con HMAC para campos criticos y servicios RSA para firmas digitales cuando existen llaves configuradas fuera del repositorio.

## Como se evita exponer secretos?

`.gitignore` excluye `.env`, `config.local.php`, llaves privadas, tokens, uploads reales y backups reales.

## Que falta para entrega perfecta?

Crear remoto GitHub, publicar video, generar backup externo y ejecutar prueba final con MySQL real.

## El proyecto funciona en Windows?

La documentacion incluye WampServer/XAMPP, `public/.htaccess`, configuracion local no versionada y SQL de instalacion limpia.

## Como se maneja un equipo devuelto?

El flujo registra devolucion, usuario receptor, fecha del sistema, estado de revision tecnica y posterior reubicacion o decision final.

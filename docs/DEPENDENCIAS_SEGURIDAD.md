# Inventario de dependencias y seguridad

Este proyecto no versiona dependencias externas de aplicacion mediante Composer o npm.

## Resultado verificado

Comandos usados para revisar dependencias versionadas:

```bash
find . -maxdepth 3 \( -name composer.json -o -name composer.lock -o -name package.json -o -name package-lock.json -o -name yarn.lock -o -name pnpm-lock.yaml -o -name vendor -o -name node_modules \)
```

Resultado esperado en este repositorio: sin salida.

Archivos estaticos propios versionados:

```text
public/assets/css/app.css
public/assets/js/app.js
```

## Dependencias de entorno

El sistema depende del entorno donde se ejecuta:

- PHP 8.1 o superior.
- Extensiones PHP obligatorias:
  - `pdo_mysql`: conexion PDO con MySQL/MariaDB.
  - `mbstring`: validacion y normalizacion multibyte en formularios, contrasenas y reportes.
  - `gd`: decodificacion de imagenes y miniaturas redimensionadas reales.
  - `fileinfo`: validacion del MIME real de archivos subidos.
  - `openssl`: firmas y verificacion RSA.
  - `sodium`: cifrado de claves de licencia y secretos cuando esta disponible.
- MySQL o MariaDB.
- Apache/WampServer en Windows para el equipo.
- Nginx y PHP-FPM solo en la copia local de Ubuntu, fuera del repositorio.

El comando de instalacion/verificacion que comprueba estas extensiones es:

```bash
php database/tools/verify_environment.php
```

Estas dependencias no se versionan dentro del proyecto. Su control de vulnerabilidades se debe hacer en el sistema operativo o plataforma local usando las herramientas propias del entorno.

## Politica

- No subir `vendor/` ni `node_modules/` si no existe un manifiesto formal.
- No agregar librerias externas copiadas manualmente sin documentar origen, version y licencia.
- Si en el futuro se agrega Composer o npm, se debe versionar el manifiesto y el lockfile correspondiente.
- Los secretos operativos, llaves RSA privadas, backups reales y configuraciones locales siguen fuera del repositorio.

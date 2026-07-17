# Entrega final y cierre de observaciones

Este documento resume lo que falta fuera del codigo para entregar el proyecto sin afectar WampServer, Ubuntu/Nginx ni credenciales locales.

## Estado tecnico del repositorio

La auditoria final del codigo deja:

```text
TOTAL 287
CUMPLE 282
NO VERIFICABLE 5
NO CUMPLE 0
CUMPLE PARCIALMENTE 0
```

Los puntos no verificables dependen de evidencias externas reales:

- URL real del repositorio.
- Video demostrativo real.
- Enlaces del video en README/documentacion.
- URL o ubicacion institucional del backup real.

No se deben inventar enlaces para cerrar estos puntos.

## Limitacion de certificacion

Una auditoria ejecutada sin MySQL/MariaDB operativo o sin `pdo_mysql`, `mbstring` y `gd` solo puede validar codigo/documentacion de forma parcial. En ese caso no marcar como completamente verificados por navegador:

- Login.
- CRUD completos.
- Carga de imagenes.
- Descargas.
- Reportes.
- Devoluciones.
- Donaciones.
- Presupuestos.
- Flujos completos por rol.

La validacion final debe ejecutarse sobre una base limpia en XAMPP o WampServer siguiendo:

```text
docs/VALIDACION_FINAL_XAMPP_WAMPSERVER.md
```

## Observacion Git

El proyecto ya tiene repositorio local inicializado en la rama `main`. Antes de subir a GitHub, confirmar la URL real del remoto y mantener fuera del repositorio `config.local.php`, `.env`, llaves privadas, uploads reales y backups reales.

## Backup real

Generar el backup fuera del repositorio:

```bash
mysqldump -u <usuario> -p cmdb_integral > /ruta/segura/fuera-del-repo/cmdb_integral_YYYYMMDD.sql
```

No guardar el backup real en:

```text
database/backups/
public/
storage/
```

`database/backups/` solo debe contener documentacion y `.gitkeep`.

## Video demostrativo

Usar como base:

```text
docs/Guion_Video.md
```

Despues de publicar el video, actualizar README con la URL real. No usar marcadores falsos.

## public/uploads

La carpeta contiene archivos de imagen usados por la instalacion/demo actual.

Antes de subir a GitHub:

1. Confirmar que no sean fotos reales de personas, equipos internos o informacion sensible.
2. Si son imagenes reales, reemplazarlas por imagenes demo anonimas.
3. Mantener `.gitkeep` para conservar la estructura.
4. No subir nuevas imagenes operativas generadas por usuarios.

La regla de `.gitignore` ya evita que nuevos archivos de subida se agreguen por accidente, pero si una imagen ya estaba versionada en el repositorio real, debe revisarse manualmente.

## Configuracion local

No subir:

```text
app/Config/config.local.php
.env
backups reales
llaves privadas
tokens
archivos de recuperacion
configuracion Nginx
```

La configuracion de Ubuntu/Nginx debe permanecer fuera del repositorio.

## Checklist antes de entregar

Usar tambien la guia paso a paso:

```text
docs/PRUEBAS_MANUALES_ENTREGA.md
docs/VALIDACION_FINAL_XAMPP_WAMPSERVER.md
```

Ejecutar desde la carpeta real del repositorio Git:

```bash
git status --short
```

Confirmar que no aparezcan:

```text
nginx.conf
default
sites-available
sites-enabled
config.local.php
.env
*.pem
*.key
*.dump
*.backup
*.bak
```

Ejecutar validaciones:

```bash
find app public tests -name '*.php' -exec php -l {} \;
node --check public/assets/js/app.js
for t in tests/*.php; do php "$t" || exit 1; done
```

Aplicar migraciones solo en una base respaldada y nunca sobre produccion sin respaldo.

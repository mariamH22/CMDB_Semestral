# Bloqueadores y No Verificables

## B1 - Base de datos real

- Estado: NO VERIFICABLE.
- Bloqueo: el entorno actual no permite conexion MySQL real.
- Error: `SQLSTATE[HY000] [2002] Operation not permitted`.
- Impacto: no se pudo certificar por navegador ni ejecutar verificadores con persistencia real.

## B2 - Remoto GitHub

- Estado: NO VERIFICABLE.
- Bloqueo: no hay `gh` instalado ni remoto configurado.
- Impacto: no existe URL final de repositorio para README/rubrica.

## B3 - Backup real

- Estado: NO VERIFICABLE.
- Bloqueo: el backup debe generarse fuera del repositorio con acceso a MySQL real.
- Impacto: falta URL de backup para rubrica.

## B4 - Video demostrativo

- Estado: NO VERIFICABLE.
- Bloqueo: requiere grabacion/publicacion externa.
- Impacto: falta evidencia para rubrica de video.

## B5 - Pruebas por navegador

- Estado: NO VERIFICABLE.
- Bloqueo: dependen de entorno web y base de datos real.
- Impacto: los modulos CRUD quedan con estado parcial en esta auditoria restringida.

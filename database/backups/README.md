# Respaldo y restauración - CMDB Integral

## Generar respaldo

```bash
mysqldump -u <usuario> -p cmdb_integral > /ruta/segura/cmdb_integral_YYYYMMDD.sql
```

Use una ruta fuera del repositorio. La carpeta `database/backups/` no debe contener respaldos reales versionados.

## Restaurar respaldo

```bash
mysql -u <usuario> -p cmdb_integral < /ruta/segura/cmdb_integral_YYYYMMDD.sql
```

## Recomendaciones

- No incluir la contraseña en archivos versionados.
- Guardar el respaldo en ubicación fuera del repositorio.
- Validar que el archivo de respaldo no esté vacío antes de restaurar.
- Mantener una rotación y fecha en el nombre del archivo.
- No ejecutar restauraciones sobre una base real sin confirmar respaldo, entorno y autorización.

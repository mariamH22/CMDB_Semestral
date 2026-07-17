# Base de datos - CMDB Integral

Este directorio separa por tipo de operación para evitar confusión entre instalación nueva, migración y respaldo.

## 1) Instalación limpia

- `install/fresh_install.sql`: script recomendado para crear la base desde cero y cargar semilla base.
- `cmdb_integral.sql`: script principal actualizado (compatible para instalación limpia).
- Estos archivos no son backups ni actualizaciones. Úselos únicamente en una base nueva o vacía.
- Si la base ya contiene datos, genere primero un respaldo y use únicamente migraciones incrementales revisadas.

## 2) Migraciones

- `migrations/`: scripts incrementales que deben ejecutarse sobre una base existente sin perder datos.
- Las migraciones se ejecutan manualmente después de revisar su contenido y confirmar que existe respaldo.
- Si falla una solicitud de devolución desde el portal, confirme que se ejecutó `migrations/2026_07_13_0007_ciclo_vida_activo_formal.sql`; esa migración agrega los estados `DEVOLUCION_REGISTRADA`, `REVISION_TECNICA` y `EN_REPARACION`.
- `migrations/2026_07_14_0001_contenido_demo_completo.sql`: semilla ampliada para demostración, con inventario, asignaciones, licencias, devoluciones, solicitudes, presupuesto, QR y noticias conectadas. Ejecutar después de `2026_07_13_0013_asignador_validaciones.sql`.

## 3) Respaldos

- `backups/README.md`: instrucciones para generar y restaurar copias seguras.
- Los backups reales no deben guardarse en este repositorio. Esta carpeta conserva solo documentación y `.gitkeep`.

## 4) Regla de seguridad

No usar scripts de instalación en ambientes con datos productivos sin un respaldo verificado.
No subir logs, caché, llaves privadas, tokens temporales ni respaldos reales al repositorio.

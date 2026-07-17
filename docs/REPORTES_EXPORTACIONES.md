# Reportes y exportaciones

Esta fase centraliza los reportes para que la vista HTML y los archivos Excel usen la misma fuente de datos. No cambia credenciales, `.htaccess`, rutas absolutas, configuracion de Ubuntu/Nginx ni archivos de servidor.

## Servicio central

La clase `App\Core\ReportService` concentra:

- Datos filtrados de inventario.
- Resumen por categoria.
- Activos asignados por categoria.
- Disponibles.
- Asignaciones.
- Reparacion.
- Descartes.
- Donaciones.
- Licencias.
- Cupos activos de licencias.
- Vencimientos de licencias.
- Depreciacion.
- Solicitudes.
- Devoluciones.
- Revisiones tecnicas.
- Historial formal de estados.

La pantalla `Reportes` consume el mismo servicio que las exportaciones. Asi se evita que la vista muestre un total y el Excel descargue otro.

## Filtros compartidos

Los reportes filtrables respetan:

- Tipo de activo.
- Estado.
- Categoria.
- Texto de busqueda.
- Rango de fechas.
- Sin asignar.
- Licencias disponibles.

Algunos reportes aplican un alcance propio. Por ejemplo, `Disponibles` solo lista activos en estado `DISPONIBLE`, `Donaciones` solo lista `DONADO` y `Reparacion` usa `EN_REPARACION` o el estado legado `MANTENIMIENTO`.

## Exportacion Excel

`App\Core\ExcelExporter` ahora acepta metadatos opcionales sin romper llamadas antiguas:

- Titulo.
- Usuario.
- Fecha de generacion.
- Filtros aplicados.
- Totales.

Tambien conserva la proteccion contra Formula Injection: valores que empiezan por `=`, `+`, `-` o `@` se prefijan con comilla simple antes de escapar HTML.

## Rutas agregadas

Todas las rutas se mantienen bajo el front controller existente:

```text
/reports/assigned-categories-excel
/reports/repairs-excel
/reports/license-seats-excel
/reports/expirations-excel
/reports/returns-excel
/reports/state-history-excel
```

No se agregan dominios, virtual hosts, puertos, archivos de Nginx ni configuracion local al repositorio.

## Prueba

La prueba `tests/Phase6CReportsExportTest.php` valida:

- Agrupacion por categoria.
- Asignados por categoria.
- Cupos de licencias.
- Vencimientos.
- Depreciacion.
- Relacion entre `ReportService` y `ExcelExporter`.
- Escape de Formula Injection en filas, usuario y filtros.

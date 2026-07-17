# Presupuesto anual y quinquenal

## Alcance

Esta fase formaliza el presupuesto anual y quinquenal a partir de solicitudes. No cambia credenciales, configuracion de Ubuntu/Nginx ni archivos de servidor.

## Calculo anual

El presupuesto anual usa:

```text
total = suma(cantidad * costo_unitario_estimado)
```

La cantidad registrada en la solicitud se respeta. No se fuerza a 1 cuando la columna existe.

## Costos ausentes

Un costo ausente no se convierte silenciosamente a cero.

El sistema separa:

- Partidas con costo.
- Solicitudes sin costo estimado.

Las solicitudes sin costo no suman al total y quedan visibles para completar datos.

## Filtros

Al generar presupuesto se puede filtrar por:

- Año objetivo.
- Categoria.
- Tipo.
- Prioridad.
- Estado de solicitud.

Los filtros se guardan como JSON cuando la migracion esta aplicada.

## Proyeccion quinquenal

Para cada año:

```text
presupuesto_n = presupuesto_base
    * (1 + crecimiento)^n
    * (1 + inflacion)^n
```

El quinquenal genera cinco años reales desde el año de inicio.

## Vista y exportacion

La pantalla muestra:

- Presupuesto base.
- Inflacion anual.
- Crecimiento anual.
- Proyeccion por año.
- Total quinquenal.
- Registros sin costo.
- Supuestos.

La exportacion Excel incluye detalle de partidas, estado de solicitud, prioridad y separacion de costos ausentes.

## Migracion

La migracion incremental esta en:

```text
database/migrations/2026_07_13_0011_presupuesto_anual_quinquenal.sql
```

Debe aplicarse manualmente con backup previo.

## Compatibilidad

- Si la migracion no esta aplicada, se mantiene el esquema anterior y se calcula con las columnas disponibles.
- Si la migracion esta aplicada, se guardan supuestos, filtros, base, total quinquenal y registros sin costo.
- No se modifica `public/.htaccess`.
- No se agregan archivos Nginx al repositorio.

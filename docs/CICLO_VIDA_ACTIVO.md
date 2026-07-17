# Ciclo de Vida Formal del Activo

El sistema usa una máquina central de estados para evitar saltos manuales y conservar trazabilidad.

## Estados internos

- `DISPONIBLE`
- `ASIGNADO`
- `DEVOLUCION_REGISTRADA`
- `REVISION_TECNICA`
- `EN_REPARACION`
- `DANADO`
- `DESCARTE`
- `DONADO`

`MANTENIMIENTO` se conserva como valor legado para instalaciones antiguas, pero el estado formal nuevo es `EN_REPARACION`.

## Flujo principal

```text
DISPONIBLE
→ ASIGNADO
→ DEVOLUCION_REGISTRADA
→ REVISION_TECNICA
→ DISPONIBLE / EN_REPARACION / DESCARTE / DONADO
```

## Restricciones

- No se puede crear un activo como `DESCARTE`.
- No se puede crear un activo como `DONADO`.
- No se puede editar directamente el estado desde el formulario general.
- No se permite `ASIGNADO → DISPONIBLE` directamente.
- No se permite `DISPONIBLE → DESCARTE` directamente.
- No se permite `DISPONIBLE → DONADO` directamente.
- Descarte y donación solo se resuelven desde revisión técnica.

## Datos registrados

Cada transición formal registra:

- Usuario responsable.
- Estado anterior.
- Estado nuevo.
- Motivo.
- Observación.
- Entidad origen.
- ID relacionado.
- Auditoría.
- Firma cuando exista llave RSA activa.

## Migración

La migración incremental correspondiente es:

```text
database/migrations/2026_07_13_0007_ciclo_vida_activo_formal.sql
```

Debe aplicarse manualmente con backup previo.

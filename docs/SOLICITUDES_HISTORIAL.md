# Solicitudes e historial formal

## Alcance

Esta fase formaliza las solicitudes de equipos, software y licencias sin cambiar configuracion local de Ubuntu/Nginx ni credenciales.

## Campos de solicitud

La solicitud registra:

- Tipo.
- Categoria.
- Descripcion.
- Justificacion.
- Cantidad.
- Costo unitario estimado.
- Año objetivo.
- Prioridad.
- Estado.
- Usuario procesador.
- Respuesta administrativa.

## Estados formales

Los estados nuevos son:

```text
EN_ESPERA
EN_TRAMITE
APROBADA
RECHAZADA
```

Estados legacy se normalizan para compatibilidad:

```text
PENDIENTE   -> EN_ESPERA
EN_REVISION -> EN_TRAMITE
ATENDIDA    -> APROBADA
CANCELADA   -> RECHAZADA
```

## Historial

Cada cambio real de estado registra:

- Solicitud.
- Estado anterior.
- Estado nuevo.
- Usuario.
- Observacion o respuesta administrativa.
- Firma, cuando exista RSA configurado y la accion lo requiera.
- Auditoria asociada.
- Fecha del evento.

La aprobacion y el rechazo requieren usuario procesador y respuesta administrativa.

## Autorizacion

- Administradores con `needs.manage` procesan solicitudes.
- Usuarios internos con `needs.view` consultan solicitudes.
- El colaborador solo consulta sus propias solicitudes desde el portal.
- Las reglas de pertenencia evitan IDOR.

## Migracion

La migracion incremental esta en:

```text
database/migrations/2026_07_13_0010_solicitudes_historial_formal.sql
```

Debe aplicarse manualmente con backup previo. No ejecuta cambios de servidor, Nginx ni credenciales.

## Compatibilidad

- El codigo normaliza estados antiguos y nuevos.
- Si la migracion no esta aplicada, el sistema guarda estados legacy compatibles.
- Si la migracion esta aplicada, guarda estados formales y campos enriquecidos.

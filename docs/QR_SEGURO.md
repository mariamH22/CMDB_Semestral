# QR seguro por activo

## Alcance

El QR identifica un activo mediante una URL pública con token aleatorio. No usa `inventory/detail?id=<ID>` y no expone datos sensibles.

## URL pública

El QR apunta a:

```text
/qr?t=<token_aleatorio>
```

La consulta pública solo muestra:

- Código o etiqueta del activo.
- Nombre.
- Categoría.
- Marca.
- Estado general.
- Precio.
- Fecha de adquisición.

No muestra:

- Colaborador responsable.
- Claves de licencia.
- Datos personales.
- Auditoría.
- Token como texto visible.

## Ciclo de vida

Desde el detalle autenticado del activo se puede:

- Generar QR.
- Visualizar QR.
- Descargar SVG real con `Content-Disposition`.
- Imprimir la etiqueta.
- Revocar QR.
- Regenerar QR.

La regeneración revoca el QR anterior y crea uno nuevo.

## Auditoría

Se registra:

- Acceso público válido.
- Acceso público inválido.
- Descarga autenticada.
- Generación.
- Revocación.
- Regeneración.

La auditoría no guarda el token en claro.

## Migración

La migración incremental está en:

```text
database/migrations/2026_07_13_0009_qr_seguro_activo.sql
```

Debe aplicarse manualmente después de hacer backup. No cambia credenciales ni configuración de Ubuntu/Nginx.

## Compatibilidad

- No modifica `public/.htaccess`.
- No agrega archivos Nginx al repositorio.
- No depende de `/var/www/html`.
- Mantiene rutas generadas con `url()`.

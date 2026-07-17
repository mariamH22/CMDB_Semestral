# Imágenes e historial de ubicaciones

Esta fase completa validaciones de imágenes e historial formal de ubicación. No modifica `.htaccess`, Nginx, credenciales ni rutas absolutas del proyecto.

## Imágenes de inventario

Para activos físicos nuevos (`HARDWARE`) las imágenes son obligatorias:

- Imagen principal obligatoria.
- Imagen adicional obligatoria.

Software y licencias pueden registrarse sin imagen. Si el formulario falla por otro campo, los archivos deben seleccionarse nuevamente por seguridad del navegador.

## Validación de archivos

Las imágenes se validan antes de guardarse:

- Error de subida PHP.
- Peso máximo de 2 MB.
- Extensión permitida: `jpg`, `jpeg`, `png`, `webp`.
- Bloqueo de doble extensión peligrosa como `shell.php.png`.
- MIME real con `finfo`.
- Dimensiones válidas con `getimagesize`.
- Límite de dimensiones máximas.
- Decodificación con GD obligatoria.
- Miniatura redimensionada real generada con GD. Si GD no está disponible, la carga falla.
- Nombre aleatorio generado por el servidor.

El nombre original del archivo nunca se usa como nombre final en `public/uploads`.
Si la inserción en base de datos falla después de subir archivos, el controlador elimina las imágenes generadas antes de devolver el error para evitar archivos huérfanos.

## Ubicación opcional

La ubicación del colaborador puede quedar vacía. Para bases ya instaladas se incluye migración incremental:

```text
database/migrations/2026_07_13_0012_imagenes_ubicaciones.sql
```

Los scripts de instalación limpia también declaran `colaboradores.ubicacion` como `NULL`.

## Historial de ubicación

Cuando cambia la ubicación de un colaborador se registra:

- Colaborador.
- Ubicación anterior.
- Ubicación nueva.
- Tipo normalizado: `OFICINA`, `EDIFICIO`, `CASA`, `SEDE`, `DIRECCION` u `OTRO`.
- Fecha inicial.
- Fecha final del registro anterior.
- Usuario.
- Motivo.
- `audit_id` cuando la columna existe.

El historial queda visible en el formulario de edición del colaborador.

## Prueba

La prueba `tests/Phase7AImagesLocationTest.php` valida:

- Imagen válida.
- Rechazo de extensión inválida.
- Rechazo de doble extensión peligrosa.
- Rechazo de hardware con cero o una imagen.
- Validación estricta de imagen principal y adicional.
- Limpieza de archivos subidos si falla la persistencia.
- Clasificación de tipos de ubicación.

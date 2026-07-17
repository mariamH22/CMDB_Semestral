# Licencias y portal del colaborador

## Alcance de la fase

Esta fase completa el control operativo de licencias y el portal del colaborador sin agregar configuracion especifica de Ubuntu o Nginx al repositorio.

## Licencias

- Las licencias registran proveedor, tipo, URL, fecha de adquisicion, vencimiento, cantidad total, cantidad asignada, cantidad disponible, observaciones y estado.
- La cantidad disponible se calcula como:

```text
cantidad_disponible = cantidad_total - suma(asignaciones_activas)
```

- La asignacion de cupos usa transaccion y bloqueo de filas activas.
- Se bloquea cantidad cero, cantidad negativa y sobreasignacion.
- Se bloquea asignar licencias vencidas o inactivas sin autorizacion.

## Cifrado de claves de licencia

Las claves nuevas se cifran con un servicio de cifrado autenticado:

- `sodium-secretbox` cuando la extension Sodium esta disponible.
- `aes-256-gcm` como respaldo cuando Sodium no esta disponible.

La clave maestra debe configurarse fuera del repositorio:

```text
CMDB_LICENSE_KEY_ENCRYPTION_KEY
```

o en `app/Config/config.local.php`:

```php
'security' => [
    'license_key_encryption_key' => 'clave-aleatoria-fuerte-fuera-de-git',
],
```

Si falta la clave maestra, el sistema permite registrar la licencia sin clave/serial y falla de forma segura si se intenta guardar ese dato sensible. Nunca guarda texto plano nuevo.

## Migracion de claves legadas

La migracion estructural esta en:

```text
database/migrations/2026_07_13_0008_licencias_portal_cifrado.sql
```

La herramienta para convertir claves legadas a cifrado esta en:

```text
database/tools/migrate_license_keys.php
```

Por defecto solo simula:

```bash
php database/tools/migrate_license_keys.php
```

Solo despues de hacer backup y configurar la clave maestra:

```bash
php database/tools/migrate_license_keys.php --apply
```

## Portal del colaborador

El colaborador puede:

- Ver sus equipos activos.
- Consultar detalle permitido sin secretos.
- Ver sus licencias activas, cupos asignados, vencimiento y estado.
- Consultar solicitudes y respuestas.
- Solicitar devolucion de un equipo propio.
- Consultar el estado de sus devoluciones.

El backend verifica que la asignacion pertenezca al colaborador autenticado antes de registrar una devolucion.

## Compatibilidad

- No se modifica `public/.htaccess`.
- No se agregan archivos de Nginx.
- No se cambian credenciales ni configuracion local.
- Las columnas nuevas son opcionales en el codigo hasta que la migracion se aplique.

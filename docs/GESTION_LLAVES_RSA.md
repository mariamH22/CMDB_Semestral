# Gestion de Llaves RSA

Este documento describe el procedimiento operativo para usar firmas RSA reales sin guardar llaves privadas dentro del repositorio.

## Reglas

- No subir llaves privadas al repositorio.
- No guardar rutas absolutas personales dentro del codigo fuente.
- Cada entorno debe proteger su propio almacen de llaves fuera de `public/` y fuera del proyecto versionado.
- La base de datos solo guarda la llave publica, el fingerprint y una referencia al archivo cifrado.
- Antes de activar llaves en una base real, hacer respaldo y validar permisos del almacen.
- Las llaves privadas se guardan cifradas con `security.key_encryption_key`; esa clave nunca debe versionarse.

## Configurar almacen local

Copiar `app/Config/config.local.example.php` como `app/Config/config.local.php` y definir:

```php
'security' => [
    'key_store_path' => 'RUTA_LOCAL_FUERA_DEL_PROYECTO',
    'key_encryption_key' => 'CLAVE_ALEATORIA_FUERTE_FUERA_DE_GIT',
]
```

`key_store_path` debe apuntar a una carpeta local fuera del proyecto/repositorio y definirse solo en configuracion local no versionada o variable de entorno.
Nunca debe apuntar a `public/`, `storage/` del proyecto ni ninguna carpeta versionada. Si el almacen no esta configurado o apunta al proyecto, el sistema falla de forma segura y no genera llaves.

## Generar llaves

La generacion operativa se hace desde **Bitacora > Gestionar llaves RSA** o mediante los servicios criptograficos del sistema:

1. Se genera RSA de 3072 bits.
2. Se obtiene la llave publica.
3. Se cifra la llave privada con AES-256-GCM.
4. Se guarda el archivo cifrado en el almacen configurado.
5. Se registra `key_store_reference`, `public_key`, `fingerprint`, `usuario_id`, `algoritmo`, `bits` y `estado`.

Ejemplo orientativo para registrar los metadatos devueltos por el servicio:

```sql
INSERT INTO llaves_rsa
(usuario_id, nombre, public_key, key_store_reference, private_key_encrypted, fingerprint, estado, algoritmo, bits)
VALUES (
  1,
  'Llave RSA de usuario',
  '-----BEGIN PUBLIC KEY-----...',
  'user_1_fingerprint.key',
  1,
  'FINGERPRINT_SHA256_DE_64_CARACTERES',
  'ACTIVA',
  'RSA-SHA256',
  3072
);
```

No insertar llaves privadas PEM directamente en la base. La pantalla administrativa exige CSRF, rol administrador y reautenticacion con la contrasena actual.

## Verificacion

El sistema muestra las firmas recientes en el modulo **Bitacora** y permite verificarlas manualmente desde **Gestionar llaves RSA**. Una firma puede aparecer como:

- `VALIDA`: la firma coincide con el hash registrado y la llave publica.
- `INVALIDA`: la firma no corresponde al hash o a la llave publica.
- `LLAVE_REVOCADA`: la firma coincide criptograficamente, pero la llave fue revocada.
- `NO_VERIFICABLE`: falta OpenSSL, la firma no es base64 valida o la llave publica no puede usarse.
- `ERROR`: ocurrio un error inesperado durante la verificacion.

Cada payload firmado incluye version, usuario, accion, entidad, entidad afectada, fecha, datos relevantes, hash del contenido, ID de auditoria, fingerprint e ID de correlacion.

## Rotacion

Para rotar una llave:

1. Cambiar la llave anterior a `REEMPLAZADA`.
2. Registrar la nueva llave como `ACTIVA`.
3. Conservar la llave publica anterior para verificar firmas historicas.
4. No borrar firmas historicas.

La aplicacion firma acciones futuras solo con llaves en estado `ACTIVA` del usuario autenticado. Una llave `REEMPLAZADA` conserva la verificacion historica, pero no se usa para firmas nuevas.

## Revocacion

Para revocar una llave:

1. Registrar motivo, fecha y usuario responsable.
2. Cambiar el estado a `REVOCADA`.
3. Bloquear nuevas firmas con esa llave.
4. Conservar la llave publica para validar registros historicos.

La revocacion no borra firmas ni llaves publicas; solo impide nuevas firmas y cambia el resultado administrativo de verificacion.

## Alcance

La firma RSA brinda no repudio tecnico dentro del sistema. El no repudio juridico requiere politica institucional de custodia, acceso, rotacion y responsabilidad sobre las llaves privadas.

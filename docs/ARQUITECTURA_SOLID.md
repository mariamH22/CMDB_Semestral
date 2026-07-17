# Arquitectura y SOLID

Este documento resume las decisiones usadas para mantener el sistema extensible sin depender de configuracion local de Ubuntu/Nginx ni rutas absolutas.

## Separacion de responsabilidades

- Los controladores coordinan HTTP, permisos, CSRF, validacion de entrada y respuesta.
- Los modelos encapsulan acceso a datos de su entidad.
- Los servicios de `app/Core` concentran reglas transversales: autorizacion, reportes, presupuesto, depreciacion, QR, licencias, validacion, errores y criptografia.
- `ReportService` evita duplicar logica entre vista HTML y exportaciones.
- `DepreciationCalculator` concentra la formula de depreciacion.
- `ModelFactory` concentra la construccion de dependencias de modelos para no acoplar controladores a `new Modelo(...)`.

## Dependencias de entorno

La instalacion debe comprobar estas extensiones PHP antes de ejecutar pruebas o formularios:

- `pdo_mysql`
- `mbstring`
- `gd`
- `fileinfo`
- `openssl`
- `sodium`

`mbstring` evita errores en validaciones y normalizacion de texto. El codigo incluye fallback para no producir errores fatales en pruebas de seguridad, pero la extension sigue siendo obligatoria en instalacion. `gd` no es opcional: se usa para validar imagenes y generar miniaturas reales.

La comprobacion operativa esta centralizada en:

```text
database/tools/verify_environment.php
```

## Configuracion

La aplicacion lee configuracion desde variables de entorno del sistema y desde:

```text
app/Config/config.local.php
```

El archivo `.env.example` es solo una referencia; copiarlo como `.env` no carga valores automaticamente. Para XAMPP/WampServer se recomienda ejecutar:

```text
php database/tools/configure_mysql_credentials.php
```

o crear `app/Config/config.local.php` desde el ejemplo.

## Flujos actualizados

- QR publico: usa token aleatorio, hash interno, revocacion y regeneracion. La vista publica muestra codigo, nombre, categoria, marca, estado, precio y fecha de adquisicion; no expone token, colaborador ni claves.
- Presupuesto: genera presupuesto anual y quinquenal desde solicitudes con cantidad, costo unitario, crecimiento e inflacion. Separa solicitudes sin costo.
- Devolucion formal: colaborador solicita, administrador u operador recibe fisicamente, luego se registra revision tecnica. La asignacion se cierra solo al finalizar la revision.
- Donacion: al cerrar una revision como `DONADO`, el activo queda `activo = 0` y sale del inventario operativo. El reporte de donaciones consulta historico independiente.
- Imagenes: hardware requiere dos imagenes. Se validan con `fileinfo`, `getimagesize` y `gd`; si falla la persistencia en base, el controlador elimina los archivos subidos para evitar huerfanos.

## Extension sin reescritura

- Los estados de inventario y solicitudes se agregan o validan desde `InventoryStatus` y `NeedStatus`.
- Los reportes nuevos se agregan como metodos de `ReportService` y rutas de exportacion, reutilizando `ExcelExporter`.
- Las funciones criptograficas se sustituyen por contratos: hash de password, firma, verificacion, integridad, cifrado, serializacion canonica y gestion de llaves.
- El almacenamiento de llaves privadas se encapsula tras `KeyStoreInterface`.

## Sustitucion segura

El sistema usa interfaces pequeñas para que las implementaciones puedan sustituirse sin romper consumidores:

- `PasswordHasherInterface`
- `IntegrityServiceInterface`
- `DigitalSignatureInterface`
- `SignatureVerifierInterface`
- `EncryptionServiceInterface`
- `CanonicalPayloadInterface`
- `KeyManagementInterface`
- `KeyStoreInterface`
- `ErrorRendererInterface`

No hay jerarquias profundas de clases de dominio; por eso el riesgo LSP se controla manteniendo contratos simples y retornos compatibles.

## Inversion de dependencias

- `Router` acepta una fabrica de controladores reemplazable.
- Los controladores aceptan `ModelFactory` opcional para pruebas o composicion externa.
- En ejecucion normal, `ModelFactory::default()` conserva compatibilidad con WampServer y el flujo actual.
- Los servicios criptograficos y de seguridad se obtienen desde `ServiceContainer` mediante contratos.

## Limites deliberados

El proyecto no usa un contenedor DI externo para evitar agregar dependencias y mantener portabilidad en WampServer. La inyeccion es ligera, explicita y compatible con PHP nativo.

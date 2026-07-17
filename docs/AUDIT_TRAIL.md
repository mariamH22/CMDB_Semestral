# Audit Trail

El sistema implementa trazabilidad con deteccion criptografica de alteraciones cuando la migracion incremental de Fase 3 esta aplicada.

## Datos registrados

- Usuario.
- Accion.
- Entidad e ID de entidad.
- Fecha y hora.
- IP.
- User-Agent.
- Resultado y motivo.
- Datos anteriores y posteriores saneados.
- ID de correlacion.
- Hash anterior.
- Hash actual.
- ID de firma y fingerprint cuando exista firma RSA asociada.
- Version de payload.

## Cadena criptografica

Cada evento calcula:

```text
record_hash = SHA-256(previous_hash + payload_canonico)
```

El payload se serializa de forma canonica para que el orden de las propiedades no cambie el hash.

## Datos sensibles

La auditoria no debe guardar secretos en claro. Se redactan o enmascaran:

- Contrasenas.
- Tokens.
- Llaves privadas.
- Frases de firma.
- Claves de licencia completas.
- Secretos de configuracion.

## Verificacion

El verificador de solo lectura esta disponible en:

```text
Bitacora > Verificar cadena
```

Puede detectar:

- Hash incorrecto.
- Cadena rota.
- Payload alterado.
- Firma invalida.
- Evento no verificable.

## Alcance

Esto no convierte la base de datos en completamente inmutable. La garantia correcta es:

```text
Trazabilidad con deteccion criptografica de alteraciones.
```

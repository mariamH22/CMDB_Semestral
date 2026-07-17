# Hallazgos de Seguridad

## H1 - MySQL real no verificable

- Estado: NO VERIFICABLE.
- Evidencia: `php database/tools/verify_environment.php`.
- Resultado real: `SQLSTATE[HY000] [2002] Operation not permitted`.
- Impacto: impide validar login, CRUD, IDOR y flujos criticos contra datos reales en esta sesion.
- Recomendacion: ejecutar validacion final en XAMPP/WampServer o Nginx con MySQL accesible.
- Prioridad: alta.

## H2 - Secretos operativos deben permanecer fuera de Git

- Estado: CUMPLE en configuracion revisada.
- Evidencia: `.gitignore`, `README.md`, `app/Config/config.local.example.php`.
- Resultado real: `git add --dry-run .` no incluyo `config.local.php`, `.env`, llaves privadas, uploads reales ni backups reales.
- Impacto: reduce riesgo de exponer credenciales y llaves.
- Recomendacion: antes de `git push`, repetir `git status --ignored --short` y revisar manualmente archivos nuevos.
- Prioridad: alta.

## H3 - RSA/HMAC implementado, pero depende de llaves locales

- Estado: PARCIAL operativo.
- Evidencia: `app/Core/Security/RsaKeyManagementService.php`, `FileKeyStore.php`, `IntegritySignerService.php`, `tests/Phase2ACryptoTest.php`, `tests/Phase2BSignatureTest.php`.
- Resultado real: pruebas criptograficas OK.
- Impacto: en produccion se requiere configurar llaves y claves fuera del repositorio.
- Recomendacion: documentar la ruta real del almacen seguro solo en configuracion local.
- Prioridad: media.

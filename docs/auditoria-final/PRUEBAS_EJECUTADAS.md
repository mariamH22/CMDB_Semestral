# Pruebas Ejecutadas

## Lint PHP

Comando:

```bash
find . -path ./.git -prune -o -name '*.php' -print0 | xargs -0 -n 1 php -l
```

Resultado: sin errores de sintaxis en `app`, `public`, `database/tools`, `tests` y vistas PHP.

## Tests del Proyecto

Comando:

```bash
for file in tests/*.php; do php "$file"; done
```

Resultado:

- `InstallSchemaSqlTest`: OK.
- `Phase1SecurityTest`: OK.
- `Phase2ACryptoTest`: OK.
- `Phase2BSignatureTest`: OK.
- `Phase3AuditTrailTest`: OK.
- `Phase4InventoryStatusTest`: OK.
- `Phase5ALicensePortalTest`: OK.
- `Phase5BQrSecurityTest`: OK.
- `Phase6ANeedWorkflowTest`: OK.
- `Phase6BBudgetProjectionTest`: OK.
- `Phase6CReportsExportTest`: OK.
- `Phase7AImagesLocationTest`: OK.

## Verificador de Entorno

Comando:

```bash
php database/tools/verify_environment.php
```

Resultado:

- `pdo_mysql`: OK.
- `mbstring`: OK.
- `gd`: OK.
- `fileinfo`: OK.
- `openssl`: OK.
- `sodium`: OK.
- Conexion a base real: NO VERIFICABLE por `SQLSTATE[HY000] [2002] Operation not permitted`.

## Revision de Versionado

Comandos:

```bash
git branch --show-current
git remote -v
git add --dry-run .
```

Resultado:

- Rama: `main`.
- Remoto: no configurado.
- Dry-run de `git add`: no incluyo `.env`, `config.local.php`, backups reales, uploads reales ni llaves privadas.

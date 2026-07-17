# Inventario de Evidencias

| Evidencia | Ruta |
|---|---|
| Entrada web | `public/index.php` |
| Rutas | `app/Core/Router.php` |
| Controladores | `app/Controllers/` |
| Modelos | `app/Models/` |
| Vistas | `app/Views/` |
| Servicios core | `app/Core/` |
| Contratos | `app/Core/Contracts/` |
| Seguridad | `app/Core/Security/` |
| Configuracion base | `app/Config/config.php` |
| Plantilla local | `app/Config/config.local.example.php` |
| SQL instalacion limpia | `database/install/fresh_install.sql` |
| SQL instalacion completa | `database/install/cmdb_integral_full_install.sql` |
| Migraciones | `database/migrations/` |
| Verificadores DB | `database/tools/` |
| Pruebas | `tests/` |
| CSS | `public/assets/css/app.css` |
| JavaScript | `public/assets/js/app.js` |
| UML fuente | `docs/uml/` |
| Diagramas | `docs/diagrams/` |
| Manual usuario | `docs/Manual_Usuario.md` |
| Guia entrega | `docs/ENTREGA_FINAL.md` |
| Pruebas manuales | `docs/PRUEBAS_MANUALES_ENTREGA.md` |
| Matriz historica | `docs/MATRIZ_CUMPLIMIENTO.md` |

## Comandos de Evidencia

```bash
git status --short
git branch --show-current
git remote -v
git add --dry-run .
find . -path ./.git -prune -o -name '*.php' -print0 | xargs -0 -n 1 php -l
for file in tests/*.php; do php "$file"; done
php database/tools/verify_environment.php
```

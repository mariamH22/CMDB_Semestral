# Auditoria Final CMDB

## Alcance

Proyecto auditado: `CMDB_Semestral`.

Ruta local: `/home/mrmop/Downloads/Semestral/CMDB_Semestral_terminado/CMDB_Semestral`.

Documento oficial revisado: `/home/mrmop/Downloads/2. Propuesta de Examen Final - Sistema de CMDB (Jorge Osorio) 10.xlsx`.

Hojas detectadas en el XLSX: `CMDB`, `Requisitos del Sistema`, `Documentacion UML`, `Hoja1`, `Observaciones`.

## Estado Git

- Rama local: `main`.
- Repositorio local: inicializado.
- Remoto GitHub: pendiente externo hasta crear o confirmar URL real.
- Backup real de base de datos: pendiente externo; no debe versionarse en `database/backups/`.

## Rubrica Oficial

La hoja `CMDB` registra estos criterios:

| Criterio | Puntaje maximo |
|---|---:|
| Documentacion UML | 10 |
| Arquitectura MVC ordenada | 5 |
| Cumplimiento de requisitos | 65 |
| Video explicativo en documentacion y resumen | 5 |
| URL de backup y repositorio | 10 |
| Revision preliminar | 10 |
| Penalizacion por no contar con CSS | -30 |

Nota: la hoja suma 105 al incluir revision preliminar. Se conserva esa inconsistencia como hallazgo de rubrica.

## Resultado Verificado

| Area | Estado | Puntaje sugerido |
|---|---|---:|
| Documentacion UML | CUMPLE | 9 / 10 |
| Arquitectura MVC | CUMPLE | 5 / 5 |
| Requisitos funcionales | PARCIAL | 55.9 / 65 |
| Video explicativo | NO VERIFICABLE | 0 / 5 |
| URL repositorio y backup | NO VERIFICABLE | 0 / 10 |
| Revision preliminar | CUMPLE segun XLSX | 10 / 10 |
| CSS | CUMPLE | sin penalizacion |

Puntaje general sugerido en esta auditoria restringida: 79.9 / 105.

Si se agregan URL real de GitHub, URL real del backup y video publicado, el puntaje externo puede aumentar hasta 94.9 / 105, sujeto a validacion docente y prueba por navegador/base real.

## Evidencia Tecnica

- Punto de entrada: `public/index.php`.
- MVC real: `app/Controllers`, `app/Models`, `app/Views`, `app/Core`.
- Configuracion base: `app/Config/config.php`.
- Configuracion local ignorada: `app/Config/config.local.php`.
- Plantilla local: `app/Config/config.local.example.php`.
- SQL limpio: `database/install/fresh_install.sql` y `database/install/cmdb_integral_full_install.sql`.
- Documentacion: `README.md`, `docs/DIAGRAMAS.md`, `docs/Manual_Usuario.md`, `docs/MATRIZ_CUMPLIMIENTO.md`.
- Seguridad: `app/Core/Csrf.php`, `app/Core/Authorization.php`, `app/Core/Security/*`, `app/Core/Contracts/*`.
- Pruebas: `tests/*.php`.

## Comandos Ejecutados

```bash
find . -path ./.git -prune -o -name '*.php' -print0 | xargs -0 -n 1 php -l
for file in tests/*.php; do php "$file"; done
php database/tools/verify_environment.php
```

Resultados:

- Lint PHP completo: sin errores.
- Tests PHP del proyecto: 12 OK.
- Extensiones PHP requeridas: OK.
- Conexion MySQL real: NO VERIFICABLE por bloqueo del entorno, `SQLSTATE[HY000] [2002] Operation not permitted`.

## Dictamen

El proyecto esta tecnicamente listo para versionarse y presentarse como entrega, con observaciones externas: crear remoto GitHub real, publicar video, generar backup real fuera del repositorio y ejecutar la prueba final en XAMPP/WampServer o Nginx con MySQL accesible.

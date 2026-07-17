# Informe Para Presentacion

## Proyecto

Sistema CMDB para administrar inventario de hardware, software, licencias, colaboradores, asignaciones, devoluciones, solicitudes, presupuesto, QR y auditoria.

## Problema

La organizacion necesita saber que activos tiene, quien los usa, en que estado estan, cuanto cuestan, cuando se deprecian, que licencias estan disponibles y que solicitudes futuras deben presupuestarse.

## Objetivo General

Construir una aplicacion web MVC en PHP nativo que centralice la gestion de activos y soporte trazabilidad, seguridad y reportes para una CMDB.

## Alcance

- Login y roles.
- Usuarios activos/inactivos.
- Inventario con imagenes, QR, depreciacion y estados.
- Categorias y colaboradores.
- Asignaciones, devoluciones, revision tecnica, descarte y donacion.
- Licencias y portal del colaborador.
- Solicitudes y presupuestos anual/quinquenal.
- Reportes exportables.
- Auditoria, HMAC/RSA, OWASP, CSRF y PDO.

## Arquitectura

- Entrada: `public/index.php`.
- Router/controladores: `app/Core/Router.php`, `app/Controllers/`.
- Modelos: `app/Models/`.
- Vistas: `app/Views/`.
- Servicios: `app/Core/`.
- Contratos: `app/Core/Contracts/`.
- Base de datos: `database/install/fresh_install.sql`.

## Estado Final

Proyecto listo para versionarse. Pendientes externos: remoto GitHub, video, backup real y validacion final por navegador con MySQL accesible.

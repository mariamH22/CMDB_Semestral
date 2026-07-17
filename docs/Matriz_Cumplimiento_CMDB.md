# Matriz de Cumplimiento - CMDB Integral

| Criterio de la hoja CMDB | Implementación entregada |
|---|---|
| Login con Hash | `usuarios.password_hash` con BCRYPT y `password_verify()` en `AuthController`. |
| Intentos, IP y fecha | Tablas `intentos_login` y `bitacora`; auditoría en `AuditLog`. |
| Máximo 3 intentos | `User::recordFailure()` bloquea la cuenta al tercer fallo. |
| CRUD de usuarios y baja lógica | `UsersController`, `User` y campo `usuarios.activo`. No hay borrado físico. |
| CRUD de inventario | `InventoryController`, `InventoryItem`, formularios y tabla `inventario`. |
| Imágenes y thumbnail | Hardware requiere dos imágenes; `Controller::uploadImage()` valida con `fileinfo`/`GD`, genera miniatura real y falla si GD no está disponible. |
| Depreciación | `vida_util_meses`, cálculo de límite y alerta de 90 días. |
| CRUD categorías | `CategoriesController`, tabla `categorias` y catálogo semilla. |
| CRUD colaboradores | `CollaboratorsController`, foto, identificación única, ubicación y datos de contacto. |
| Equipos por colaborador | `asignaciones`, `AssignmentsController` y Portal del Colaborador. |
| Daño, descarte y donación | Campo `inventario.estado`, responsable y fecha de donación. |
| Inventario sin asignar | Filtro `sin_asignar` en Inventario y método `InventoryItem::available()`. |
| Licencias sin asignar | Campo `es_licencia` y filtro `licencias` en Inventario. |
| Necesidades | `necesidades`, `NeedsController` y formulario del Portal. |
| Clase de conexión | `App\Core\Database` con PDO. |
| Manejo de errores e Interfaces | `ErrorRendererInterface`, `WebErrorRenderer`, `ErrorHandler`. |
| Sanitizar y validar | `Sanitizer`, `Validator` y validaciones en controladores. |
| OWASP / DRY / SOLID | CSRF, PDO, capas MVC, componentes centralizados, responsabilidad única. |
| Reportes Excel | `ReportsController` y `ExcelExporter`. |
| Portal colaborador | `PortalController`, equipos, solicitudes, historial, cambio y recuperación de contraseña. |
| CSS y menú horizontal | `public/assets/css/app.css` y navegación global en `Views/layout/header.php`. |
| Página de noticias | `HomeController`, `NewsController` y vistas públicas. |
| Backup de BD | `database/cmdb_integral.sql`. |
| README / Repositorio | `README.md` con instalación, credenciales, requisitos y enlaces externos marcados como pendientes reales cuando no hay URL verificable. |
| UML / documentación | `docs/DIAGRAMAS.md`, `docs/uml/`, `docs/diagrams/` y documentos técnicos por fase. |

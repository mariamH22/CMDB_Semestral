<?php
declare(strict_types=1);

// Cabeceras basicas de seguridad aplicadas desde PHP para funcionar igual en Apache/Nginx.
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

session_name('CMDBSESSID');
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

// Carga manual del autoloader y helpers para mantener el proyecto portable sin Composer.
require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
require_once dirname(__DIR__) . '/app/Core/helpers.php';

use App\Controllers\AssignmentsController;
use App\Controllers\AuditController;
use App\Controllers\AuthController;
use App\Controllers\BudgetsController;
use App\Controllers\CategoriesController;
use App\Controllers\CollaboratorsController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\InventoryController;
use App\Controllers\NeedsController;
use App\Controllers\NewsController;
use App\Controllers\PortalController;
use App\Controllers\ReportsController;
use App\Controllers\UsersController;
use App\Core\Config;
use App\Core\ErrorHandler;
use App\Core\Router;
use App\Core\WebErrorRenderer;

Config::load(require dirname(__DIR__) . '/app/Config/config.php');
date_default_timezone_set((string) Config::get('app.timezone', 'America/Panama'));
ErrorHandler::register(new WebErrorRenderer());

$router = new Router();

// Las rutas conectan URL + metodo HTTP con controladores MVC.
// La validacion de permisos se hace dentro de cada controlador.

// Sitio público
$router->get('/', [HomeController::class, 'index']);
$router->get('/news', [NewsController::class, 'index']);
$router->get('/qr', [InventoryController::class, 'qrLookup']);

// Autenticación y recuperación
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'forgotForm']);
$router->post('/forgot-password', [AuthController::class, 'forgot']);
$router->get('/reset-password', [AuthController::class, 'resetForm']);
$router->post('/reset-password', [AuthController::class, 'reset']);

// Dashboard y auditoría
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/audit', [AuditController::class, 'index']);
$router->get('/audit/trail', [AuditController::class, 'trail']);
$router->get('/audit/keys', [AuditController::class, 'keys']);
$router->post('/audit/keys/generate', [AuditController::class, 'generateKey']);
$router->post('/audit/keys/rotate', [AuditController::class, 'rotateKey']);
$router->post('/audit/keys/revoke', [AuditController::class, 'revokeKey']);
$router->post('/audit/signatures/verify', [AuditController::class, 'verifySignature']);

// Usuarios
$router->get('/users', [UsersController::class, 'index']);
$router->get('/users/create', [UsersController::class, 'create']);
$router->post('/users/store', [UsersController::class, 'store']);
$router->get('/users/edit', [UsersController::class, 'edit']);
$router->post('/users/update', [UsersController::class, 'update']);
$router->post('/users/unlock', [UsersController::class, 'unlock']);
$router->post('/users/toggle-active', [UsersController::class, 'toggleActive']);

// Categorías
$router->get('/categories', [CategoriesController::class, 'index']);
$router->get('/categories/create', [CategoriesController::class, 'create']);
$router->post('/categories/store', [CategoriesController::class, 'store']);
$router->get('/categories/edit', [CategoriesController::class, 'edit']);
$router->post('/categories/update', [CategoriesController::class, 'update']);
$router->post('/categories/deactivate', [CategoriesController::class, 'deactivate']);

// Colaboradores
$router->get('/collaborators', [CollaboratorsController::class, 'index']);
$router->get('/collaborators/create', [CollaboratorsController::class, 'create']);
$router->post('/collaborators/store', [CollaboratorsController::class, 'store']);
$router->get('/collaborators/edit', [CollaboratorsController::class, 'edit']);
$router->post('/collaborators/update', [CollaboratorsController::class, 'update']);

// Inventario
$router->get('/inventory', [InventoryController::class, 'index']);
$router->get('/inventory/create', [InventoryController::class, 'create']);
$router->post('/inventory/store', [InventoryController::class, 'store']);
$router->get('/inventory/edit', [InventoryController::class, 'edit']);
$router->post('/inventory/update', [InventoryController::class, 'update']);
$router->get('/inventory/detail', [InventoryController::class, 'detail']);
$router->get('/inventory/qr-svg', [InventoryController::class, 'qrSvg']);
$router->get('/inventory/qr-download', [InventoryController::class, 'qrDownload']);
$router->post('/inventory/qr-generate', [InventoryController::class, 'qrGenerate']);
$router->post('/inventory/qr-regenerate', [InventoryController::class, 'qrRegenerate']);
$router->post('/inventory/qr-revoke', [InventoryController::class, 'qrRevoke']);
$router->post('/inventory/status', [InventoryController::class, 'status']);
// Revelar licencias es una accion separada para aplicar permiso, CSRF y auditoria.
$router->post('/inventory/reveal-license', [InventoryController::class, 'revealLicense']);
$router->post('/inventory/license-assign', [InventoryController::class, 'assignLicense']);
$router->post('/inventory/license-release', [InventoryController::class, 'releaseLicense']);

// Asignaciones
$router->get('/assignments', [AssignmentsController::class, 'index']);
$router->get('/assignments/create', [AssignmentsController::class, 'create']);
$router->post('/assignments/store', [AssignmentsController::class, 'store']);
$router->post('/assignments/close', [AssignmentsController::class, 'close']);
$router->post('/assignments/receive', [AssignmentsController::class, 'receive']);
$router->post('/assignments/review', [AssignmentsController::class, 'review']);

// Necesidades
$router->get('/needs', [NeedsController::class, 'index']);
$router->post('/needs/update-status', [NeedsController::class, 'updateStatus']);
$router->post('/portal/needs/store', [NeedsController::class, 'createPortalRequest']);

// Presupuesto
$router->get('/budgets', [BudgetsController::class, 'index']);
$router->post('/budgets/generate', [BudgetsController::class, 'generate']);
$router->get('/budgets/excel', [BudgetsController::class, 'excel']);

// Reportes
$router->get('/reports', [ReportsController::class, 'index']);
$router->get('/reports/assets-excel', [ReportsController::class, 'assetsExcel']);
$router->get('/reports/assignments-excel', [ReportsController::class, 'assignmentsExcel']);
$router->get('/reports/available-excel', [ReportsController::class, 'availableExcel']);
$router->get('/reports/categories-excel', [ReportsController::class, 'categoriesExcel']);
$router->get('/reports/assigned-categories-excel', [ReportsController::class, 'assignedCategoriesExcel']);
$router->get('/reports/repairs-excel', [ReportsController::class, 'repairsExcel']);
$router->get('/reports/donations-excel', [ReportsController::class, 'donationsExcel']);
$router->get('/reports/discards-excel', [ReportsController::class, 'discardsExcel']);
$router->get('/reports/licenses-excel', [ReportsController::class, 'licensesExcel']);
$router->get('/reports/license-seats-excel', [ReportsController::class, 'licenseSeatsExcel']);
$router->get('/reports/expirations-excel', [ReportsController::class, 'expirationsExcel']);
$router->get('/reports/depreciation-excel', [ReportsController::class, 'depreciationExcel']);
$router->get('/reports/needs-excel', [ReportsController::class, 'needsExcel']);
$router->get('/reports/returns-excel', [ReportsController::class, 'returnsExcel']);
$router->get('/reports/reviews-excel', [ReportsController::class, 'reviewsExcel']);
$router->get('/reports/state-history-excel', [ReportsController::class, 'stateHistoryExcel']);

// Portal del colaborador
$router->get('/portal', [PortalController::class, 'index']);
$router->get('/portal/password', [PortalController::class, 'passwordForm']);
$router->post('/portal/password', [PortalController::class, 'changePassword']);
$router->post('/portal/returns/store', [PortalController::class, 'requestReturn']);

// Noticias
$router->get('/news/admin', [NewsController::class, 'adminIndex']);
$router->get('/news/create', [NewsController::class, 'create']);
$router->post('/news/store', [NewsController::class, 'store']);
$router->get('/news/edit', [NewsController::class, 'edit']);
$router->post('/news/update', [NewsController::class, 'update']);

// Punto final del front controller: normaliza la URI y ejecuta la accion solicitada.
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

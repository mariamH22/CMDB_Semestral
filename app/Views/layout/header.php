<?php
use App\Core\Auth;
use App\Core\Config;

$sessionUser = Auth::user();
$isAuthenticated = Auth::check();
$isInternal = Auth::isInternal();
$basePath = rtrim((string) Config::get('app.base_path', ''), '/');
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($basePath !== '' && str_starts_with($requestPath, $basePath)) {
    $requestPath = substr($requestPath, strlen($basePath)) ?: '/';
}
$requestPath = trim($requestPath, '/');
$isCurrent = static function (string $path) use ($requestPath): bool {
    $path = trim($path, '/');
    return $path === ''
        ? $requestPath === ''
        : ($requestPath === $path || str_starts_with($requestPath, $path . '/'));
};

$primaryNav = [
    ['label' => 'Dashboard', 'path' => 'dashboard', 'permission' => 'dashboard.view'],
    ['label' => 'Inventario', 'path' => 'inventory', 'permission' => 'inventory.view'],
    ['label' => 'Colaboradores', 'path' => 'collaborators', 'permission' => 'collaborators.view'],
    ['label' => 'Asignaciones', 'path' => 'assignments', 'permission' => 'assignments.view'],
    ['label' => 'Necesidades', 'path' => 'needs', 'permission' => 'needs.view'],
    ['label' => 'Presupuesto', 'path' => 'budgets', 'permission' => 'budgets.view'],
    ['label' => 'Reportes', 'path' => 'reports', 'permission' => 'reports.view'],
];

$adminNav = [
    ['label' => 'Categorías', 'path' => 'categories', 'permission' => 'categories.view'],
    ['label' => 'Usuarios', 'path' => 'users', 'permission' => 'users.manage'],
    ['label' => 'Bitácora', 'path' => 'audit', 'permission' => 'audit.view'],
    ['label' => 'Noticias', 'path' => 'news/admin', 'permission' => 'news.manage'],
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($title ?? 'CMDB Integral') . ' · CMDB Integral') ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="<?= $isAuthenticated ? 'app-body' : 'public-body' ?>">
<a class="skip-link" href="#contenido">Saltar al contenido</a>

<?php if ($isAuthenticated): ?>
<div class="app-shell" data-app-shell>
    <div class="sidebar-backdrop" data-sidebar-close hidden></div>
    <aside class="app-sidebar" id="app-sidebar" aria-label="Navegación del sistema">
        <button class="sidebar-close" type="button" data-sidebar-close aria-label="Cerrar navegación">Cerrar</button>
        <a class="brand app-brand" href="<?= url($isInternal ? 'dashboard' : 'portal') ?>">
            <span class="brand-mark">CMDB</span>
            <span><strong>Integral</strong><small>Mesa CMDB</small></span>
        </a>

        <nav class="sidebar-nav">
            <?php if (Auth::isCollaborator()): ?>
                <p class="nav-group-label">Portal</p>
                <a class="nav-item <?= $isCurrent('portal') ? 'is-current' : '' ?>" href="<?= url('portal') ?>" <?= $isCurrent('portal') ? 'aria-current="page"' : '' ?>>Mi portal</a>
                <a class="nav-item <?= $isCurrent('portal/password') ? 'is-current' : '' ?>" href="<?= url('portal/password') ?>" <?= $isCurrent('portal/password') ? 'aria-current="page"' : '' ?>>Cambiar contraseña</a>
            <?php else: ?>
                <p class="nav-group-label">Operación</p>
                <?php foreach ($primaryNav as $navItem): ?>
                    <?php if (Auth::can($navItem['permission'])): ?>
                        <a class="nav-item <?= $isCurrent($navItem['path']) ? 'is-current' : '' ?>" href="<?= url($navItem['path']) ?>" <?= $isCurrent($navItem['path']) ? 'aria-current="page"' : '' ?>><?= e($navItem['label']) ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>

                <p class="nav-group-label">Administración</p>
                <?php foreach ($adminNav as $navItem): ?>
                    <?php if (Auth::can($navItem['permission'])): ?>
                        <a class="nav-item <?= $isCurrent($navItem['path']) ? 'is-current' : '' ?>" href="<?= url($navItem['path']) ?>" <?= $isCurrent($navItem['path']) ? 'aria-current="page"' : '' ?>><?= e($navItem['label']) ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php unset($navItem); ?>
            <?php endif; ?>
        </nav>

        <div class="sidebar-card">
            <span class="small muted">Sesión activa</span>
            <strong><?= e($sessionUser['nombre_usuario'] ?? 'Usuario') ?></strong>
            <span class="badge badge-info"><?= e($sessionUser['rol'] ?? '') ?></span>
        </div>
    </aside>

    <div class="app-workspace">
        <header class="app-topbar">
            <button class="menu-toggle" type="button" data-sidebar-open aria-controls="app-sidebar" aria-expanded="false" aria-label="Abrir navegación">
                <span></span><span></span><span></span>
            </button>
            <div>
                <p class="eyebrow">Mesa operativa CMDB</p>
                <strong><?= e($title ?? 'Panel principal') ?></strong>
            </div>
            <div class="topbar-actions">
                <span class="topbar-role"><?= e($sessionUser['rol'] ?? '') ?></span>
                <a class="btn btn-light btn-small" href="<?= url('news') ?>">Noticias</a>
                <form class="logout-form" method="post" action="<?= url('logout') ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-danger btn-small" type="submit">Salir</button>
                </form>
            </div>
        </header>

        <main id="contenido" class="app-main" tabindex="-1">
<?php else: ?>
<header class="public-header">
    <div class="container public-navbar">
        <a class="brand" href="<?= url('') ?>">
            <span class="brand-mark">CMDB</span>
            <span><strong>Integral</strong><small>Mesa CMDB</small></span>
        </a>
        <nav class="public-nav" aria-label="Navegación pública">
            <a href="<?= url('') ?>" <?= $isCurrent('') ? 'aria-current="page"' : '' ?>>Inicio</a>
            <a href="<?= url('news') ?>" <?= $isCurrent('news') ? 'aria-current="page"' : '' ?>>Noticias</a>
            <a class="btn btn-primary btn-small" href="<?= url('login') ?>">Iniciar sesión</a>
        </nav>
    </div>
</header>

<main id="contenido" class="container page-shell" tabindex="-1">
<?php endif; ?>

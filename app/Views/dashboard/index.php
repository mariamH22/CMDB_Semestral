<?php
use App\Core\NeedStatus;

$filters = $dashboardFilters ?? ['tipo' => '', 'categoria_id' => 0, 'ubicacion' => '', 'estado' => ''];
$filterOptions = $dashboardFilterOptions ?? ['tipos' => [], 'estados' => [], 'ubicaciones' => []];
$categoryOptions = $categories ?? [];
$generatedAtLabel = $generatedAt instanceof DateTimeInterface ? $generatedAt->format('d/m/Y H:i') : date('d/m/Y H:i');

$totalAssets = (int) ($counts['total'] ?? 0);
$availableAssets = (int) ($counts['disponibles'] ?? 0);
$assignedAssets = (int) ($counts['asignados'] ?? 0);
$damagedAssets = (int) ($counts['danados'] ?? 0);
$discardAssets = (int) ($counts['descarte'] ?? 0);
$licenseAssets = (int) ($counts['licencias'] ?? 0);
$activeAssignmentsCount = count($activeAssignments);
$pendingReturnsCount = count($pendingReturns ?? []);
$nearDepreciationCount = count($nearDepreciation);

$needCounters = ['espera' => 0, 'tramite' => 0, 'cerradas' => 0];
foreach ($needs as $need) {
    $status = NeedStatus::normalize((string) ($need['estado'] ?? ''));
    if ($status === NeedStatus::EN_ESPERA) {
        $needCounters['espera']++;
    } elseif ($status === NeedStatus::EN_TRAMITE) {
        $needCounters['tramite']++;
    } elseif (in_array($status, [NeedStatus::APROBADA, NeedStatus::RECHAZADA], true)) {
        $needCounters['cerradas']++;
    }
}
$openNeeds = $needCounters['espera'] + $needCounters['tramite'];
$totalNeeds = array_sum($needCounters);

$licenseSummary = $licenseSummary ?? [];
$availableLicenseSeats = (int) ($licenseSummary['cupos_disponibles'] ?? 0);
$licensesWithSeats = (int) ($licenseSummary['con_cupos'] ?? 0);
$expiredLicenses = max((int) ($licenseSummary['vencidas'] ?? 0), (int) ($licenseSummary['estado_vencida'] ?? 0));
$nearLicenseExpiration = (int) ($licenseSummary['proximas_vencer'] ?? 0);

$availabilityRate = $totalAssets > 0 ? round(($availableAssets / $totalAssets) * 100) : 0;
$highPriorityTotal = $damagedAssets + $discardAssets + $pendingReturnsCount + $expiredLicenses;
$mediumPriorityTotal = $nearDepreciationCount + $nearLicenseExpiration;
$criticalLevel = $highPriorityTotal > 0 ? 'is-critical' : ($mediumPriorityTotal > 0 ? 'is-warning' : 'is-ok');
$hasFilters = (bool) array_filter($filters, static fn (mixed $value): bool => (string) $value !== '' && (string) $value !== '0');
?>

<section class="section-header dashboard-header">
    <div>
        <p class="eyebrow">Administración CMDB</p>
        <h1>Panel de control</h1>
        <p>Resumen de activos, custodias, licencias y alertas operativas.</p>
        <p class="dashboard-generated">Panel generado el <?= e($generatedAtLabel) ?></p>
    </div>
    <div class="button-row dashboard-actions">
        <a class="btn btn-light" href="<?= url('reports') ?>">Reportes</a>
        <a class="btn btn-primary" href="<?= url('inventory/create') ?>">Registrar activo</a>
    </div>
</section>

<form class="card dashboard-filter" method="get" action="<?= url('dashboard') ?>" aria-label="Filtros del dashboard">
    <div class="dashboard-filter-grid">
        <div class="form-group">
            <label for="dashboard_tipo">Tipo de activo</label>
            <select id="dashboard_tipo" name="tipo">
                <option value="">Todos</option>
                <?php foreach ($filterOptions['tipos'] ?? [] as $option): ?>
                    <option value="<?= e($option['value']) ?>" <?= selected($filters['tipo'], $option['value']) ?>><?= e($option['value']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="dashboard_categoria">Categoría</label>
            <select id="dashboard_categoria" name="categoria_id">
                <option value="0">Todas</option>
                <?php foreach ($categoryOptions as $category): ?>
                    <option value="<?= e($category['id']) ?>" <?= selected($filters['categoria_id'], $category['id']) ?>><?= e($category['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="dashboard_ubicacion">Ubicación</label>
            <select id="dashboard_ubicacion" name="ubicacion">
                <option value="">Todas</option>
                <?php foreach ($filterOptions['ubicaciones'] ?? [] as $option): ?>
                    <option value="<?= e($option['value']) ?>" <?= selected($filters['ubicacion'], $option['value']) ?>><?= e($option['value']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="dashboard_estado">Estado de activo</label>
            <select id="dashboard_estado" name="estado">
                <option value="">Todos</option>
                <?php foreach ($filterOptions['estados'] ?? [] as $option): ?>
                    <option value="<?= e($option['value']) ?>" <?= selected($filters['estado'], $option['value']) ?>><?= e(\App\Core\InventoryStatus::label((string) $option['value'])) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="dashboard-filter-actions">
            <button class="btn btn-primary btn-small" type="submit">Aplicar</button>
            <a class="btn btn-light btn-small" href="<?= url('dashboard') ?>">Limpiar filtros</a>
        </div>
    </div>
    <p class="dashboard-filter-note">
        Los filtros usan datos reales. El estado aplica a inventario, custodias y alertas; las solicitudes se filtran por tipo, categoría y ubicación cuando corresponde.
    </p>
    <?php if ($hasFilters): ?>
        <p class="dashboard-filter-active" role="status">Vista filtrada activa.</p>
    <?php endif; ?>
</form>

<section class="dashboard-summary" aria-labelledby="dashboard-summary-title">
    <div class="dashboard-section-heading">
        <div>
            <p class="eyebrow">Resumen general</p>
            <h2 id="dashboard-summary-title">Inventario y operación actual</h2>
        </div>
    </div>

    <div class="dashboard-summary-layout">
        <article class="dashboard-summary-primary">
            <div>
                <span>Inventario activo</span>
                <strong><?= e((string) $totalAssets) ?> activos</strong>
            </div>
            <div class="dashboard-progress" aria-label="<?= e((string) $availableAssets) ?> de <?= e((string) $totalAssets) ?> activos disponibles, <?= e((string) $availabilityRate) ?>%">
                <div class="dashboard-progress-label">
                    <span>Disponibilidad</span>
                    <strong><?= e((string) $availabilityRate) ?>%</strong>
                </div>
                <meter class="dashboard-meter" min="0" max="100" value="<?= e((string) $availabilityRate) ?>">
                    <?= e((string) $availabilityRate) ?>%
                </meter>
                <small><?= e((string) $availableAssets) ?> de <?= e((string) $totalAssets) ?> disponibles</small>
            </div>
        </article>

        <div class="dashboard-summary-metrics">
            <article><span>Disponibles</span><strong><?= e((string) $availableAssets) ?></strong></article>
            <article><span>Asignados</span><strong><?= e((string) $assignedAssets) ?></strong></article>
            <article><span>Licencias</span><strong><?= e((string) $licenseAssets) ?></strong></article>
            <article><span>Solicitudes activas</span><strong><?= e((string) $openNeeds) ?></strong></article>
        </div>
    </div>
</section>

<section class="dashboard-critical <?= e($criticalLevel) ?>" aria-labelledby="dashboard-critical-title">
    <div class="dashboard-section-heading">
        <div>
            <p class="eyebrow">Estado crítico</p>
            <h2 id="dashboard-critical-title">Requieren atención</h2>
        </div>
        <div class="dashboard-critical-indicator" aria-label="Nivel de atención operativa">
            <span><?= $criticalLevel === 'is-ok' ? 'Sin incidencias' : ($criticalLevel === 'is-critical' ? 'Crítico' : 'Atención') ?></span>
        </div>
    </div>

    <div class="dashboard-critical-grid">
        <a class="dashboard-critical-item <?= $damagedAssets > 0 ? 'is-critical' : 'is-ok' ?>" href="<?= url('inventory?estado=DANADO') ?>">
            <span>Equipos dañados</span>
            <strong><?= e((string) $damagedAssets) ?></strong>
            <small><?= $damagedAssets > 0 ? 'Requieren diagnóstico o reparación.' : 'Sin daños activos.' ?></small>
        </a>
        <a class="dashboard-critical-item <?= $discardAssets > 0 ? 'is-critical' : 'is-ok' ?>" href="<?= url('inventory?estado=DESCARTE') ?>">
            <span>En descarte</span>
            <strong><?= e((string) $discardAssets) ?></strong>
            <small><?= $discardAssets > 0 ? 'Pendientes de cierre formal.' : 'Sin descartes pendientes.' ?></small>
        </a>
        <a class="dashboard-critical-item <?= $pendingReturnsCount > 0 ? 'is-critical' : 'is-ok' ?>" href="<?= url('assignments') ?>">
            <span>Revisión técnica</span>
            <strong><?= e((string) $pendingReturnsCount) ?></strong>
            <small><?= $pendingReturnsCount > 0 ? 'Devoluciones pendientes de revisión.' : 'Sin devoluciones pendientes.' ?></small>
        </a>
        <a class="dashboard-critical-item <?= $nearDepreciationCount > 0 ? 'is-warning' : 'is-ok' ?>" href="<?= url('inventory') ?>">
            <span>Depreciación próxima</span>
            <strong><?= e((string) $nearDepreciationCount) ?></strong>
            <small><?= $nearDepreciationCount > 0 ? 'Dentro del rango de 90 días.' : 'Sin vencimientos próximos.' ?></small>
        </a>
        <a class="dashboard-critical-item <?= ($expiredLicenses + $nearLicenseExpiration) > 0 ? ($expiredLicenses > 0 ? 'is-critical' : 'is-warning') : 'is-ok' ?>" href="<?= url('inventory?licencias=1') ?>">
            <span>Licencias por vencer</span>
            <strong><?= e((string) ($expiredLicenses + $nearLicenseExpiration)) ?></strong>
            <small><?= $expiredLicenses > 0 ? e((string) $expiredLicenses) . ' vencidas.' : ($nearLicenseExpiration > 0 ? 'Próximas a vencer.' : 'Sin alertas de licencia.') ?></small>
        </a>
    </div>
</section>

<section class="dashboard-management" aria-labelledby="dashboard-management-title">
    <div class="dashboard-section-heading">
        <div>
            <p class="eyebrow">Gestión activa</p>
            <h2 id="dashboard-management-title">Trabajo administrativo en curso</h2>
        </div>
    </div>

    <div class="dashboard-management-grid">
        <article class="dashboard-management-card">
            <div class="dashboard-management-title">
                <span>Solicitudes</span>
                <strong><?= e((string) $totalNeeds) ?></strong>
            </div>
            <div class="dashboard-traffic" aria-label="Solicitudes: <?= e((string) $needCounters['espera']) ?> en espera, <?= e((string) $needCounters['tramite']) ?> en trámite, <?= e((string) $needCounters['cerradas']) ?> cerradas">
                <span class="is-waiting"><strong><?= e((string) $needCounters['espera']) ?></strong><small>En espera</small></span>
                <span class="is-progress"><strong><?= e((string) $needCounters['tramite']) ?></strong><small>En trámite</small></span>
                <span class="is-closed"><strong><?= e((string) $needCounters['cerradas']) ?></strong><small>Cerradas</small></span>
            </div>
        </article>
        <article class="dashboard-management-card">
            <div class="dashboard-management-title">
                <span>Custodias</span>
                <strong><?= e((string) $activeAssignmentsCount) ?></strong>
            </div>
            <p>Asignaciones activas con responsable vigente.</p>
        </article>
        <article class="dashboard-management-card">
            <div class="dashboard-management-title">
                <span>Licencias disponibles</span>
                <strong><?= e((string) $availableLicenseSeats) ?></strong>
            </div>
            <p><?= e((string) $licensesWithSeats) ?> licencias tienen cupos libres.</p>
        </article>
    </div>
</section>

<section class="dashboard-actions-panel" aria-labelledby="dashboard-actions-title">
    <div class="dashboard-section-heading">
        <div>
            <p class="eyebrow">Acciones rápidas</p>
            <h2 id="dashboard-actions-title">Atajos operativos</h2>
        </div>
    </div>
    <div class="dashboard-quicklinks">
        <?php if (\App\Core\Auth::can('inventory.manage')): ?><a href="<?= url('inventory/create') ?>"><span>Registrar activo</span><small>Alta de equipo o licencia</small></a><?php endif; ?>
        <?php if (\App\Core\Auth::can('assignments.manage')): ?><a href="<?= url('assignments/create') ?>"><span>Asignar activo</span><small>Crear custodia</small></a><?php endif; ?>
        <?php if (\App\Core\Auth::can('collaborators.manage')): ?><a href="<?= url('collaborators/create') ?>"><span>Nuevo colaborador</span><small>Perfil y contacto</small></a><?php endif; ?>
        <?php if (\App\Core\Auth::can('needs.view')): ?><a href="<?= url('needs') ?>"><span>Procesar solicitudes</span><small>Revisión administrativa</small></a><?php endif; ?>
        <?php if (\App\Core\Auth::can('budgets.view')): ?><a href="<?= url('budgets') ?>"><span>Presupuesto</span><small>Proyección anual</small></a><?php endif; ?>
    </div>
</section>

<section class="dashboard-details" aria-labelledby="dashboard-details-title">
    <div class="dashboard-section-heading dashboard-details-heading">
        <div>
            <p class="eyebrow">Información operativa detallada</p>
            <h2 id="dashboard-details-title">Depreciación, solicitudes y custodias recientes</h2>
        </div>
    </div>

    <div class="dashboard-details-grid">
        <section class="card dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <p class="eyebrow">Depreciación</p>
                    <h3>Equipos cerca de depreciación</h3>
                </div>
                <span class="badge badge-warning">90 días</span>
            </div>
            <?php if ($nearDepreciation): ?>
                <div class="table-wrap dashboard-table">
                    <table class="responsive-table dashboard-compact-table">
                        <caption>Primeros equipos próximos a completar su vida útil operativa.</caption>
                        <thead><tr><th scope="col">Activo</th><th scope="col">Serie</th><th scope="col">Límite</th><th scope="col">Estado</th></tr></thead>
                        <tbody>
                        <?php foreach (array_slice($nearDepreciation, 0, 6) as $item): ?>
                            <tr>
                                <td data-label="Activo"><a href="<?= url('inventory/detail?id=' . $item['id']) ?>"><?= e($item['nombre']) ?></a></td>
                                <td data-label="Serie"><?= e($item['serie']) ?></td>
                                <td data-label="Límite"><?= e($item['fecha_limite_depreciacion']) ?></td>
                                <td data-label="Estado"><?= status_badge($item['estado']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data dashboard-empty">No hay equipos dentro del período de alerta.</div>
            <?php endif; ?>
        </section>

        <section class="card dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <p class="eyebrow">Solicitudes</p>
                    <h3>Necesidades recientes</h3>
                </div>
                <a class="btn btn-light btn-small" href="<?= url('needs') ?>">Ver módulo</a>
            </div>
            <?php if ($needs): ?>
                <div class="table-wrap dashboard-table">
                    <table class="responsive-table dashboard-compact-table">
                        <caption>Solicitudes recientes registradas por colaboradores.</caption>
                        <thead><tr><th scope="col">Colaborador</th><th scope="col">Solicitud</th><th scope="col">Prioridad</th><th scope="col">Estado</th></tr></thead>
                        <tbody>
                        <?php foreach (array_slice($needs, 0, 6) as $need): ?>
                            <tr>
                                <td data-label="Colaborador"><?= e($need['colaborador_nombre']) ?></td>
                                <td data-label="Solicitud"><?= e($need['tipo_necesidad']) ?></td>
                                <td data-label="Prioridad"><?= status_badge($need['prioridad']) ?></td>
                                <td data-label="Estado"><?= status_badge($need['estado']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data dashboard-empty">No existen solicitudes para los filtros actuales.</div>
            <?php endif; ?>
        </section>

        <section class="card dashboard-panel dashboard-panel-wide">
            <div class="dashboard-panel-header">
                <div>
                    <p class="eyebrow">Custodia actual</p>
                    <h3>Equipos asignados</h3>
                </div>
                <a class="btn btn-light btn-small" href="<?= url('assignments') ?>">Ver todas</a>
            </div>
            <div class="table-wrap dashboard-table">
                <table class="responsive-table dashboard-compact-table">
                    <caption>Custodias activas más recientes.</caption>
                    <thead><tr><th scope="col">Equipo</th><th scope="col">Código</th><th scope="col">Colaborador</th><th scope="col">Departamento</th><th scope="col">IP</th><th scope="col">Desde</th></tr></thead>
                    <tbody>
                    <?php foreach (array_slice($activeAssignments, 0, 8) as $assignment): ?>
                        <tr>
                            <td data-label="Equipo"><?= e($assignment['equipo_nombre']) ?></td>
                            <td data-label="Código"><span class="small muted"><?= e($assignment['codigo_activo']) ?></span></td>
                            <td data-label="Colaborador"><?= e($assignment['colaborador_nombre']) ?></td>
                            <td data-label="Departamento"><?= e($assignment['departamento']) ?></td>
                            <td data-label="IP"><?= e($assignment['ip_asignada'] ?: '-') ?></td>
                            <td data-label="Desde"><?= e($assignment['fecha_asignacion']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$activeAssignments): ?>
                        <tr><td colspan="6" class="muted">No hay equipos asignados para los filtros actuales.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</section>

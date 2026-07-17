<?php
$reportSuffix = $queryString ? '?' . $queryString : '';
$assetCount = count($assets ?? []);
$availableCount = count($available ?? []);
$assignmentCount = count($assignments ?? []);
$repairCount = count($repairs ?? []);
$licenseTotals ??= ['total' => 0, 'used' => 0, 'available' => 0];
?>

<section class="section-header">
    <div>
        <p class="eyebrow">Exportación y consulta</p>
        <h1>Reportes CMDB</h1>
        <p>Los reportes HTML y Excel usan el mismo servicio de datos, filtros y totales.</p>
    </div>
    <div>
        <a class="btn btn-success" href="<?= url('reports/assets-excel' . $reportSuffix) ?>">Inventario Excel</a>
        <a class="btn btn-primary" href="<?= url('reports/assignments-excel' . $reportSuffix) ?>">Asignados Excel</a>
        <a class="btn btn-light" href="<?= url('reports/available-excel' . $reportSuffix) ?>">Disponibles Excel</a>
    </div>
</section>

<section class="grid grid-4 mb-md">
    <article class="stat-card stat-blue"><span>Activos filtrados</span><strong><?= e($assetCount) ?></strong><small class="muted">Inventario según filtros</small></article>
    <article class="stat-card stat-green"><span>Disponibles</span><strong><?= e($availableCount) ?></strong><small class="muted">Sin custodia activa</small></article>
    <article class="stat-card stat-orange"><span>Cupos libres</span><strong><?= e($licenseTotals['available']) ?></strong><small class="muted">Licencias disponibles</small></article>
    <article class="stat-card stat-red"><span>Reparación</span><strong><?= e($repairCount) ?></strong><small class="muted">Atención técnica</small></article>
</section>

<section class="card report-actions">
    <a class="btn btn-light" href="<?= url('reports/categories-excel' . $reportSuffix) ?>">Categorías</a>
    <a class="btn btn-light" href="<?= url('reports/assigned-categories-excel' . $reportSuffix) ?>">Asignados por categoría</a>
    <a class="btn btn-light" href="<?= url('reports/repairs-excel' . $reportSuffix) ?>">Reparación</a>
    <a class="btn btn-light" href="<?= url('reports/donations-excel' . $reportSuffix) ?>">Donaciones</a>
    <a class="btn btn-light" href="<?= url('reports/discards-excel' . $reportSuffix) ?>">Descartes</a>
    <a class="btn btn-light" href="<?= url('reports/licenses-excel' . $reportSuffix) ?>">Licencias</a>
    <a class="btn btn-light" href="<?= url('reports/license-seats-excel' . $reportSuffix) ?>">Cupos</a>
    <a class="btn btn-light" href="<?= url('reports/expirations-excel' . $reportSuffix) ?>">Vencimientos</a>
    <a class="btn btn-light" href="<?= url('reports/depreciation-excel' . $reportSuffix) ?>">Depreciación</a>
    <a class="btn btn-light" href="<?= url('reports/state-history-excel' . $reportSuffix) ?>">Historial de estados</a>
    <a class="btn btn-light" href="<?= url('reports/needs-excel') ?>">Solicitudes</a>
    <a class="btn btn-light" href="<?= url('reports/returns-excel') ?>">Devoluciones</a>
    <a class="btn btn-light" href="<?= url('reports/reviews-excel') ?>">Revisiones técnicas</a>
    <a class="btn btn-light" href="<?= url('budgets') ?>">Presupuestos</a>
</section>

<form class="card filter-bar" method="get" action="<?= url('reports') ?>">
    <div class="form-group">
        <label>Tipo</label>
        <select name="tipo">
            <option value="">Todos</option>
            <option value="HARDWARE" <?= selected($filters['tipo'] ?? '', 'HARDWARE') ?>>Hardware</option>
            <option value="SOFTWARE" <?= selected($filters['tipo'] ?? '', 'SOFTWARE') ?>>Software</option>
        </select>
    </div>
    <div class="form-group">
        <label>Estado</label>
        <select name="estado">
            <option value="">Todos</option>
            <?php foreach (\App\Core\InventoryStatus::values() as $status): ?>
                <option value="<?= e($status) ?>" <?= selected($filters['estado'] ?? '', $status) ?>><?= e(\App\Core\InventoryStatus::label($status)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Categoría</label>
        <select name="categoria_id">
            <option value="">Todas</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category['id']) ?>" <?= selected($filters['categoria_id'] ?? '', $category['id']) ?>><?= e($category['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Buscar</label>
        <input name="buscar" value="<?= e($filters['buscar'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label>Desde</label>
        <input type="date" name="fecha_desde" value="<?= e($filters['fecha_desde'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label>Hasta</label>
        <input type="date" name="fecha_hasta" value="<?= e($filters['fecha_hasta'] ?? '') ?>">
    </div>
    <label class="check-inline"><input type="checkbox" name="sin_asignar" value="1" <?= checked(!empty($filters['sin_asignar'])) ?>> Sin asignar</label>
    <label class="check-inline"><input type="checkbox" name="licencias" value="1" <?= checked(!empty($filters['licencias'])) ?>> Licencias disponibles</label>
    <button class="btn btn-primary" type="submit">Filtrar</button>
    <a class="btn btn-light" href="<?= url('reports') ?>">Limpiar</a>
</form>

<div class="grid grid-2">
    <section class="card">
        <h2>Inventario por tipo</h2>
        <div class="detail-list">
            <div><strong>Hardware</strong><?= e(count($grouped['HARDWARE'] ?? [])) ?> registros</div>
            <div><strong>Software / licencias</strong><?= e(count($grouped['SOFTWARE'] ?? [])) ?> registros</div>
            <div><strong>Costo filtrado</strong>$<?= e(number_format(array_sum(array_map(static fn (array $item): float => (float) ($item['costo'] ?? 0), $assets ?? [])), 2)) ?></div>
        </div>
    </section>
    <section class="card">
        <h2>Licencias y custodia</h2>
        <div class="detail-list">
            <div><strong>Cupos totales</strong><?= e($licenseTotals['total']) ?></div>
            <div><strong>Cupos usados</strong><?= e($licenseTotals['used']) ?></div>
            <div><strong>Custodias activas</strong><?= e($assignmentCount) ?></div>
        </div>
    </section>
</div>

<div class="grid grid-2">
    <section class="card">
        <h2>Categorías</h2>
        <div class="table-wrap table-wrap-compact">
            <table>
                <thead><tr><th scope="col">Categoría</th><th scope="col">Total</th><th scope="col">Asignados</th><th scope="col">Disponibles</th><th scope="col">Costo</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($categorySummary ?? [], 0, 8) as $row): ?>
                    <tr><td><?= e($row['categoria']) ?></td><td><?= e($row['cantidad']) ?></td><td><?= e($row['asignados']) ?></td><td><?= e($row['disponibles']) ?></td><td>$<?= e(number_format((float) $row['costo'], 2)) ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($categorySummary)): ?><tr><td colspan="5" class="muted">Sin datos para los filtros seleccionados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    <section class="card">
        <h2>Asignados por categoría</h2>
        <div class="table-wrap table-wrap-compact">
            <table>
                <thead><tr><th scope="col">Categoría</th><th scope="col">Asignados</th><th scope="col">Responsables</th><th scope="col">Costo</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($assignedByCategory ?? [], 0, 8) as $row): ?>
                    <tr><td><?= e($row['categoria']) ?></td><td><?= e($row['asignados']) ?></td><td><?= e($row['responsables_distintos']) ?></td><td>$<?= e(number_format((float) $row['costo'], 2)) ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($assignedByCategory)): ?><tr><td colspan="4" class="muted">No hay activos asignados con los filtros actuales.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<div class="grid grid-2">
    <section class="card">
        <h2>Vencimientos próximos</h2>
        <div class="table-wrap table-wrap-compact">
            <table>
                <thead><tr><th scope="col">Licencia</th><th scope="col">Proveedor</th><th scope="col">Vence</th><th scope="col">Estado</th></tr></thead>
                <tbody>
                <?php foreach (array_slice(\App\Core\ReportService::expirationRows($expirations ?? []), 0, 8) as $row): ?>
                    <tr><td><?= e($row[1]) ?></td><td><?= e($row[2]) ?></td><td><?= e($row[3]) ?></td><td><?= status_badge($row[5]) ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($expirations)): ?><tr><td colspan="4" class="muted">No hay licencias con fecha de vencimiento.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    <section class="card">
        <h2>Historial de estados</h2>
        <div class="table-wrap table-wrap-compact">
            <table>
                <thead><tr><th scope="col">Fecha</th><th scope="col">Activo</th><th scope="col">Nuevo estado</th><th scope="col">Motivo</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($stateHistory ?? [], 0, 8) as $row): ?>
                    <tr><td><?= e($row['created_at']) ?></td><td><?= e(($row['codigo_activo'] ?? '') . ' - ' . ($row['equipo_nombre'] ?? '')) ?></td><td><?= status_badge($row['estado_nuevo']) ?></td><td><?= e($row['motivo'] ?? '') ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($stateHistory)): ?><tr><td colspan="4" class="muted">Sin historial formal para mostrar.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<section class="section-header"><div><p class="eyebrow">Vista previa</p><h2>Activos registrados</h2></div></section>
<div class="table-wrap">
    <table>
        <caption><?= e($assetCount) ?> activos coinciden con los filtros; se muestran hasta 15 en esta vista previa.</caption>
        <thead><tr><th scope="col">Activo</th><th scope="col">Tipo</th><th scope="col">Categoría</th><th scope="col">Estado</th><th scope="col">Asignado a</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($assets ?? [], 0, 15) as $item): ?>
            <tr><td><?= e($item['codigo_activo'] . ' - ' . $item['nombre']) ?></td><td><?= e($item['tipo_activo']) ?></td><td><?= e($item['categoria_nombre'] ?? '-') ?></td><td><?= status_badge($item['estado']) ?></td><td><?= e($item['asignado_a'] ?? 'Sin asignar') ?></td></tr>
        <?php endforeach; ?>
        <?php if (empty($assets)): ?><tr><td colspan="5" class="muted">No hay activos para los filtros seleccionados.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

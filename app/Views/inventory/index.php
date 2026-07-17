<section class="section-header">
    <div>
        <p class="eyebrow">Inventario centralizado</p>
        <h1>Activos, equipos y licencias</h1>
        <p>Filtre por tipo, estado, activos sin asignar, licencias disponibles o texto de búsqueda.</p>
    </div>
    <?php if (\App\Core\Auth::can('inventory.manage')): ?><a class="btn btn-primary" href="<?= url('inventory/create') ?>">+ Registrar activo</a><?php endif; ?>
</section>

<section class="inventory-summary" aria-label="Resumen del inventario filtrado">
    <article>
        <span>Resultados</span>
        <strong><?= e((string) count($items)) ?></strong>
        <small>Activos según filtros actuales</small>
    </article>
    <article>
        <span>Alertas de depreciación</span>
        <strong><?= e((string) count($nearDepreciation)) ?></strong>
        <small>Dentro del rango preventivo</small>
    </article>
    <article>
        <span>Filtro activo</span>
        <strong><?= e($filters['buscar'] !== '' ? 'Texto' : (($filters['tipo'] ?: $filters['estado']) ?: 'General')) ?></strong>
        <small>Use filtros para acotar la operación</small>
    </article>
</section>

<form class="card filter-bar" method="get" action="<?= url('inventory') ?>">
    <div class="form-group"><label for="buscar">Buscar</label><input id="buscar" name="buscar" value="<?= e($filters['buscar']) ?>" placeholder="Código, nombre, serie o marca"></div>
    <div class="form-group">
        <label for="tipo">Tipo</label>
        <select id="tipo" name="tipo"><option value="">Todos</option><option value="HARDWARE" <?= selected($filters['tipo'], 'HARDWARE') ?>>Hardware</option><option value="SOFTWARE" <?= selected($filters['tipo'], 'SOFTWARE') ?>>Software</option></select>
    </div>
    <div class="form-group">
        <label for="estado">Estado</label>
        <select id="estado" name="estado">
            <option value="">Todos</option>
            <?php foreach (\App\Core\InventoryStatus::values() as $status): ?>
                <option value="<?= e($status) ?>" <?= selected($filters['estado'], $status) ?>><?= e(\App\Core\InventoryStatus::label($status)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <label class="check-row"><input type="checkbox" name="sin_asignar" value="1" <?= checked((bool) $filters['sin_asignar']) ?>> Sin asignar</label>
    <label class="check-row"><input type="checkbox" name="licencias" value="1" <?= checked((bool) $filters['licencias']) ?>> Licencias disponibles</label>
    <button class="btn btn-primary" type="submit">Filtrar</button>
    <a class="btn btn-light" href="<?= url('inventory') ?>">Limpiar</a>
</form>

<div class="table-wrap inventory-table">
    <table>
        <caption><?= e(count($items)) ?> activos encontrados con los filtros actuales.</caption>
        <thead><tr><th scope="col">Imagen</th><th scope="col">Código / Activo</th><th scope="col">Tipo / Categoría</th><th scope="col">Serie</th><th scope="col">Estado</th><th scope="col">Responsable</th><th scope="col">Depreciación</th><th scope="col">Firma</th><th scope="col">Acción</th></tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <?php if ($item['thumbnail']): ?><img class="image-thumb" src="<?= url($item['thumbnail']) ?>" alt="Activo" loading="lazy"><?php else: ?><span class="image-thumb placeholder-center">CMDB</span><?php endif; ?>
                </td>
                <td><strong><?= e($item['nombre']) ?></strong><br><span class="small muted"><?= e($item['codigo_activo']) ?> · <?= e($item['marca']) ?></span></td>
                <td><?= status_badge($item['tipo_activo']) ?><br><span class="small muted"><?= e($item['categoria_nombre'] ?? '-') ?><?= (int)$item['es_licencia'] ? ' · Licencia' : '' ?></span></td>
                <td><?= e($item['serie']) ?></td>
                <td><?= status_badge($item['estado']) ?></td>
                <td><?= e($item['asignado_a'] ?? 'Sin asignar') ?></td>
                <td><?= e($item['fecha_limite_depreciacion']) ?></td>
                <td>
                    <?php if (\App\Core\IntegritySigner::isConfigured()): ?>
                        <?= $item['integridad_valida'] ? status_badge('FIRMA VÁLIDA') : status_badge('ALERTA') ?>
                    <?php else: ?>
                        <?= status_badge('FIRMA PENDIENTE') ?>
                    <?php endif; ?>
                </td>
                <td><a class="btn btn-light btn-small" href="<?= url('inventory/detail?id=' . $item['id']) ?>">Ver</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$items): ?><tr><td colspan="9" class="muted">No se encontraron activos con los filtros seleccionados.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<section class="section-header">
    <div><p class="eyebrow">Mantenimiento preventivo</p><h2>Equipos al borde de depreciación</h2></div>
</section>
<div class="table-wrap">
    <table>
        <caption>Activos dentro del rango de alerta de 90 días.</caption>
        <thead><tr><th scope="col">Activo</th><th scope="col">Categoría</th><th scope="col">Ingreso</th><th scope="col">Fecha límite</th><th scope="col">Estado</th></tr></thead>
        <tbody>
        <?php foreach ($nearDepreciation as $item): ?>
            <tr><td><?= e($item['nombre']) ?></td><td><?= e($item['categoria_nombre']) ?></td><td><?= e($item['fecha_ingreso']) ?></td><td><?= e($item['fecha_limite_depreciacion']) ?></td><td><?= status_badge($item['estado']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$nearDepreciation): ?><tr><td colspan="5" class="muted">Sin alertas de depreciación dentro de 90 días.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

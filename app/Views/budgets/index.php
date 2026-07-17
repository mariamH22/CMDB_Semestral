<section class="section-header">
    <div>
        <p class="eyebrow">Planificación</p>
        <h1>Presupuesto anual y quinquenal</h1>
        <p>Genera un borrador desde las necesidades pendientes y en revisión.</p>
    </div>
</section>

<?php if (!$schemaReady): ?>
    <section class="card">
        <h2>Migración pendiente</h2>
        <p class="muted">Para usar presupuesto se deben crear las tablas `presupuestos` y `presupuesto_detalles` incluidas en la migración incremental del proyecto.</p>
    </section>
<?php else: ?>
    <?php if (\App\Core\Auth::can('budgets.manage')): ?>
        <?php
            $budgetField = static fn (string $key, mixed $default = ''): mixed => old_value($key, $default);
            $budgetType = (string) $budgetField('tipo', 'ANUAL');
        ?>
        <form class="card form-grid" method="post" action="<?= url('budgets/generate') ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input id="nombre" name="nombre" value="<?= e($budgetField('nombre', 'Presupuesto tecnológico ' . date('Y'))) ?>" required>
            </div>
            <div class="form-group">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo">
                    <option value="ANUAL" <?= selected($budgetType, 'ANUAL') ?>>Anual</option>
                    <option value="QUINQUENAL" <?= selected($budgetType, 'QUINQUENAL') ?>>Quinquenal</option>
                </select>
            </div>
            <div class="form-group">
                <label for="anio_inicio">Año de inicio</label>
                <input id="anio_inicio" type="number" min="2020" max="2100" name="anio_inicio" value="<?= e($budgetField('anio_inicio', date('Y'))) ?>" required>
            </div>
            <div class="form-group">
                <label for="crecimiento_anual">Crecimiento anual %</label>
                <input id="crecimiento_anual" type="number" min="0" max="100" step="0.01" name="crecimiento_anual" value="<?= e($budgetField('crecimiento_anual', '0')) ?>" required>
            </div>
            <div class="form-group">
                <label for="inflacion_anual">Inflación anual %</label>
                <input id="inflacion_anual" type="number" min="0" max="100" step="0.01" name="inflacion_anual" value="<?= e($budgetField('inflacion_anual', '0')) ?>" required>
            </div>
            <div class="form-group">
                <label for="filtro_anio">Año objetivo</label>
                <input id="filtro_anio" type="number" min="2020" max="2100" name="filtro_anio" value="<?= e($budgetField('filtro_anio')) ?>" placeholder="Todos">
            </div>
            <div class="form-group">
                <label for="filtro_categoria_id">Categoría</label>
                <select id="filtro_categoria_id" name="filtro_categoria_id">
                    <option value="0" <?= selected($budgetField('filtro_categoria_id', 0), 0) ?>>Todas</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category['id']) ?>" <?= selected($budgetField('filtro_categoria_id', 0), $category['id']) ?>><?= e($category['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filtro_tipo">Tipo</label>
                <select id="filtro_tipo" name="filtro_tipo">
                    <option value="" <?= selected($budgetField('filtro_tipo'), '') ?>>Todos</option>
                    <option value="EQUIPO" <?= selected($budgetField('filtro_tipo'), 'EQUIPO') ?>>Equipo</option>
                    <option value="SOFTWARE" <?= selected($budgetField('filtro_tipo'), 'SOFTWARE') ?>>Software</option>
                    <option value="LICENCIA" <?= selected($budgetField('filtro_tipo'), 'LICENCIA') ?>>Licencia</option>
                </select>
            </div>
            <div class="form-group">
                <label for="filtro_prioridad">Prioridad</label>
                <select id="filtro_prioridad" name="filtro_prioridad">
                    <option value="" <?= selected($budgetField('filtro_prioridad'), '') ?>>Todas</option>
                    <option value="BAJA" <?= selected($budgetField('filtro_prioridad'), 'BAJA') ?>>Baja</option>
                    <option value="MEDIA" <?= selected($budgetField('filtro_prioridad'), 'MEDIA') ?>>Media</option>
                    <option value="ALTA" <?= selected($budgetField('filtro_prioridad'), 'ALTA') ?>>Alta</option>
                </select>
            </div>
            <div class="form-group">
                <label for="filtro_estado">Estado solicitud</label>
                <select id="filtro_estado" name="filtro_estado">
                    <option value="" <?= selected($budgetField('filtro_estado'), '') ?>>Pendientes y en trámite</option>
                    <?php foreach (\App\Core\NeedStatus::values() as $state): ?>
                        <option value="<?= e($state) ?>" <?= selected($budgetField('filtro_estado'), $state) ?>><?= e(\App\Core\NeedStatus::label($state)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <button class="btn btn-success" type="submit">Generar borrador</button>
            </div>
        </form>
    <?php endif; ?>

    <section class="section-header"><div><p class="eyebrow">Borradores</p><h2>Presupuestos registrados</h2></div></section>
    <div class="table-wrap">
        <table>
            <caption><?= e(count($budgets)) ?> presupuestos registrados.</caption>
            <thead><tr><th scope="col">Nombre</th><th scope="col">Periodo</th><th scope="col">Total</th><th scope="col">Estado</th><th scope="col">Creado por</th><th scope="col">Acción</th></tr></thead>
            <tbody>
            <?php foreach ($budgets as $budget): ?>
                <tr>
                    <td><?= e($budget['nombre']) ?><br><span class="small muted"><?= e($budget['tipo']) ?></span></td>
                    <td><?= e($budget['anio_inicio']) ?> - <?= e($budget['anio_fin']) ?></td>
                    <td>$<?= e(number_format((float) $budget['total_estimado'], 2)) ?></td>
                    <td><?= status_badge($budget['estado']) ?></td>
                    <td><?= e($budget['nombre_usuario'] ?? '-') ?></td>
                    <td>
                        <a class="btn btn-light btn-small" href="<?= url('budgets?id=' . $budget['id']) ?>">Ver detalle</a>
                        <a class="btn btn-primary btn-small" href="<?= url('budgets/excel?id=' . $budget['id']) ?>">Excel</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$budgets): ?><tr><td colspan="6" class="muted">No hay presupuestos registrados.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($selectedBudgetId > 0): ?>
        <?php $summary ??= ['base' => 0, 'total' => 0, 'without_cost_count' => 0, 'by_year' => [], 'without_cost' => []]; ?>
        <section class="section-header"><div><p class="eyebrow">Resumen</p><h2>Supuestos y proyección</h2></div></section>
        <div class="grid grid-3">
            <article class="card">
                <p class="eyebrow">Base</p>
                <h3>$<?= e(number_format((float) $summary['base'], 2)) ?></h3>
            </article>
            <article class="card">
                <p class="eyebrow">Total proyectado</p>
                <h3>$<?= e(number_format((float) $summary['total'], 2)) ?></h3>
            </article>
            <article class="card">
                <p class="eyebrow">Sin costo</p>
                <h3><?= e((string) $summary['without_cost_count']) ?></h3>
            </article>
        </div>
        <?php if (!empty($selectedBudget['supuestos'] ?? '')): ?>
            <section class="card section-spaced">
                <p class="muted"><?= e($selectedBudget['supuestos']) ?></p>
            </section>
        <?php endif; ?>
        <div class="table-wrap section-spaced">
            <table>
                <caption>Proyección por año.</caption>
                <thead><tr><th scope="col">Año</th><th scope="col">Total</th><th scope="col">Partidas</th></tr></thead>
                <tbody>
                <?php foreach (($summary['by_year'] ?? []) as $row): ?>
                    <tr><td><?= e($row['label']) ?></td><td>$<?= e(number_format((float) $row['total'], 2)) ?></td><td><?= e((string) $row['count']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (empty($summary['by_year'])): ?><tr><td colspan="3" class="muted">Sin partidas con costo.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($summary['without_cost'])): ?>
            <div class="table-wrap section-spaced">
                <table>
                    <caption>Solicitudes separadas por falta de costo.</caption>
                    <thead><tr><th scope="col">Año</th><th scope="col">Tipo</th><th scope="col">Categoría</th><th scope="col">Descripción</th><th scope="col">Motivo</th></tr></thead>
                    <tbody>
                    <?php foreach ($summary['without_cost'] as $row): ?>
                        <tr>
                            <td><?= e((string) ($row['anio'] ?? '-')) ?></td>
                            <td><?= e($row['tipo_necesidad'] ?? '-') ?></td>
                            <td><?= e($row['categoria_nombre'] ?? '-') ?></td>
                            <td><?= e($row['descripcion'] ?? '-') ?></td>
                            <td><?= e($row['motivo_sin_costo'] ?? 'Sin costo estimado') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <section class="section-header"><div><p class="eyebrow">Detalle</p><h2>Partidas del presupuesto</h2></div></section>
        <div class="table-wrap">
            <table>
                <caption><?= e(count($details)) ?> partidas del presupuesto seleccionado.</caption>
                <thead><tr><th scope="col">Año</th><th scope="col">Tipo</th><th scope="col">Categoría</th><th scope="col">Prioridad</th><th scope="col">Estado</th><th scope="col">Descripción</th><th scope="col">Cantidad</th><th scope="col">Costo unitario</th><th scope="col">Subtotal</th></tr></thead>
                <tbody>
                <?php foreach ($details as $detail): ?>
                    <?php $hasCost = (int) ($detail['tiene_costo'] ?? 1) === 1; ?>
                    <tr>
                        <td><?= e($detail['anio']) ?></td>
                        <td><?= e($detail['tipo_necesidad']) ?></td>
                        <td><?= e($detail['categoria_nombre'] ?? '-') ?></td>
                        <td><?= e($detail['prioridad'] ?? '-') ?></td>
                        <td><?= e($detail['estado_solicitud'] ?? '-') ?></td>
                        <td><?= e($detail['descripcion']) ?></td>
                        <td><?= e($detail['cantidad']) ?></td>
                        <td><?= $hasCost ? '$' . e(number_format((float) $detail['costo_unitario'], 2)) : '<span class="muted">Sin costo</span>' ?></td>
                        <td><?= $hasCost ? '$' . e(number_format((float) $detail['subtotal'], 2)) : '<span class="muted">Separado</span>' ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$details): ?><tr><td colspan="9" class="muted">Este presupuesto no tiene partidas.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endif; ?>

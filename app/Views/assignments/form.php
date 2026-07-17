<?php
$field = static fn (string $key, mixed $default = ''): mixed => old_value($key, $default);
?>
<section class="section-header">
    <div><p class="eyebrow">Custodia</p><h1>Asignar activo a colaborador</h1></div>
    <a class="btn btn-light" href="<?= url('assignments') ?>">Volver</a>
</section>

<form class="card form-card" method="post" action="<?= url('assignments/store') ?>">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="form-group">
            <label>Activo disponible</label>
            <select name="inventario_id" required>
                <option value="">Seleccione...</option>
                <?php foreach ($availableItems as $item): ?>
                    <option value="<?= e($item['id']) ?>" <?= selected($field('inventario_id'), $item['id']) ?>><?= e($item['codigo_activo'] . ' - ' . $item['nombre'] . ' (' . $item['serie'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!$availableItems): ?><span class="small muted">No hay activos disponibles. Registre o devuelva un equipo primero.</span><?php endif; ?>
        </div>
        <div class="form-group">
            <label>Colaborador responsable</label>
            <select name="colaborador_id" required>
                <option value="">Seleccione...</option>
                <?php foreach ($collaborators as $collaborator): ?>
                    <option value="<?= e($collaborator['id']) ?>" <?= selected($field('colaborador_id'), $collaborator['id']) ?>><?= e($collaborator['nombres'] . ' ' . $collaborator['apellidos'] . ' · ' . $collaborator['departamento']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Fecha de asignación</label><input type="date" name="fecha_asignacion" value="<?= e($field('fecha_asignacion', date('Y-m-d'))) ?>" required></div>
        <div class="form-group"><label>IP asignada (si aplica)</label><input name="ip_asignada" value="<?= e($field('ip_asignada')) ?>" placeholder="Ej. 192.168.1.35"></div>
        <div class="form-group full"><label>Observaciones</label><textarea name="observaciones" placeholder="Condición del activo, accesorios entregados, etc."><?= e($field('observaciones')) ?></textarea></div>
    </div>
    <div class="button-row"><button class="btn btn-success" type="submit">Registrar asignación</button></div>
</form>

<?php
$editing = $collaborator !== null;
$field = static fn (string $key, mixed $default = ''): mixed => old_value($key, $collaborator[$key] ?? $default);
?>
<section class="section-header">
    <div><p class="eyebrow">Responsables de activos</p><h1><?= e($editing ? 'Editar colaborador' : 'Registrar colaborador') ?></h1></div>
    <a class="btn btn-light" href="<?= url('collaborators') ?>">Volver</a>
</section>

<form class="card form-card" method="post" enctype="multipart/form-data" action="<?= url($editing ? 'collaborators/update' : 'collaborators/store') ?>">
    <?= csrf_field() ?>
    <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($collaborator['id']) ?>"><?php endif; ?>
    <div class="form-grid">
        <div class="form-group"><label>Nombres</label><input name="nombres" value="<?= e($field('nombres')) ?>" required></div>
        <div class="form-group"><label>Apellidos</label><input name="apellidos" value="<?= e($field('apellidos')) ?>" required></div>
        <div class="form-group"><label>Identificación única</label><input name="identificacion" value="<?= e($field('identificacion')) ?>" required></div>
        <div class="form-group"><label>Departamento</label><input name="departamento" value="<?= e($field('departamento')) ?>" required></div>
        <div class="form-group"><label>Ubicación</label><input name="ubicacion" value="<?= e($field('ubicacion')) ?>" placeholder="Edificio 303 / Casa 257"></div>
        <?php if ($editing): ?><div class="form-group"><label>Motivo de cambio de ubicación</label><input name="motivo_ubicacion" value="<?= e(old_value('motivo_ubicacion')) ?>" placeholder="Traslado, teletrabajo, cambio de sede"></div><?php endif; ?>
        <div class="form-group"><label>Dirección</label><input name="direccion" value="<?= e($field('direccion')) ?>"></div>
        <div class="form-group"><label>Teléfono</label><input name="telefono" value="<?= e($field('telefono')) ?>"></div>
        <div class="form-group"><label>Correo</label><input type="email" name="email" value="<?= e($field('email')) ?>" required></div>
        <div class="form-group"><label>Foto (JPG, PNG o WEBP, máximo 2 MB)</label><input type="file" name="foto" accept=".jpg,.jpeg,.png,.webp"></div>
        <div class="form-group">
            <?php if ($editing && $collaborator['foto']): ?><img class="photo-circle" src="<?= url($collaborator['foto']) ?>" alt="Foto actual" loading="lazy"><?php endif; ?>
        </div>
        <div class="form-group full"><div class="check-row"><label><input type="checkbox" name="activo" value="1" <?= old_checked('activo', $collaborator['activo'] ?? 1) ?>> Colaborador activo</label></div></div>
    </div>
    <div class="button-row"><button class="btn btn-success" type="submit">Guardar colaborador</button></div>
</form>

<?php if ($editing): ?>
    <!-- Historial de ubicación solo se muestra cuando existe histórico por colaborador en la base actual. -->
    <section class="card section-spaced">
        <h2>Historial de ubicación</h2>
        <?php if (empty($locationHistoryReady)): ?>
            <div class="no-data">La migración de historial de ubicación aún no está aplicada.</div>
        <?php elseif ($locationHistory): ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th scope="col">Fecha</th><th scope="col">Tipo</th><th scope="col">Ubicación anterior</th><th scope="col">Ubicación nueva</th><th scope="col">Inicio</th><th scope="col">Fin</th><th scope="col">Motivo</th><th scope="col">Responsable</th></tr></thead>
                    <tbody>
                    <?php foreach ($locationHistory as $row): ?>
                        <tr>
                            <td><?= e($row['created_at']) ?></td>
                            <td><?= e($row['tipo'] ?: '-') ?></td>
                            <td><?= e($row['ubicacion_anterior'] ?: '-') ?></td>
                            <td><?= e($row['ubicacion_nueva'] ?: '-') ?></td>
                            <td><?= e($row['fecha_inicio'] ?? '-') ?></td>
                            <td><?= e($row['fecha_fin'] ?? '-') ?></td>
                            <td><?= e($row['motivo'] ?? '-') ?></td>
                            <td><?= e($row['nombre_usuario'] ?: 'Sistema') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-data">Aún no hay cambios de ubicación registrados.</div>
        <?php endif; ?>
    </section>
<?php endif; ?>

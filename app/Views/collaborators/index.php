<section class="section-header">
    <div>
        <p class="eyebrow">Responsables del inventario</p>
        <h1>Colaboradores</h1>
        <p>Personas vinculadas a equipos, licencias, departamento y ubicación.</p>
    </div>
    <?php if (\App\Core\Auth::can('collaborators.manage')): ?><a class="btn btn-primary" href="<?= url('collaborators/create') ?>">+ Nuevo colaborador</a><?php endif; ?>
</section>

<div class="table-wrap">
    <table>
        <caption><?= e(count($collaborators)) ?> colaboradores visibles.</caption>
        <thead><tr><th scope="col">Foto</th><th scope="col">Colaborador</th><th scope="col">Identificación</th><th scope="col">Departamento</th><th scope="col">Ubicación</th><th scope="col">Contacto</th><th scope="col">Estado</th><th scope="col">Acción</th></tr></thead>
        <tbody>
        <?php foreach ($collaborators as $collaborator): ?>
            <tr>
                <td>
                    <?php if ($collaborator['foto']): ?><img class="photo-circle" src="<?= url($collaborator['foto']) ?>" alt="Foto" loading="lazy"><?php else: ?><span class="photo-circle placeholder-center">?</span><?php endif; ?>
                </td>
                <td><strong><?= e($collaborator['nombres'] . ' ' . $collaborator['apellidos']) ?></strong><br><span class="small muted"><?= e($collaborator['email']) ?></span></td>
                <td><?= e($collaborator['identificacion']) ?></td>
                <td><?= e($collaborator['departamento']) ?></td>
                <td><?= e($collaborator['ubicacion']) ?></td>
                <td><?= e($collaborator['telefono'] ?: '-') ?></td>
                <td><?= (int) $collaborator['activo'] ? status_badge('ACTIVO') : status_badge('BAJA') ?></td>
                <td><?php if (\App\Core\Auth::can('collaborators.manage')): ?><a class="btn btn-light btn-small" href="<?= url('collaborators/edit?id=' . $collaborator['id']) ?>">Editar</a><?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

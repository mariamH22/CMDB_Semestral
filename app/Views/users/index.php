<section class="section-header">
    <div>
        <p class="eyebrow">Seguridad y administración</p>
        <h1>Usuarios del sistema</h1>
        <p>Las cuentas no se eliminan: se dan de baja con el campo Activo.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('users/create') ?>">+ Nuevo usuario</a>
</section>

<div class="table-wrap">
    <table>
        <caption><?= e(count($users)) ?> cuentas registradas en el sistema.</caption>
        <thead>
            <tr><th scope="col">Usuario</th><th scope="col">Correo</th><th scope="col">Rol</th><th scope="col">Vinculado a</th><th scope="col">Activo</th><th scope="col">Cuenta</th><th scope="col">Intentos</th><th scope="col">Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= e($user['nombre_usuario']) ?></td>
                <td><?= e($user['email']) ?></td>
                <td><?= status_badge($user['rol']) ?></td>
                <td><?= e(trim($user['colaborador_nombre']) ?: '-') ?></td>
                <td><?= (int) $user['activo'] ? status_badge('ACTIVO') : status_badge('BAJA') ?></td>
                <td><?= status_badge($user['estado_cuenta']) ?></td>
                <td><?= e($user['intentos_fallidos']) ?></td>
                <td>
                    <a class="btn btn-light btn-small" href="<?= url('users/edit?id=' . $user['id']) ?>">Editar</a>
                    <?php if ((int) $user['id'] !== (int) \App\Core\Auth::id()): ?>
                        <form method="post" action="<?= url('users/toggle-active') ?>" class="inline-action">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e($user['id']) ?>">
                            <?php if ((int) $user['activo']): ?>
                                <button class="btn btn-danger btn-small" data-confirm="¿Inactivar este usuario? No podrá iniciar sesión mientras esté inactivo." type="submit">Inactivar</button>
                            <?php else: ?>
                                <input type="hidden" name="activo" value="1">
                                <button class="btn btn-success btn-small" type="submit">Reactivar</button>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                    <?php if ($user['estado_cuenta'] === 'BLOQUEADO'): ?>
                        <form method="post" action="<?= url('users/unlock') ?>" class="inline-action">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e($user['id']) ?>">
                            <button class="btn btn-warning btn-small" type="submit">Desbloquear</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

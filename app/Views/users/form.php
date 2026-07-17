<?php
$editing = is_array($user ?? null);
$field = static fn (string $key, mixed $default = ''): mixed => old_value($key, $user[$key] ?? $default);
$roleValue = (string) $field('rol', 'OPERADOR');
$collaboratorValue = (int) $field('colaborador_id', 0);
?>
<section class="section-header">
    <div>
        <p class="eyebrow">Módulo de usuarios</p>
        <h1><?= e($editing ? 'Editar usuario' : 'Registrar usuario') ?></h1>
    </div>
    <a class="btn btn-light" href="<?= url('users') ?>">Volver</a>
</section>

<form class="card form-card" method="post" action="<?= url($editing ? 'users/update' : 'users/store') ?>">
    <?= csrf_field() ?>
    <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($user['id']) ?>"><?php endif; ?>

    <div class="form-grid">
        <div class="form-group">
            <label>Nombre de usuario</label>
            <input name="nombre_usuario" value="<?= e($field('nombre_usuario')) ?>" required>
        </div>
        <div class="form-group">
            <label>Correo electrónico</label>
            <input type="email" name="email" value="<?= e($field('email')) ?>" required>
        </div>
        <div class="form-group">
            <label>Rol</label>
            <select name="rol">
                <option value="ADMIN" <?= selected($roleValue, 'ADMIN') ?>>Administrador</option>
                <option value="OPERADOR" <?= selected($roleValue, 'OPERADOR') ?>>Operador</option>
                <option value="COLABORADOR" <?= selected($roleValue, 'COLABORADOR') ?>>Colaborador</option>
            </select>
        </div>
        <div class="form-group">
            <label>Vincular con colaborador (opcional)</label>
            <select name="colaborador_id">
                <option value="0">Sin vínculo</option>
                <?php if ($editing && $user['colaborador_id']): ?>
                    <option value="<?= e($user['colaborador_id']) ?>" <?= selected($collaboratorValue, $user['colaborador_id']) ?>>Colaborador vinculado actual</option>
                <?php endif; ?>
                <?php foreach ($collaborators as $collaborator): ?>
                    <option value="<?= e($collaborator['id']) ?>" <?= selected($collaboratorValue, $collaborator['id']) ?>>
                        <?= e($collaborator['nombres'] . ' ' . $collaborator['apellidos']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label><?= $editing ? 'Nueva contraseña (opcional)' : 'Contraseña' ?></label>
            <input type="password" name="password" minlength="8" maxlength="64" autocomplete="new-password" <?= $editing ? '' : 'required' ?>>
            <span class="small muted">8 a 64 caracteres, con mayúscula, minúscula, número y símbolo.</span>
        </div>
        <div class="form-group">
            <label><?= $editing ? 'Confirmar nueva contraseña' : 'Confirmar contraseña' ?></label>
            <input type="password" name="password_confirmation" minlength="8" maxlength="64" autocomplete="new-password" <?= $editing ? '' : 'required' ?>>
        </div>
        <div class="form-group">
            <label>Estado de la cuenta</label>
            <div class="check-row">
                <label><input type="checkbox" name="activo" value="1" <?= old_checked('activo', $user['activo'] ?? 1) ?>> Usuario activo</label>
            </div>
        </div>
    </div>

    <div class="button-row">
        <button class="btn btn-success" type="submit"><?= $editing ? 'Guardar cambios' : 'Registrar usuario' ?></button>
    </div>
</form>

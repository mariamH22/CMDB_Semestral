<section class="login-shell">
    <div class="card auth-card auth-card-wide">
        <p class="eyebrow">Portal del colaborador</p>
        <h1>Registro de colaborador</h1>
        <p class="muted">El registro crea un colaborador y una cuenta con rol COLABORADOR para consultar equipos bajo su responsabilidad.</p>

        <form method="post" action="<?= url('register') ?>" class="form-grid">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Nombres</label>
                <input name="nombres" value="<?= old('nombres') ?>" required>
            </div>
            <div class="form-group">
                <label>Apellidos</label>
                <input name="apellidos" value="<?= old('apellidos') ?>" required>
            </div>
            <div class="form-group">
                <label>Identificación única</label>
                <input name="identificacion" value="<?= old('identificacion') ?>" required>
            </div>
            <div class="form-group">
                <label>Departamento</label>
                <input name="departamento" value="<?= old('departamento') ?>" required placeholder="Ej. Tecnología">
            </div>
            <div class="form-group">
                <label>Ubicación</label>
                <input name="ubicacion" value="<?= old('ubicacion') ?>" required placeholder="Ej. Edificio 303 / Casa 257">
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input name="direccion" value="<?= old('direccion') ?>">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input name="telefono" value="<?= old('telefono') ?>">
            </div>
            <div class="form-group">
                <label>Correo</label>
                <input type="email" name="email" value="<?= old('email') ?>" required>
            </div>
            <div class="form-group">
                <label>Nombre de usuario</label>
                <input name="nombre_usuario" value="<?= old('nombre_usuario') ?>" required>
            </div>
            <div class="form-group">
                <label>Contraseña (8 a 64 caracteres)</label>
                <input type="password" name="password" minlength="8" maxlength="64" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <label>Confirmar contraseña</label>
                <input type="password" name="password_confirmation" minlength="8" maxlength="64" required autocomplete="new-password">
            </div>
            <div class="form-group full">
                <button class="btn btn-success" type="submit">Registrar cuenta</button>
            </div>
        </form>

        <p class="small muted">La contraseña debe contener mayúscula, minúscula, número y carácter especial.</p>
    </div>
</section>

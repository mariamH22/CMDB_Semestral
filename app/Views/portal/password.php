<section class="login-shell">
    <div class="card auth-card">
        <p class="eyebrow">Portal del Colaborador</p>
        <h1>Cambiar contraseña</h1>
        <p class="muted">La nueva contraseña debe tener entre 8 y 64 caracteres, mayúscula, minúscula, número y símbolo.</p>
        <form method="post" action="<?= url('portal/password') ?>" class="form-grid">
            <?= csrf_field() ?>
            <div class="form-group full"><label>Contraseña actual</label><input type="password" name="password_actual" required autocomplete="current-password"></div>
            <div class="form-group full"><label>Nueva contraseña</label><input type="password" name="password_nueva" minlength="8" maxlength="64" required autocomplete="new-password"></div>
            <div class="form-group full"><label>Confirmación</label><input type="password" name="password_confirmacion" minlength="8" maxlength="64" required autocomplete="new-password"></div>
            <div class="form-group full"><button class="btn btn-success" type="submit">Actualizar contraseña</button></div>
        </form>
    </div>
</section>

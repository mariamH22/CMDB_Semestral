<section class="login-shell">
    <div class="card auth-card">
        <p class="eyebrow">Nueva credencial</p>
        <h1>Restablecer contraseña</h1>
        <p class="muted">Use una contraseña de 8 a 64 caracteres con mayúscula, minúscula, número y carácter especial.</p>
        <form method="post" action="<?= url('reset-password') ?>" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
            <div class="form-group full">
                <label for="password">Nueva contraseña</label>
                <input id="password" type="password" name="password" minlength="8" maxlength="64" required autocomplete="new-password">
            </div>
            <div class="form-group full">
                <label for="password_confirmation">Confirmar contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" minlength="8" maxlength="64" required autocomplete="new-password">
            </div>
            <div class="form-group full">
                <button class="btn btn-success" type="submit">Actualizar contraseña</button>
            </div>
        </form>
    </div>
</section>

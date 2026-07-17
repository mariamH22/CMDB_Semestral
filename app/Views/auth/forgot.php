<section class="login-shell">
    <div class="card auth-card">
        <p class="eyebrow">Recuperación</p>
        <h1>Recuperar contraseña</h1>
        <p class="muted">Si el correo existe, se registrará una solicitud de recuperación para ser atendida de forma segura.</p>

        <form method="post" action="<?= url('forgot-password') ?>" class="form-grid">
            <?= csrf_field() ?>
            <div class="form-group full">
                <label for="email">Correo registrado</label>
                <input id="email" type="email" name="email" required autocomplete="email">
                <span class="field-help">Por seguridad, la respuesta no confirma si el correo existe.</span>
            </div>
            <div class="form-group full">
                <button class="btn btn-primary" type="submit">Generar enlace</button>
            </div>
        </form>
        <?php unset($_SESSION['reset_demo_link']); ?>
    </div>
</section>

<section class="login-shell">
    <aside class="login-identity" aria-label="Contexto del sistema">
        <p class="eyebrow">Mesa operativa CMDB</p>
        <h2>Acceso a la mesa de inventario tecnológico</h2>
        <p>Revise activos, custodias, solicitudes, licencias y reportes desde un entorno administrativo con roles y bitácora.</p>
        <ul>
            <li>Inventario y depreciación.</li>
            <li>Asignaciones y devoluciones.</li>
            <li>Solicitudes y reportes.</li>
        </ul>
    </aside>
    <div class="card auth-card">
        <p class="eyebrow">Acceso seguro</p>
        <h1>Iniciar sesión</h1>
        <p class="muted">Ingrese con su correo o nombre de usuario. La cuenta se bloquea al tercer intento fallido.</p>

        <form method="post" action="<?= url('login') ?>" class="form-grid">
            <?= csrf_field() ?>
            <div class="form-group full">
                <label for="identificador">Correo o nombre de usuario</label>
                <input id="identificador" name="identificador" value="<?= old('identificador') ?>" required autocomplete="username" inputmode="email">
            </div>
            <div class="form-group full">
                <label for="password">Contraseña</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
            </div>
            <div class="form-group full">
                <button class="btn btn-primary" type="submit">Iniciar sesión</button>
            </div>
        </form>

        <p><a href="<?= url('forgot-password') ?>">¿Olvidó su contraseña?</a></p>
    </div>
</section>

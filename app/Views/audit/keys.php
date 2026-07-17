<section class="section-header">
    <div>
        <p class="eyebrow">No repudio técnico</p>
        <h1>Llaves RSA</h1>
        <p>Gestión local de llaves públicas, fingerprints, rotación, revocación y verificación de firmas.</p>
    </div>
    <a class="btn btn-light" href="<?= url('audit') ?>">Volver a bitácora</a>
</section>

<?php if (empty($keySchemaReady) || empty($signatureSchemaReady)): ?>
    <section class="card">
        <h2>Migración pendiente</h2>
        <p class="muted">Aplique las migraciones incrementales de RSA antes de generar o verificar llaves.</p>
    </section>
<?php elseif (empty($lifecycleReady)): ?>
    <section class="card">
        <h2>Ciclo de vida pendiente</h2>
        <p class="muted">Falta aplicar la migración `2026_07_13_0005_firma_rsa_ciclo_vida.sql` para activar rotación, revocación y verificación formal.</p>
    </section>
<?php elseif (empty($keyManagementConfigured)): ?>
    <section class="card">
        <h2>Almacén seguro pendiente</h2>
        <p class="muted">Configure `security.key_store_path` y `security.key_encryption_key` fuera del repositorio. No se generarán llaves privadas sin cifrado.</p>
    </section>
<?php else: ?>
    <section class="grid grid-2">
        <form class="card form-grid" method="post" action="<?= url('audit/keys/generate') ?>">
            <?= csrf_field() ?>
            <h2>Generar llave</h2>
            <div class="form-group">
                <label>Usuario</label>
                <select name="usuario_id" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= e($user['id']) ?>"><?= e($user['nombre_usuario'] . ' · ' . $user['rol']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" maxlength="120" value="Llave RSA de usuario">
            </div>
            <div class="form-group">
                <label>Contraseña actual</label>
                <input type="password" name="password_actual" autocomplete="current-password" required>
            </div>
            <button class="btn btn-primary" type="submit">Generar</button>
        </form>

        <form class="card form-grid" method="post" action="<?= url('audit/keys/rotate') ?>">
            <?= csrf_field() ?>
            <h2>Rotar llave</h2>
            <div class="form-group">
                <label>Usuario</label>
                <select name="usuario_id" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= e($user['id']) ?>"><?= e($user['nombre_usuario'] . ' · ' . $user['rol']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Nombre nueva llave</label>
                <input type="text" name="nombre" maxlength="120" value="Llave RSA rotada">
            </div>
            <div class="form-group">
                <label>Contraseña actual</label>
                <input type="password" name="password_actual" autocomplete="current-password" required>
            </div>
            <button class="btn btn-warning" data-confirm="¿Rotar la llave activa de este usuario?" type="submit">Rotar</button>
        </form>
    </section>
<?php endif; ?>

<section class="section-header">
    <div>
        <p class="eyebrow">Inventario criptográfico</p>
        <h2>Llaves registradas</h2>
    </div>
</section>

<div class="table-wrap">
    <table>
        <caption><?= e(count($keys)) ?> llaves RSA registradas.</caption>
        <thead>
            <tr><th scope="col">Usuario</th><th scope="col">Nombre</th><th scope="col">Estado</th><th scope="col">Algoritmo</th><th scope="col">Fingerprint</th><th scope="col">Creada</th><th scope="col">Revocación</th><th scope="col">Acción</th></tr>
        </thead>
        <tbody>
        <?php foreach ($keys as $key): ?>
            <?php
                $statusClass = match ($key['estado']) {
                    'ACTIVA' => 'success',
                    'REVOCADA' => 'danger',
                    'REEMPLAZADA', 'ROTADA' => 'warning',
                    default => 'neutral',
                };
            ?>
            <tr>
                <td><?= e($key['nombre_usuario'] ?? 'Sistema') ?></td>
                <td><?= e($key['nombre']) ?></td>
                <td><span class="badge badge-<?= e($statusClass) ?>"><?= e($key['estado']) ?></span></td>
                <td><?= e($key['algoritmo'] ?? 'RSA-SHA256') ?><br><span class="small muted"><?= e($key['bits'] ?? '2048+') ?> bits</span></td>
                <td><code><?= e(substr((string) $key['fingerprint'], 0, 20)) ?>...</code></td>
                <td><?= e($key['created_at']) ?></td>
                <td>
                    <?= e($key['revoked_at'] ?? '-') ?>
                    <?php if (!empty($key['revocation_reason'])): ?><br><span class="small muted"><?= e($key['revocation_reason']) ?></span><?php endif; ?>
                </td>
                <td>
                    <?php if (($key['estado'] ?? '') !== 'REVOCADA' && !empty($lifecycleReady)): ?>
                        <form method="post" action="<?= url('audit/keys/revoke') ?>" class="compact-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="llave_id" value="<?= e($key['id']) ?>">
                            <input type="text" name="motivo" maxlength="255" placeholder="Motivo" required>
                            <input type="password" name="password_actual" autocomplete="current-password" placeholder="Contraseña" required>
                            <button class="btn btn-danger btn-small" data-confirm="¿Revocar esta llave RSA?" type="submit">Revocar</button>
                        </form>
                    <?php else: ?>
                        <span class="muted">Sin acciones</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$keys): ?><tr><td colspan="8" class="muted">No hay llaves RSA registradas.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<section class="section-header">
    <div>
        <p class="eyebrow">Validación</p>
        <h2>Firmas recientes</h2>
    </div>
</section>

<div class="table-wrap">
    <table>
        <caption>Verificación manual de firmas RSA almacenadas.</caption>
        <thead>
            <tr><th scope="col">Fecha</th><th scope="col">Usuario</th><th scope="col">Acción</th><th scope="col">Entidad</th><th scope="col">Hash</th><th scope="col">Resultado</th><th scope="col">Acción</th></tr>
        </thead>
        <tbody>
        <?php foreach ($signatures as $signature): ?>
            <?php
                $verificationClass = match ($signature['verificacion']) {
                    'VALIDA' => 'success',
                    'INVALIDA', 'LLAVE_REVOCADA', 'ERROR' => 'danger',
                    default => 'warning',
                };
            ?>
            <tr>
                <td><?= e($signature['created_at']) ?></td>
                <td><?= e($signature['nombre_usuario'] ?? 'Sistema') ?></td>
                <td><?= e($signature['modulo'] . ' · ' . $signature['accion']) ?></td>
                <td><?= e($signature['entidad'] . ($signature['entidad_id'] ? ' #' . $signature['entidad_id'] : '')) ?></td>
                <td><code><?= e(substr((string) $signature['payload_hash'], 0, 18)) ?>...</code></td>
                <td><span class="badge badge-<?= e($verificationClass) ?>"><?= e($signature['verificacion']) ?></span></td>
                <td>
                    <form method="post" action="<?= url('audit/signatures/verify') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="firma_id" value="<?= e($signature['id']) ?>">
                        <button class="btn btn-light btn-small" type="submit">Verificar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$signatures): ?><tr><td colspan="7" class="muted">No hay firmas RSA registradas.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

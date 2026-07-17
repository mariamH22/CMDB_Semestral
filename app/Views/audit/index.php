<section class="section-header">
    <div>
        <p class="eyebrow">Trazabilidad y seguridad</p>
        <h1>Bitácora de auditoría</h1>
        <p>Registro de operaciones relevantes: autenticación, altas, cambios, asignaciones y solicitudes.</p>
    </div>
    <div class="report-actions">
        <a class="btn btn-light" href="<?= url('audit/trail') ?>">Verificar cadena</a>
        <a class="btn btn-primary" href="<?= url('audit/keys') ?>">Gestionar llaves RSA</a>
    </div>
</section>

<section class="card">
    <h2>Trazabilidad con detección criptográfica de alteraciones</h2>
    <?php if (empty($trailSummary['schemaReady'])): ?>
        <p class="muted">La migración del Audit Trail está pendiente. La bitácora funciona en modo compatible sin cadena criptográfica.</p>
    <?php else: ?>
        <p class="muted">
            <?= e($trailSummary['valid']) ?> de <?= e($trailSummary['total']) ?> eventos verificados correctamente.
            <?= e($trailSummary['invalid']) ?> con alerta y <?= e($trailSummary['notVerifiable']) ?> no verificables.
        </p>
    <?php endif; ?>
</section>

<section class="section-header">
    <div>
        <p class="eyebrow">No repudio técnico</p>
        <h2>Firmas digitales RSA</h2>
        <p>Verificación administrativa de acciones sensibles firmadas con una llave activa fuera del repositorio.</p>
    </div>
</section>

<?php if (empty($signatureSchemaReady)): ?>
    <section class="card">
        <h2>Migración pendiente</h2>
        <p class="muted">Para verificar firmas RSA se requieren las tablas `llaves_rsa` y `firmas_digitales` incluidas en la migración incremental.</p>
    </section>
<?php elseif (empty($keyManagementConfigured)): ?>
    <section class="card">
        <h2>Almacén de llaves pendiente</h2>
        <p class="muted">Las firmas RSA requieren configurar `security.key_store_path` y `security.key_encryption_key` fuera del repositorio. No se generarán llaves privadas desprotegidas.</p>
    </section>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <caption>Últimas firmas digitales registradas para acciones sensibles.</caption>
            <thead><tr><th scope="col">Fecha</th><th scope="col">Usuario</th><th scope="col">Módulo</th><th scope="col">Acción</th><th scope="col">Entidad</th><th scope="col">Hash</th><th scope="col">Llave</th><th scope="col">Verificación</th></tr></thead>
            <tbody>
            <?php foreach ($signatures as $signature): ?>
                <?php
                    $verificationClass = match ($signature['verificacion']) {
                        'VALIDA' => 'success',
                        'INVALIDA' => 'danger',
                        default => 'warning',
                    };
                    $verificationText = match ($signature['verificacion']) {
                        'VALIDA' => 'Válida',
                        'INVALIDA' => 'Inválida',
                        default => 'No verificable',
                    };
                ?>
                <tr>
                    <td><?= e($signature['created_at']) ?></td>
                    <td><?= e($signature['nombre_usuario'] ?? 'Sistema') ?></td>
                    <td><?= e($signature['modulo']) ?></td>
                    <td><?= e($signature['accion']) ?></td>
                    <td><?= e($signature['entidad'] . ($signature['entidad_id'] ? ' #' . $signature['entidad_id'] : '')) ?></td>
                    <td><code><?= e(substr((string) $signature['payload_hash'], 0, 16)) ?>...</code></td>
                    <td><?= e($signature['llave_nombre']) ?><br><span class="small muted"><?= e($signature['llave_estado']) ?> · <?= e(substr((string) $signature['fingerprint'], 0, 12)) ?>...</span></td>
                    <td><span class="badge badge-<?= e($verificationClass) ?>"><?= e($verificationText) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$signatures): ?><tr><td colspan="8" class="muted">No hay firmas RSA registradas.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<section class="section-header">
    <div>
        <p class="eyebrow">Eventos</p>
        <h2>Bitácora del sistema</h2>
    </div>
</section>

<div class="table-wrap">
    <table>
        <caption>Últimos eventos de auditoría disponibles.</caption>
        <thead><tr><th scope="col">Fecha</th><th scope="col">Usuario</th><th scope="col">Módulo</th><th scope="col">Acción</th><th scope="col">Descripción</th><th scope="col">IP</th><th scope="col">Nivel</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= e($log['created_at']) ?></td>
                <td><?= e($log['nombre_usuario'] ?? 'Sistema') ?></td>
                <td><?= e($log['modulo']) ?></td>
                <td><?= e($log['accion']) ?></td>
                <td><?= e($log['descripcion']) ?></td>
                <td><?= e($log['ip']) ?></td>
                <td><?= status_badge($log['nivel']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$logs): ?><tr><td colspan="7" class="muted">No hay registros en la bitácora.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<section class="section-header">
    <div>
        <p class="eyebrow">Audit Trail</p>
        <h1>Verificador de auditoría</h1>
        <p>Trazabilidad con detección criptográfica de alteraciones.</p>
    </div>
    <a class="btn btn-light" href="<?= url('audit') ?>">Volver a bitácora</a>
</section>

<?php if (empty($summary['schemaReady'])): ?>
    <section class="card">
        <h2>Migración pendiente</h2>
        <p class="muted">Aplique la migración `2026_07_13_0006_audit_trail_criptografico.sql` para activar la cadena criptográfica de bitácora.</p>
    </section>
<?php else: ?>
    <section class="stats-grid">
        <article class="stat-card"><span>Total</span><strong><?= e($summary['total']) ?></strong></article>
        <article class="stat-card"><span>Válidos</span><strong><?= e($summary['valid']) ?></strong></article>
        <article class="stat-card"><span>Alertas</span><strong><?= e($summary['invalid']) ?></strong></article>
        <article class="stat-card"><span>No verificables</span><strong><?= e($summary['notVerifiable']) ?></strong></article>
    </section>

    <div class="table-wrap">
        <table>
            <caption>Resultado de verificación de cadena y firmas asociadas.</caption>
            <thead>
                <tr><th scope="col">Evento</th><th scope="col">Estado</th><th scope="col">Hash guardado</th><th scope="col">Hash esperado</th><th scope="col">Hash anterior</th></tr>
            </thead>
            <tbody>
            <?php foreach ($summary['results'] as $result): ?>
                <?php
                    $class = match ($result['status']) {
                        'CADENA_VALIDA' => 'success',
                        'EVENTO_NO_VERIFICABLE' => 'warning',
                        default => 'danger',
                    };
                ?>
                <tr>
                    <td>#<?= e($result['id']) ?></td>
                    <td><span class="badge badge-<?= e($class) ?>"><?= e($result['status']) ?></span></td>
                    <td><code><?= e(substr((string) $result['stored_hash'], 0, 20)) ?>...</code></td>
                    <td><code><?= e(substr((string) $result['expected_hash'], 0, 20)) ?>...</code></td>
                    <td><code><?= e(substr((string) ($result['stored_previous_hash'] ?? ''), 0, 20)) ?><?= $result['stored_previous_hash'] ? '...' : '-' ?></code></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($summary['results'])): ?><tr><td colspan="5" class="muted">No hay eventos para verificar.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<section class="section-header">
    <div>
        <p class="eyebrow">Consulta QR</p>
        <h1><?= $asset ? 'Activo verificado' : 'QR no válido' ?></h1>
        <p>Consulta pública limitada para identificar un activo sin mostrar datos sensibles.</p>
    </div>
</section>

<?php if ($asset): ?>
    <section class="card qr-lookup-card">
        <div class="detail-list">
            <div><strong>Código</strong><?= e($asset['codigo_activo']) ?></div>
            <div><strong>Activo</strong><?= e($asset['nombre']) ?></div>
            <div><strong>Categoría</strong><?= e($asset['categoria_nombre'] ?? '-') ?></div>
            <div><strong>Marca</strong><?= e($asset['marca'] ?? '-') ?></div>
            <div><strong>Estado</strong><?= status_badge($asset['estado']) ?></div>
            <div><strong>Precio</strong>B/. <?= e(number_format((float) ($asset['costo'] ?? 0), 2)) ?></div>
            <div><strong>Fecha de adquisición</strong><?= e($asset['fecha_ingreso'] ?? '-') ?></div>
        </div>
        <p class="muted mt-md">Para ver información completa, entre al sistema con un usuario autorizado.</p>
        <a class="btn btn-primary" href="<?= url('login') ?>">Iniciar sesión</a>
    </section>
<?php else: ?>
    <section class="card">
        <h2>No se pudo verificar el código</h2>
        <p class="muted">El token no existe, fue revocado o no coincide con la firma interna del QR.</p>
        <a class="btn btn-light" href="<?= url('') ?>">Volver al inicio</a>
    </section>
<?php endif; ?>

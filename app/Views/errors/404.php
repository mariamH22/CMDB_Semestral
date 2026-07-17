<section class="card error-card">
    <div class="error-code">404</div>
    <h1>Página no encontrada</h1>
    <p><?= e($message ?? 'La ruta solicitada no existe.') ?></p>
    <a class="btn btn-primary" href="<?= url('') ?>">Volver al inicio</a>
</section>

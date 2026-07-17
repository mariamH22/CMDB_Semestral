<section class="card error-card">
    <div class="error-code">403</div>
    <h1>Acceso denegado</h1>
    <p><?= e($message ?? 'No tiene permisos para acceder a este recurso.') ?></p>
    <a class="btn btn-primary" href="<?= url('') ?>">Volver al inicio</a>
</section>

<section class="card error-card">
    <div class="error-code">CMDB</div>
    <h1><?= e($title ?? 'Error') ?></h1>
    <p><?= e($message ?? 'Ocurrió un error inesperado.') ?></p>
    <a class="btn btn-primary" href="<?= url('') ?>">Volver al inicio</a>
</section>

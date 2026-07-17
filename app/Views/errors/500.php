<section class="card error-card">
    <div class="error-code">500</div>
    <h1>Error del sistema</h1>
    <p><?= e($message ?? 'Ocurrió un error inesperado. Intente nuevamente o contacte al administrador.') ?></p>
    <a class="btn btn-primary" href="<?= url('') ?>">Volver al inicio</a>
</section>

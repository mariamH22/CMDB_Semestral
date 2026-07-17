<section class="card error-card">
    <div class="error-code">419</div>
    <h1>Solicitud expirada</h1>
    <p><?= e($message ?? 'La sesión del formulario expiró. Vuelva a cargar la pantalla e intente nuevamente.') ?></p>
    <a class="btn btn-primary" href="<?= url('') ?>">Volver al inicio</a>
</section>

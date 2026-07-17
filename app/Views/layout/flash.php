<?php if ($success = flash('success')): ?>
    <div class="alert alert-success" role="status" aria-live="polite">
        <span class="alert-icon" aria-hidden="true"></span>
        <span class="alert-body"><strong>Operación completada</strong><span><?= e($success) ?></span></span>
        <button class="alert-close" type="button" data-alert-close aria-label="Cerrar mensaje">×</button>
    </div>
<?php endif; ?>

<?php if ($error = flash('error')): ?>
    <div class="alert alert-error" role="alert" aria-live="assertive">
        <span class="alert-icon" aria-hidden="true"></span>
        <span class="alert-body"><strong>Revisión requerida</strong><span><?= e($error) ?></span></span>
        <button class="alert-close" type="button" data-alert-close aria-label="Cerrar mensaje">×</button>
    </div>
<?php endif; ?>

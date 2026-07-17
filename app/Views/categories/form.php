<?php
$editing = $category !== null;
$field = static fn (string $key, mixed $default = ''): mixed => old_value($key, $category[$key] ?? $default);
$typeValue = (string) $field('tipo', 'HARDWARE');
?>
<section class="section-header">
    <div><p class="eyebrow">Catálogo</p><h1><?= e($editing ? 'Editar categoría' : 'Nueva categoría') ?></h1></div>
    <a class="btn btn-light" href="<?= url('categories') ?>">Volver</a>
</section>

<form class="card form-card" method="post" action="<?= url($editing ? 'categories/update' : 'categories/store') ?>">
    <?= csrf_field() ?>
    <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($category['id']) ?>"><?php endif; ?>
    <div class="form-grid">
        <div class="form-group">
            <label>Nombre</label>
            <input name="nombre" value="<?= e($field('nombre')) ?>" required placeholder="Ej. Equipo de Telefonía">
        </div>
        <div class="form-group">
            <label>Tipo</label>
            <select name="tipo">
                <option value="HARDWARE" <?= selected($typeValue, 'HARDWARE') ?>>Hardware</option>
                <option value="SOFTWARE" <?= selected($typeValue, 'SOFTWARE') ?>>Software</option>
            </select>
        </div>
        <div class="form-group full">
            <label>Descripción</label>
            <textarea name="descripcion"><?= e($field('descripcion')) ?></textarea>
        </div>
        <div class="form-group full">
            <div class="check-row"><label><input type="checkbox" name="activo" value="1" <?= old_checked('activo', $category['activo'] ?? 1) ?>> Categoría activa</label></div>
        </div>
    </div>
    <div class="button-row"><button class="btn btn-success" type="submit">Guardar categoría</button></div>
</form>

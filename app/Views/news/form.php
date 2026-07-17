<?php
$editing = $article !== null;
$field = static fn (string $key, mixed $default = ''): mixed => old_value($key, $article[$key] ?? $default);
?>
<section class="section-header">
    <div><p class="eyebrow">Mercadeo</p><h1><?= e($editing ? 'Editar noticia' : 'Nueva noticia') ?></h1></div>
    <a class="btn btn-light" href="<?= url('news/admin') ?>">Volver</a>
</section>

<form class="card form-card" method="post" enctype="multipart/form-data" action="<?= url($editing ? 'news/update' : 'news/store') ?>">
    <?= csrf_field() ?>
    <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($article['id']) ?>"><?php endif; ?>
    <div class="form-grid">
        <div class="form-group full"><label>Título</label><input name="titulo" value="<?= e($field('titulo')) ?>" required></div>
        <div class="form-group full"><label>Resumen</label><textarea name="resumen" required><?= e($field('resumen')) ?></textarea></div>
        <div class="form-group full"><label>Contenido</label><textarea name="contenido" required><?= e($field('contenido')) ?></textarea></div>
        <div class="form-group"><label>Imagen</label><input type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp"><?php if ($editing && $article['imagen']): ?><img class="image-thumb mt-sm" src="<?= url($article['imagen']) ?>" alt="Imagen actual" loading="lazy"><?php endif; ?></div>
        <div class="form-group"><label class="check-row"><input type="checkbox" name="publicada" value="1" <?= old_checked('publicada', $article['publicada'] ?? 1) ?>> Publicar noticia</label></div>
    </div>
    <div class="button-row"><button class="btn btn-success" type="submit">Guardar noticia</button></div>
</form>

<section class="section-header">
    <div>
        <p class="eyebrow">Página informativa</p>
        <h1>Noticias de Hardware y Software</h1>
        <p>Información sobre inventario tecnológico, buenas prácticas, seguridad y administración de activos.</p>
    </div>
</section>

<?php if ($news): ?>
    <div class="grid grid-3">
        <?php foreach ($news as $article): ?>
            <article class="card news-card">
                <?php if ($article['imagen']): ?>
                    <img src="<?= url($article['imagen']) ?>" alt="<?= e($article['titulo']) ?>" loading="lazy">
                <?php endif; ?>
                <p class="eyebrow"><?= e(date('d/m/Y', strtotime($article['created_at']))) ?></p>
                <h3><?= e($article['titulo']) ?></h3>
                <p><?= e($article['resumen']) ?></p>
                <p><?= nl2br(e($article['contenido'])) ?></p>
                <span class="small muted">Autor: <?= e($article['autor'] ?? 'CMDB Integral') ?></span>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="no-data">No hay noticias disponibles.</div>
<?php endif; ?>

<section class="section-header">
    <div><p class="eyebrow">Mercadeo del sistema</p><h1>Administrar noticias</h1><p>Publicaciones sobre hardware, software y gestión de inventarios.</p></div>
    <a class="btn btn-primary" href="<?= url('news/create') ?>">+ Nueva noticia</a>
</section>

<div class="table-wrap">
    <table>
        <caption><?= e(count($news)) ?> noticias administrativas.</caption>
        <thead><tr><th scope="col">Título</th><th scope="col">Resumen</th><th scope="col">Autor</th><th scope="col">Estado</th><th scope="col">Fecha</th><th scope="col">Acción</th></tr></thead>
        <tbody>
        <?php foreach ($news as $article): ?>
            <tr>
                <td><?= e($article['titulo']) ?></td>
                <td><?= e($article['resumen']) ?></td>
                <td><?= e($article['autor'] ?? '-') ?></td>
                <td><?= (int) $article['publicada'] ? status_badge('PUBLICADA') : status_badge('BORRADOR') ?></td>
                <td><?= e(date('d/m/Y', strtotime($article['created_at']))) ?></td>
                <td><a class="btn btn-light btn-small" href="<?= url('news/edit?id=' . $article['id']) ?>">Editar</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

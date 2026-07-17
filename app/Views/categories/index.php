<section class="section-header">
    <div>
        <p class="eyebrow">Catálogo</p>
        <h1>Categorías de activos</h1>
        <p>Hardware, software, equipo de red, cómputo y telefonía.</p>
    </div>
    <?php if (\App\Core\Auth::can('categories.manage')): ?><a class="btn btn-primary" href="<?= url('categories/create') ?>">+ Nueva categoría</a><?php endif; ?>
</section>

<div class="table-wrap">
    <table>
        <caption><?= e(count($categories)) ?> categorías registradas.</caption>
        <thead><tr><th scope="col">Nombre</th><th scope="col">Tipo</th><th scope="col">Descripción</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($categories as $category): ?>
            <tr>
                <td><?= e($category['nombre']) ?></td>
                <td><?= status_badge($category['tipo']) ?></td>
                <td><?= e($category['descripcion']) ?></td>
                <td><?= (int) $category['activo'] ? status_badge('ACTIVO') : status_badge('BAJA') ?></td>
                <td>
                    <?php if (\App\Core\Auth::can('categories.manage')): ?>
                        <a class="btn btn-light btn-small" href="<?= url('categories/edit?id=' . $category['id']) ?>">Editar</a>
                        <?php if ((int) $category['activo']): ?>
                            <form method="post" action="<?= url('categories/deactivate') ?>" class="inline-action">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e($category['id']) ?>">
                                <button class="btn btn-danger btn-small" data-confirm="¿Dar de baja esta categoría?" type="submit">Baja</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

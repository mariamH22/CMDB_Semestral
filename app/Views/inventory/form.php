<?php
$editing = $item !== null;
$imageWarning = $editing ? \App\Core\InventoryImagePolicy::legacyWarning($item) : null;
$field = static fn (string $key, mixed $default = ''): mixed => old_value($key, $item[$key] ?? $default);
$typeValue = (string) $field('tipo_activo', 'HARDWARE');
$statusValue = (string) $field('estado', 'DISPONIBLE');
$licenseStatusValue = (string) $field('estado_licencia', \App\Core\LicensePolicy::ACTIVA);
$licenseKeyConfigured = (bool) ($licenseKeyConfigured ?? false);
$hardwareImagesRequired = !$editing && strtoupper($typeValue) === 'HARDWARE';
?>
<section class="section-header">
    <div>
        <p class="eyebrow">CRUD de inventario</p>
        <h1><?= e($editing ? 'Editar activo o licencia' : 'Registrar activo o licencia') ?></h1>
        <p>Los campos críticos se sellan con una firma HMAC: serie, tipo, estado y fecha de ingreso.</p>
    </div>
    <a class="btn btn-light" href="<?= url('inventory') ?>">Volver</a>
</section>

<form class="card form-card" method="post" enctype="multipart/form-data" action="<?= url($editing ? 'inventory/update' : 'inventory/store') ?>" data-inventory-form data-editing="<?= $editing ? '1' : '0' ?>">
    <?= csrf_field() ?>
    <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($item['id']) ?>"><?php endif; ?>
    <?php if ($imageWarning): ?><div class="alert alert-warning"><?= e($imageWarning) ?></div><?php endif; ?>

    <div class="form-grid-3">
        <div class="form-section-heading full">
            <span>01</span>
            <div><strong>Identificación y clasificación</strong><small>Código, nombre, tipo, categoría y datos técnicos del activo.</small></div>
        </div>
        <div class="form-group">
            <label>Código de activo</label>
            <input name="codigo_activo" value="<?= e($field('codigo_activo')) ?>" required placeholder="ACT-0001">
        </div>
        <div class="form-group">
            <label>Nombre del equipo / software</label>
            <input name="nombre" value="<?= e($field('nombre')) ?>" required>
        </div>
        <div class="form-group">
            <label>Tipo de activo</label>
            <select name="tipo_activo">
                <option value="HARDWARE" <?= selected($typeValue, 'HARDWARE') ?>>Hardware</option>
                <option value="SOFTWARE" <?= selected($typeValue, 'SOFTWARE') ?>>Software</option>
            </select>
        </div>
        <div class="form-group">
            <label>Categoría</label>
            <select name="categoria_id">
                <option value="0">Seleccione una categoría</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e($category['id']) ?>" <?= selected($field('categoria_id', 0), $category['id']) ?>><?= e($category['nombre']) ?> (<?= e($category['tipo']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Subcategoría</label><input name="subcategoria" value="<?= e($field('subcategoria')) ?>" placeholder="Ej. Laptop / Antivirus / Office"></div>
        <div class="form-group"><label>Marca</label><input name="marca" value="<?= e($field('marca')) ?>"></div>
        <div class="form-group"><label>Modelo</label><input name="modelo" value="<?= e($field('modelo')) ?>"></div>
        <div class="form-group"><label>Serie / identificador único</label><input name="serie" value="<?= e($field('serie')) ?>" required></div>

        <div class="form-section-heading full">
            <span>02</span>
            <div><strong>Compra, vida útil y estado</strong><small>Campos usados para depreciación, disponibilidad e integridad HMAC.</small></div>
        </div>
        <div class="form-group"><label>Costo</label><input type="number" min="0" step="0.01" name="costo" value="<?= e($field('costo', '0')) ?>" required></div>
        <div class="form-group"><label>Fecha de ingreso</label><input type="date" name="fecha_ingreso" value="<?= e($field('fecha_ingreso', date('Y-m-d'))) ?>" required></div>
        <div class="form-group"><label>Vida útil / depreciación (meses)</label><input type="number" min="1" max="240" name="vida_util_meses" value="<?= e($field('vida_util_meses', '36')) ?>" required></div>
        <div class="form-group">
            <label>Estado</label>
            <?php if ($editing): ?>
                <input type="hidden" name="estado" value="<?= e($item['estado']) ?>">
                <div class="readonly-field"><?= status_badge($item['estado']) ?></div>
            <?php else: ?>
                <select name="estado">
                <?php foreach (\App\Core\InventoryStatus::creationTransitions() as $status): ?>
                    <option value="<?= e($status) ?>" <?= selected($statusValue, $status) ?>><?= e(\App\Core\InventoryStatus::label($status)) ?></option>
                <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>

        <div class="form-section-heading full">
            <span>03</span>
            <div><strong>Imágenes y opciones operativas</strong><small>Hardware requiere dos imágenes; software puede registrarse sin imagen.</small></div>
        </div>
        <div class="form-group"><label>Imagen principal</label><input type="file" name="imagen_principal" accept=".jpg,.jpeg,.png,.webp" data-hardware-image <?= $hardwareImagesRequired ? 'required' : '' ?>><span class="small muted">JPG, PNG o WEBP, máximo 2 MB. Hardware requiere mínimo dos imágenes.</span><?php if ($editing && $item['thumbnail']): ?><img class="image-thumb mt-sm" src="<?= url($item['thumbnail']) ?>" alt="Miniatura" loading="lazy"><?php endif; ?></div>
        <div class="form-group"><label>Imagen adicional</label><input type="file" name="imagen_adicional" accept=".jpg,.jpeg,.png,.webp" data-hardware-image <?= $hardwareImagesRequired ? 'required' : '' ?>><span class="small muted">Hardware requiere mínimo dos imágenes; software puede quedar sin imagen.</span></div>
        <div class="form-group">
            <label>Opciones</label>
            <div class="check-row">
                <label><input id="es_licencia" type="checkbox" name="es_licencia" value="1" <?= old_checked('es_licencia', $item['es_licencia'] ?? 0) ?>> Es licencia de software</label>
                <label><input type="checkbox" name="activo" value="1" <?= old_checked('activo', $item['activo'] ?? 1) ?>> Activo</label>
            </div>
        </div>

        <div class="form-section-heading full license-field">
            <span>04</span>
            <div><strong>Licencia de software</strong><small>Proveedor, cupos, vencimiento y clave cifrada cuando exista configuración local.</small></div>
        </div>
        <div class="form-group license-field">
            <label>Clave de licencia</label>
            <input
                name="clave_licencia"
                value=""
                placeholder="<?= $licenseKeyConfigured ? ($editing ? 'Dejar vacío para conservar la clave actual' : 'Se cifrará antes de guardar') : 'Configure la clave maestra para guardar este dato' ?>"
                <?= $licenseKeyConfigured ? '' : 'disabled' ?>
            >
            <?php if ($licenseKeyConfigured): ?>
                <span class="small muted">La clave se cifra antes de guardarse y no se conserva si el formulario vuelve por error.</span>
            <?php else: ?>
                <span class="small muted">Puede registrar la licencia sin clave. Para guardar seriales o claves, falta configurar el cifrado local.</span>
            <?php endif; ?>
        </div>
        <div class="form-group license-field"><label>Proveedor de licencia</label><input name="proveedor_licencia" value="<?= e($field('proveedor_licencia')) ?>"></div>
        <div class="form-group license-field"><label>Tipo de licencia</label><input name="tipo_licencia" value="<?= e($field('tipo_licencia')) ?>" placeholder="Anual, OEM, suscripción, perpetua"></div>
        <div class="form-group license-field"><label>Fecha de adquisición</label><input type="date" name="fecha_adquisicion_licencia" value="<?= e($field('fecha_adquisicion_licencia')) ?>"></div>
        <div class="form-group license-field"><label>URL de licencia</label><input name="url_licencia" value="<?= e($field('url_licencia')) ?>"></div>
        <div class="form-group license-field"><label>Vencimiento de licencia</label><input type="date" name="fecha_vencimiento_licencia" value="<?= e($field('fecha_vencimiento_licencia')) ?>"></div>
        <div class="form-group license-field">
            <label>Estado de licencia</label>
            <select name="estado_licencia">
                <?php foreach (\App\Core\LicensePolicy::statuses() as $licenseStatus): ?>
                    <option value="<?= e($licenseStatus) ?>" <?= selected($licenseStatusValue, $licenseStatus) ?>><?= e($licenseStatus) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Cantidad</label><input type="number" min="1" name="cantidad" value="<?= e($field('cantidad', 1)) ?>"></div>

        <div class="form-section-heading full">
            <span>05</span>
            <div><strong>Observaciones</strong><small>Notas administrativas y comentarios de licencia sin datos sensibles.</small></div>
        </div>
        <div class="form-group full"><label>Observación de licencia</label><textarea name="observaciones_licencia"><?= e($field('observaciones_licencia')) ?></textarea></div>
        <div class="form-group full"><label>Notas</label><textarea name="notas"><?= e($field('notas')) ?></textarea></div>
    </div>

    <div class="button-row">
        <button class="btn btn-success" type="submit"><?= $editing ? 'Actualizar activo' : 'Registrar activo' ?></button>
    </div>
</form>

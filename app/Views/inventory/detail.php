<section class="section-header">
    <div>
        <p class="eyebrow">Activo #<?= e($item['id']) ?></p>
        <h1><?= e($item['nombre']) ?></h1>
        <p><?= e($item['codigo_activo']) ?> · <?= e($item['marca']) ?> <?= e($item['modelo']) ?></p>
    </div>
    <div>
        <a class="btn btn-light" href="<?= url('inventory') ?>">Volver</a>
        <?php if (\App\Core\Auth::can('inventory.manage')): ?><a class="btn btn-primary" href="<?= url('inventory/edit?id=' . $item['id']) ?>">Editar</a><?php endif; ?>
    </div>
</section>

<div class="grid grid-2">
    <section class="card">
        <?php if (!empty($imageWarning)): ?><div class="alert alert-warning"><?= e($imageWarning) ?></div><?php endif; ?>
        <?php if ($item['imagen_principal']): ?>
            <img class="image-large" src="<?= url($item['imagen_principal']) ?>" alt="<?= e($item['nombre']) ?>" loading="lazy">
        <?php else: ?>
            <div class="no-data">No se ha cargado imagen principal para este activo.</div>
        <?php endif; ?>

        <?php if (!empty($item['imagenes'])): ?>
            <div class="gallery">
                <?php foreach ($item['imagenes'] as $image): ?><img src="<?= url($image['ruta']) ?>" alt="Imagen del activo" loading="lazy"><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="qr-panel">
            <h3>QR del activo</h3>
            <?php if (empty($qrSchemaReady)): ?>
                <div class="no-data">La migración de QR seguro aún no está aplicada.</div>
            <?php elseif (empty($qr)): ?>
                <div class="no-data">Este activo no tiene un QR activo.</div>
                <?php if (\App\Core\Auth::can('inventory.manage')): ?>
                    <form method="post" action="<?= url('inventory/qr-generate') ?>" class="qr-actions no-print">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($item['id']) ?>">
                        <button class="btn btn-success btn-small" type="submit">Generar QR</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="print-label">
                    <strong><?= e($item['codigo_activo']) ?></strong>
                    <span><?= e($item['nombre']) ?></span>
                    <img class="qr-image" src="<?= url('inventory/qr-svg?id=' . $item['id']) ?>" alt="QR del activo">
                    <span class="small muted"><?= e($item['categoria_nombre'] ?? 'CMDB Integral') ?></span>
                </div>
                <div class="qr-actions no-print">
                    <a class="btn btn-light btn-small" href="<?= url('inventory/qr-download?id=' . $item['id']) ?>">Descargar SVG</a>
                    <button class="btn btn-light btn-small" type="button" data-print-page>Imprimir</button>
                    <?php if (!empty($qr['token'])): ?>
                        <a class="btn btn-primary btn-small" href="<?= url('qr?t=' . $qr['token']) ?>" target="_blank" rel="noopener">Vista QR</a>
                    <?php endif; ?>
                    <?php if (\App\Core\Auth::can('inventory.manage')): ?>
                        <form method="post" action="<?= url('inventory/qr-regenerate') ?>" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e($item['id']) ?>">
                            <input type="hidden" name="motivo_qr" value="Regeneración desde detalle de activo">
                            <button class="btn btn-warning btn-small" data-confirm="El QR actual quedará revocado y se generará uno nuevo. ¿Continuar?" type="submit">Regenerar</button>
                        </form>
                        <form method="post" action="<?= url('inventory/qr-revoke') ?>" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e($item['id']) ?>">
                            <input type="hidden" name="motivo_qr" value="Revocación desde detalle de activo">
                            <button class="btn btn-danger btn-small" data-confirm="El QR dejará de funcionar públicamente. ¿Continuar?" type="submit">Revocar</button>
                        </form>
                    <?php endif; ?>
                </div>
                <p class="small muted no-print">La URL pública solo muestra etiqueta, nombre, categoría, marca y estado general.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="card">
        <h2>Información del activo</h2>
        <div class="detail-list">
            <div><strong>Tipo</strong><?= status_badge($item['tipo_activo']) ?></div>
            <div><strong>Categoría</strong><?= e($item['categoria_nombre'] ?? '-') ?></div>
            <div><strong>Serie</strong><?= e($item['serie']) ?></div>
            <div><strong>Estado</strong><?= status_badge($item['estado']) ?></div>
            <div><strong>Costo</strong>$<?= e(number_format((float)$item['costo'], 2)) ?></div>
            <div><strong>Fecha de ingreso</strong><?= e($item['fecha_ingreso']) ?></div>
            <div><strong>Vida útil</strong><?= e($item['vida_util_meses']) ?> meses</div>
            <div><strong>Licencia</strong><?= (int)$item['es_licencia'] ? 'Sí' : 'No' ?></div>
            <?php if ((int) $item['es_licencia']): ?>
                <div><strong>Proveedor licencia</strong><?= e($item['proveedor_licencia'] ?? '-') ?></div>
                <div><strong>Tipo licencia</strong><?= e($item['tipo_licencia'] ?? '-') ?></div>
                <div><strong>Adquisición licencia</strong><?= e($item['fecha_adquisicion_licencia'] ?? '-') ?></div>
                <div><strong>Vencimiento licencia</strong><?= e($item['fecha_vencimiento_licencia'] ?? '-') ?></div>
                <div><strong>URL licencia</strong><?= e($item['url_licencia'] ?? '-') ?></div>
                <div><strong>Estado licencia</strong><?= status_badge($item['estado_licencia'] ?? 'ACTIVA') ?></div>
                <div><strong>Cupos</strong><?= e((string) ($licenseUsed ?? 0)) ?> usados / <?= e((string) $item['cantidad']) ?> totales</div>
            <?php endif; ?>
            <div>
                <strong>Clave licencia</strong>
                <?php if ((int) $item['es_licencia'] && ($licenseKeyMasked ?? '-') !== '-'): ?>
                    <?php // La licencia se enmascara por defecto y solo se muestra tras una accion auditada. ?>
                    <?php if (!empty($licenseRevealed) && !empty($canRevealLicense) && !empty($licenseKeyDisplay)): ?>
                        <?= e($licenseKeyDisplay) ?>
                    <?php else: ?>
                        <?= e($licenseKeyMasked) ?>
                        <?php if (!empty($canRevealLicense)): ?>
                            <form method="post" action="<?= url('inventory/reveal-license') ?>" class="inline-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= e($item['id']) ?>">
                                <button class="btn btn-light btn-small" data-confirm="Esta acción quedará registrada en bitácora. ¿Desea revelar la clave?" type="submit">Revelar clave</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!empty($licenseKeyLegacyPlaintext)): ?>
                        <span class="small warning-text">Pendiente de migrar a cifrado.</span>
                    <?php elseif (!empty($licenseKeyEncrypted)): ?>
                        <span class="small muted">Cifrada.</span>
                    <?php endif; ?>
                <?php else: ?>
                    -
                <?php endif; ?>
            </div>
            <div><strong>Cantidad</strong><?= e($item['cantidad']) ?></div>
            <div><strong>Responsable donación</strong><?= e($item['responsable_donacion'] ?: '-') ?></div>
            <div><strong>Beneficiario donación</strong><?= e($item['beneficiario_donacion'] ?? '-') ?></div>
            <div><strong>Evidencia donación</strong><?= e($item['evidencia_donacion'] ?? '-') ?></div>
            <div><strong>Opinión descarte</strong><?= e($item['observacion_tecnica_descarte'] ?? '-') ?></div>
            <div><strong>Evidencia descarte</strong><?= e($item['evidencia_descarte'] ?? '-') ?></div>
            <div>
                <strong>Integridad HMAC</strong>
                <?php if (!empty($integrityConfigured)): ?>
                    <?= $item['integridad_valida'] ? status_badge('FIRMA VÁLIDA') : status_badge('ALERTA: FIRMA INVÁLIDA') ?>
                <?php else: ?>
                    <?= status_badge('FIRMA PENDIENTE DE CONFIGURACIÓN') ?>
                <?php endif; ?>
            </div>
        </div>

        <h3 class="mt-md">Notas</h3>
        <p class="muted"><?= nl2br(e($item['notas'] ?: 'Sin notas adicionales.')) ?></p>
    </section>
</div>

<?php if ((int) $item['es_licencia']): ?>
<section class="card section-spaced">
    <h2>Control de cupos de licencia</h2>
    <?php if (empty($licenseSchemaReady)): ?>
        <div class="no-data">La migración de cupos de licencia aún no está aplicada.</div>
    <?php else: ?>
        <p class="muted">Cupos disponibles: <?= e((string) ($licenseAvailable ?? 0)) ?></p>
        <?php if (\App\Core\Auth::can('inventory.manage') && ($licenseAvailable ?? 0) > 0): ?>
            <form method="post" action="<?= url('inventory/license-assign') ?>" class="form-grid">
                <?= csrf_field() ?>
                <input type="hidden" name="inventario_id" value="<?= e($item['id']) ?>">
                <div class="form-group">
                    <label>Colaborador</label>
                    <select name="colaborador_id" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($collaborators as $collaborator): ?>
                            <option value="<?= e($collaborator['id']) ?>"><?= e($collaborator['nombres'] . ' ' . $collaborator['apellidos'] . ' · ' . $collaborator['departamento']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Cantidad</label><input type="number" min="1" max="<?= e((string) $licenseAvailable) ?>" name="cantidad" value="1" required></div>
                <div class="form-group"><label>Fecha de asignación</label><input type="date" name="fecha_asignacion" value="<?= e(date('Y-m-d')) ?>" required></div>
                <div class="form-group"><label>Observaciones</label><input name="observaciones"></div>
                <div class="form-group full"><button class="btn btn-success" type="submit">Asignar cupo</button></div>
            </form>
        <?php endif; ?>

        <div class="table-wrap mt-sm">
            <table>
                <caption>Cupos activos asociados a esta licencia.</caption>
                <thead><tr><th scope="col">Colaborador</th><th scope="col">Cantidad</th><th scope="col">Desde</th><th scope="col">Observaciones</th><th scope="col">Acción</th></tr></thead>
                <tbody>
                <?php foreach ($licenseAssignments as $licenseAssignment): ?>
                    <tr>
                        <td><?= e($licenseAssignment['colaborador_nombre']) ?><br><span class="small muted"><?= e($licenseAssignment['departamento']) ?></span></td>
                        <td><?= e($licenseAssignment['cantidad']) ?></td>
                        <td><?= e($licenseAssignment['fecha_asignacion']) ?></td>
                        <td><?= e($licenseAssignment['observaciones'] ?? '-') ?></td>
                        <td>
                            <?php if (\App\Core\Auth::can('inventory.manage')): ?>
                                <form method="post" action="<?= url('inventory/license-release') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= e($licenseAssignment['id']) ?>">
                                    <input type="hidden" name="inventario_id" value="<?= e($item['id']) ?>">
                                    <button class="btn btn-warning btn-small" type="submit">Liberar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$licenseAssignments): ?><tr><td colspan="5" class="muted">No hay cupos activos asignados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if (\App\Core\Auth::can('inventory.manage')): ?>
<section class="card section-spaced">
    <h2>Cambiar estado del activo</h2>
    <p class="muted">Use esta opción solo para transiciones manuales permitidas. Descarte y donación se registran desde revisión técnica.</p>
    <?php $manualTransitions = \App\Core\InventoryStatus::manualTransitions((string) $item['estado']); ?>
    <?php if (!$manualTransitions): ?>
        <div class="no-data">Este estado no permite cambios manuales directos.</div>
    <?php else: ?>
    <form method="post" action="<?= url('inventory/status') ?>" class="form-grid">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e($item['id']) ?>">
        <div class="form-group">
            <label>Nuevo estado</label>
            <select name="estado">
                <?php foreach ($manualTransitions as $status): ?><option value="<?= e($status) ?>"><?= e(\App\Core\InventoryStatus::label($status)) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="form-group full"><label>Observación del cambio</label><textarea name="observacion_estado"></textarea></div>
        <div class="form-group full"><button class="btn btn-warning" type="submit">Actualizar estado</button></div>
    </form>
    <?php endif; ?>
</section>
<?php endif; ?>

<section class="card section-spaced">
    <h2>Historial formal de estados</h2>
    <?php if (empty($historySchemaReady)): ?>
        <div class="no-data">La migración de historial formal aún no está aplicada.</div>
    <?php elseif ($history): ?>
        <div class="table-wrap">
            <table>
                <caption>Eventos de estado registrados para este activo.</caption>
                <thead><tr><th scope="col">Fecha</th><th scope="col">Usuario</th><th scope="col">Anterior</th><th scope="col">Nuevo</th><th scope="col">Motivo</th><th scope="col">Observación</th></tr></thead>
                <tbody>
                <?php foreach ($history as $row): ?>
                    <tr>
                        <td><?= e($row['created_at']) ?></td>
                        <td><?= e($row['nombre_usuario'] ?? '-') ?></td>
                        <td><?= e($row['estado_anterior'] ?? '-') ?></td>
                        <td><?= status_badge($row['estado_nuevo']) ?></td>
                        <td><?= e($row['motivo'] ?? '-') ?></td>
                        <td><?= e($row['observacion'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-data">Sin historial formal registrado para este activo.</div>
    <?php endif; ?>
</section>

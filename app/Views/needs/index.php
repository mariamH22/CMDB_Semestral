<section class="section-header">
    <div>
        <p class="eyebrow">Solicitudes internas</p>
        <h1>Necesidades de equipos y software</h1>
        <p>Requerimientos originados por colaboradores: equipos, software o licencias.</p>
    </div>
</section>

<div class="table-wrap">
        <table>
            <caption><?= e(count($needs)) ?> solicitudes registradas.</caption>
            <!-- Cabeceras adaptables por compatibilidad de schema (cantidad/año/costo según columnas disponibles). -->
            <thead><tr><th scope="col">Fecha</th><th scope="col">Colaborador</th><th scope="col">Departamento</th><th scope="col">Tipo</th><th scope="col">Detalle</th><?php if (!empty($supportsNeedQuantity)): ?><th scope="col">Cantidad</th><?php endif; ?><?php if (!empty($supportsAnioObjetivo)): ?><th scope="col">Año objetivo</th><?php endif; ?><?php if (!empty($supportsUnitEstimatedCost)): ?><th scope="col">Costo unitario</th><?php endif; ?><?php if (!empty($supportsEstimatedCost)): ?><th scope="col">Costo total</th><?php endif; ?><th scope="col">Prioridad</th><th scope="col">Estado</th><th scope="col">Procesar</th></tr></thead>
        <tbody>
        <?php foreach ($needs as $need): ?>
            <tr>
                <td><?= e(date('d/m/Y', strtotime($need['created_at']))) ?></td>
                <td><?= e($need['colaborador_nombre']) ?></td>
                <td><?= e($need['departamento']) ?></td>
                <td><?= status_badge($need['tipo_necesidad']) ?><br><span class="small muted"><?= e($need['categoria_nombre'] ?: '-') ?></span></td>
                <td>
                    <?= e($need['descripcion']) ?>
                    <?php if (!empty($need['justificacion'])): ?><br><span class="small muted">Justificación: <?= e($need['justificacion']) ?></span><?php endif; ?>
                </td>
                <?php if (!empty($supportsNeedQuantity)): ?><td><?= e((string) ($need['cantidad'] ?? 1)) ?></td><?php endif; ?>
                <?php if (!empty($supportsAnioObjetivo)): ?><td><?= e((string) ($need['anio_objetivo'] ?: '-')) ?></td><?php endif; ?>
                <?php if (!empty($supportsUnitEstimatedCost)): ?><td>$<?= e(number_format((float) ($need['costo_unitario_estimado'] ?? 0), 2)) ?></td><?php endif; ?>
                <?php if (!empty($supportsEstimatedCost)): ?><td>$<?= e(number_format((float) ($need['costo_estimado'] ?? 0), 2)) ?></td><?php endif; ?>
                <td><?= status_badge($need['prioridad']) ?></td>
                <td>
                    <?= status_badge($need['estado']) ?><br><span class="small muted"><?= e(($need['respuesta_administrativa'] ?? '') ?: ($need['comentario_resolucion'] ?? '')) ?></span>
                    <?php if (!empty($need['procesador_nombre'])): ?><br><span class="small muted">Procesado por <?= e($need['procesador_nombre']) ?></span><?php endif; ?>
                    <!-- Historial de cambios de estado si la tabla soporte_historial existe en el esquema actual. -->
                    <?php if (!empty($needHistory[$need['id']] ?? [])): ?>
                        <details class="small muted">
                            <summary>Historial</summary>
                            <ul class="list">
                                <?php foreach ($needHistory[$need['id']] as $history): ?>
                                    <li>
                                        <?= e($history['created_at']) ?> -
                                        <?= e($history['estado_anterior'] ?: 'Sin estado anterior') ?> -> <?= e($history['estado_nuevo']) ?><?= !empty($history['nombre_usuario']) ? ' · ' . e($history['nombre_usuario']) : '' ?>
                                        <br><span class="small muted"><?= e(($history['respuesta_administrativa'] ?? '') ?: ($history['observacion'] ?? '-')) ?></span>
                                        <?php if (!empty($history['firma_id'])): ?><br><span class="small muted">Firma #<?= e($history['firma_id']) ?></span><?php endif; ?>
                                        <?php if (!empty($history['audit_id'])): ?><span class="small muted"> · Auditoría #<?= e($history['audit_id']) ?></span><?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (\App\Core\Auth::can('needs.manage')): ?>
                        <form method="post" action="<?= url('needs/update-status') ?>" class="compact-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e($need['id']) ?>">
                            <label>Estado</label>
                            <select name="estado">
                                <?php foreach (\App\Core\NeedStatus::values() as $state): ?><option value="<?= e($state) ?>" <?= selected(\App\Core\NeedStatus::normalize($need['estado']), $state) ?>><?= e(\App\Core\NeedStatus::label($state)) ?></option><?php endforeach; ?>
                            </select>
                            <label>Respuesta administrativa</label>
                            <input name="respuesta_administrativa" placeholder="Respuesta" value="<?= e(($need['respuesta_administrativa'] ?? '') ?: ($need['comentario_resolucion'] ?? '')) ?>">
                            <button class="btn btn-primary btn-small" type="submit">Guardar</button>
                        </form>
                    <?php else: ?>
                        <span class="muted">Solo lectura</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php
            $colspan = 8;
            if (!empty($supportsNeedQuantity)) {
                $colspan++;
            }
            if (!empty($supportsAnioObjetivo)) {
                $colspan++;
            }
            if (!empty($supportsUnitEstimatedCost)) {
                $colspan++;
            }
            if (!empty($supportsEstimatedCost)) {
                $colspan++;
            }
        ?>
        <?php if (!$needs): ?><tr><td colspan="<?= $colspan ?>" class="muted">No hay necesidades registradas.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

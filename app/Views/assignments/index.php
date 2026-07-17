<section class="section-header">
    <div>
        <p class="eyebrow">Custodia y responsabilidad</p>
        <h1>Asignaciones de equipos</h1>
        <p>Indica qué equipo tiene cada colaborador, departamento, ubicación, IP y desde cuándo.</p>
    </div>
    <?php if (\App\Core\Auth::can('assignments.manage')): ?><a class="btn btn-primary" href="<?= url('assignments/create') ?>">+ Nueva asignación</a><?php endif; ?>
</section>

<div class="table-wrap">
    <table>
        <caption><?= e(count($assignments)) ?> asignaciones registradas.</caption>
        <thead><tr><th scope="col">Activo</th><th scope="col">Equipo</th><th scope="col">Colaborador</th><th scope="col">Departamento / Ubicación</th><th scope="col">IP</th><th scope="col">Desde</th><th scope="col">Asignó</th><th scope="col">Estado</th><th scope="col">Acción</th></tr></thead>
        <tbody>
        <?php foreach ($assignments as $assignment): ?>
            <tr>
                <td><?= e($assignment['codigo_activo']) ?><br><span class="small muted"><?= e($assignment['serie']) ?></span></td>
                <td><?= e($assignment['equipo_nombre']) ?><br><?= status_badge($assignment['tipo_activo']) ?></td>
                <td><?= e($assignment['colaborador_nombre']) ?></td>
                <td><?= e($assignment['departamento']) ?><br><span class="small muted"><?= e($assignment['ubicacion']) ?></span></td>
                <td><?= e($assignment['ip_asignada'] ?: '-') ?></td>
                <td><?= e($assignment['fecha_asignacion']) ?></td>
                <td><?= e($assignment['asignador_nombre'] ?? '-') ?></td>
                <td><?= status_badge($assignment['estado']) ?></td>
                <td>
                    <?php if (\App\Core\Auth::can('assignments.manage') && $assignment['estado'] === 'ACTIVA' && ($assignment['inventario_estado'] ?? '') === \App\Core\InventoryStatus::ASIGNADO && !empty($returnSchemaReady)): ?>
                        <form method="post" action="<?= url('assignments/close') ?>" class="compact-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e($assignment['id']) ?>">
                            <label>Motivo</label>
                            <input name="motivo" value="Devolución de activo" required>
                            <label>Observación</label>
                            <input name="observaciones" placeholder="Observación">
                            <label>Evidencia</label>
                            <input name="evidencia" placeholder="Acta, URL o referencia">
                            <button data-confirm="¿Registrar solicitud de devolución del equipo?" class="btn btn-warning btn-small" type="submit">Solicitar devolución</button>
                        </form>
                    <?php elseif (\App\Core\Auth::can('assignments.manage') && $assignment['estado'] === 'ACTIVA' && ($assignment['inventario_estado'] ?? '') !== \App\Core\InventoryStatus::ASIGNADO): ?>
                        <span class="small muted">Devolución en proceso.</span>
                    <?php elseif (\App\Core\Auth::can('assignments.manage') && $assignment['estado'] === 'ACTIVA'): ?>
                        <span class="small muted">Migración de ciclo de vida pendiente.</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$assignments): ?><tr><td colspan="9" class="muted">No hay asignaciones registradas.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<section class="section-header"><div><p class="eyebrow">Revisión técnica</p><h2>Devoluciones pendientes</h2></div></section>
<?php if (empty($returnSchemaReady)): ?>
    <section class="card"><div class="no-data">La migración de devoluciones y revisiones técnicas aún no está aplicada.</div></section>
<?php else: ?>
    <div class="card card-soft mb-md">
        <div class="timeline">
            <div class="timeline-item"><span class="timeline-dot"></span><div class="timeline-body"><strong>Asignado</strong><span class="muted">El activo está bajo responsabilidad de un colaborador.</span></div></div>
            <div class="timeline-item"><span class="timeline-dot"></span><div class="timeline-body"><strong>Devolución registrada</strong><span class="muted">Se captura motivo y observación sin cerrar la asignación.</span></div></div>
            <div class="timeline-item"><span class="timeline-dot"></span><div class="timeline-body"><strong>Recepción física</strong><span class="muted">Un usuario autorizado recibe el activo, valida condición, accesorios y evidencia.</span></div></div>
            <div class="timeline-item"><span class="timeline-dot"></span><div class="timeline-body"><strong>Revisión técnica</strong><span class="muted">Se define el estado final y se cierra la asignación.</span></div></div>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <caption><?= e(count($pendingReviews)) ?> devoluciones pendientes de revisión técnica.</caption>
            <thead><tr><th scope="col">Activo</th><th scope="col">Colaborador</th><th scope="col">Motivo</th><th scope="col">Estado físico</th><th scope="col">Estado</th><th scope="col">Observaciones</th><th scope="col">Acción</th></tr></thead>
            <tbody>
            <?php foreach ($pendingReviews as $return): ?>
                <tr>
                    <td><?= e($return['codigo_activo']) ?><br><span class="small muted"><?= e($return['equipo_nombre']) ?> · <?= e($return['serie']) ?></span></td>
                    <td><?= e($return['colaborador_nombre']) ?></td>
                    <td><?= e($return['motivo']) ?></td>
                    <td><?= !empty($return['estado_fisico']) ? status_badge($return['estado_fisico']) : '<span class="small muted">Pendiente de recepción</span>' ?></td>
                    <td>
                        <?= status_badge($return['estado']) ?>
                        <?php if (!empty($return['receptor_nombre'])): ?><br><span class="small muted">Recibió: <?= e($return['receptor_nombre']) ?></span><?php endif; ?>
                        <?php if (!empty($return['fecha_recepcion'])): ?><br><span class="small muted"><?= e($return['fecha_recepcion']) ?></span><?php endif; ?>
                    </td>
                    <td><?= e($return['observaciones'] ?? '-') ?></td>
                    <td>
                        <?php if (\App\Core\Auth::can('assignments.manage') && $return['estado'] === 'PENDIENTE_REVISION'): ?>
                            <form method="post" action="<?= url('assignments/receive') ?>" class="compact-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="devolucion_id" value="<?= e($return['id']) ?>">
                                <label>Estado al recibir</label>
                                <select name="estado_fisico">
                                    <option value="BUENO" <?= selected($return['estado_fisico'] ?: 'BUENO', 'BUENO') ?>>Bueno</option>
                                    <option value="REGULAR" <?= selected($return['estado_fisico'], 'REGULAR') ?>>Regular</option>
                                    <option value="DANADO" <?= selected($return['estado_fisico'], 'DANADO') ?>>Dañado</option>
                                    <option value="INCOMPLETO" <?= selected($return['estado_fisico'], 'INCOMPLETO') ?>>Incompleto</option>
                                </select>
                                <label>Accesorios recibidos</label>
                                <input name="accesorios_recibidos" placeholder="Cargador, cable, base, etc.">
                                <label>Observación recepción</label>
                                <input name="observacion_recepcion" placeholder="Condición al recibir">
                                <label>Evidencia</label>
                                <input name="evidencia" value="<?= e($return['evidencia'] ?? '') ?>" placeholder="Acta, URL o referencia">
                                <button class="btn btn-warning btn-small" data-confirm="¿Registrar recepción física de esta devolución?" type="submit">Recibir activo</button>
                            </form>
                        <?php elseif (\App\Core\Auth::can('assignments.manage') && $return['estado'] === 'EN_REVISION'): ?>
                            <form method="post" action="<?= url('assignments/review') ?>" class="compact-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="devolucion_id" value="<?= e($return['id']) ?>">
                                <label>Resultado</label>
                                <select name="resultado">
                                    <?php foreach (\App\Core\InventoryStatus::reviewResults() as $status): ?>
                                        <option value="<?= e($status) ?>"><?= e(\App\Core\InventoryStatus::label($status)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Diagnóstico</label>
                                <input name="diagnostico" placeholder="Diagnóstico técnico" required>
                                <label>Observación técnica</label>
                                <input name="observacion_tecnica" placeholder="Observación técnica" required>
                                <label>Recomendación</label>
                                <input name="recomendacion" placeholder="Recomendación">
                                <label>Evidencia</label>
                                <input name="evidencia" placeholder="Acta, URL o referencia">
                                <label>Motivo final</label>
                                <input name="motivo_final" placeholder="Obligatorio para descarte o donación">
                                <label>Responsable donación</label>
                                <input name="responsable_donacion" placeholder="Si dona">
                                <label>Beneficiario</label>
                                <input name="beneficiario_donacion" placeholder="Si dona">
                                <label>Valor donación</label>
                                <input type="number" min="0" step="0.01" name="valor_donacion" value="0">
                                <button class="btn btn-success btn-small" type="submit">Aprobar revisión</button>
                            </form>
                        <?php else: ?>
                            <span class="small muted">Pendiente de recepción física.</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$pendingReviews): ?><tr><td colspan="7" class="muted">No hay devoluciones pendientes.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

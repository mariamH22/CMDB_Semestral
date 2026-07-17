<section class="section-header">
    <div>
        <p class="eyebrow">Portal del Colaborador</p>
        <h1>Mis equipos bajo responsabilidad</h1>
        <p>Consulte sus activos asignados, solicite necesidades y revise el historial de accesos.</p>
    </div>
    <a class="btn btn-light" href="<?= url('portal/password') ?>">Cambiar contraseña</a>
</section>

<section class="card">
    <h2>Activos asignados</h2>
    <?php if ($equipment): ?>
        <div class="grid grid-3">
            <?php foreach ($equipment as $asset): ?>
                <article class="card">
                    <?php if ($asset['imagen_principal']): ?><img class="image-large asset-card-image" src="<?= url($asset['imagen_principal']) ?>" alt="Activo" loading="lazy"><?php endif; ?>
                    <h3><?= e($asset['nombre']) ?></h3>
                    <p class="small muted"><?= e($asset['codigo_activo']) ?> · <?= e($asset['marca'] . ' ' . $asset['modelo']) ?></p>
                    <p><strong>Serie:</strong> <?= e($asset['serie']) ?></p>
                    <p><strong>Desde:</strong> <?= e($asset['fecha_asignacion']) ?></p>
                    <p><strong>IP:</strong> <?= e($asset['ip_asignada'] ?: 'No aplica') ?></p>
                    <p><?= status_badge($asset['estado']) ?></p>
                    <?php if (!empty($supportsFormalReturns) && $asset['estado'] === \App\Core\InventoryStatus::ASIGNADO): ?>
                        <details class="mt-sm">
                            <summary>Solicitar devolución</summary>
                            <form method="post" action="<?= url('portal/returns/store') ?>" class="form-grid mt-sm">
                                <?= csrf_field() ?>
                                <input type="hidden" name="asignacion_id" value="<?= e($asset['id']) ?>">
                                <div class="form-group">
                                    <label>Motivo</label>
                                    <input name="motivo" maxlength="160" required>
                                </div>
                                <div class="form-group full">
                                    <label>Observaciones</label>
                                    <textarea name="observaciones"></textarea>
                                </div>
                                <div class="form-group full">
                                    <label>Evidencia</label>
                                    <input name="evidencia" maxlength="255" placeholder="Referencia, acta o URL interna">
                                </div>
                                <div class="form-group full">
                                    <button class="btn btn-warning btn-small" data-confirm="Se registrará la solicitud y quedará pendiente de recepción física. ¿Continuar?" type="submit">Solicitar devolución</button>
                                </div>
                            </form>
                        </details>
                    <?php elseif (empty($supportsFormalReturns)): ?>
                        <p class="small muted">La devolución requiere aplicar la migración de ciclo de vida formal.</p>
                    <?php else: ?>
                        <p class="small muted">La devolución de este activo ya está en proceso.</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-data">No tiene equipos asignados actualmente.</div>
    <?php endif; ?>
</section>

<?php if (!empty($licenses)): ?>
    <!-- Bloque visible solo si la funcionalidad de licencias por cupo está activa en datos y rutas. -->
    <section class="card section-spaced">
        <p class="eyebrow">Licencias activas</p>
        <h2>Mis cupos de licencia</h2>
        <div class="grid grid-2">
            <?php foreach ($licenses as $license): ?>
                <article class="card">
                    <h3><?= e($license['licencia_nombre']) ?></h3>
                    <p class="small muted"><?= e($license['codigo_activo']) ?> · <?= e($license['marca'] . ' ' . $license['modelo']) ?></p>
                    <p><strong>Proveedor:</strong> <?= e($license['proveedor_licencia'] ?: 'Sin definir') ?></p>
                    <p><strong>Tipo:</strong> <?= e($license['tipo_licencia'] ?? 'Sin definir') ?></p>
                    <p><strong>Asignado:</strong> <?= e((string) $license['cantidad']) ?> de <?= e((string) $license['cupos_totales']) ?> cupos</p>
                    <p><strong>Adquisición:</strong> <?= e($license['fecha_adquisicion_licencia'] ?? 'Sin fecha') ?></p>
                    <p><strong>Vence:</strong> <?= e($license['fecha_vencimiento_licencia'] ?: 'Sin fecha') ?></p>
                    <p><strong>Estado:</strong> <?= status_badge($license['estado_licencia'] ?? 'ACTIVA') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<div class="grid grid-2 section-spaced">
    <section class="card">
        <p class="eyebrow">Nueva solicitud</p>
        <h2>Necesito un equipo, software o licencia</h2>
        <form method="post" action="<?= url('portal/needs/store') ?>" class="form-grid">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="tipo_necesidad">Tipo</label>
                <select id="tipo_necesidad" name="tipo_necesidad">
                    <option value="EQUIPO">Equipo informático</option>
                    <option value="SOFTWARE">Software</option>
                    <option value="LICENCIA">Licencia</option>
                </select>
            </div>
            <div class="form-group">
                <label for="categoria_id">Categoría sugerida</label>
                <select id="categoria_id" name="categoria_id">
                    <option value="0">No especificada</option>
                    <?php foreach ($categories as $category): ?><option value="<?= e($category['id']) ?>"><?= e($category['nombre']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="prioridad">Prioridad</label>
                <select id="prioridad" name="prioridad"><option value="BAJA">Baja</option><option value="MEDIA" selected>Media</option><option value="ALTA">Alta</option></select>
            </div>
            <?php if (!empty($supportsEstimatedCost) || !empty($supportsUnitEstimatedCost)): ?>
                <div class="form-group">
                    <label for="costo_unitario_estimado">Costo unitario estimado</label>
                    <input id="costo_unitario_estimado" type="number" min="0" step="0.01" name="<?= !empty($supportsUnitEstimatedCost) ? 'costo_unitario_estimado' : 'costo_estimado' ?>" value="0">
                </div>
            <?php endif; ?>
            <?php if (!empty($supportsNeedQuantity)): ?>
                <div class="form-group"><label for="cantidad">Cantidad estimada</label><input id="cantidad" type="number" min="1" step="1" name="cantidad" value="1"></div>
            <?php endif; ?>
            <?php if (!empty($supportsAnioObjetivo)): ?>
                <div class="form-group"><label for="anio_objetivo">Año objetivo</label><input id="anio_objetivo" type="number" min="2020" max="2100" name="anio_objetivo" value="<?= e(date('Y')) ?>"></div>
            <?php endif; ?>
            <div class="form-group full">
                <label for="descripcion">Descripción de la necesidad</label>
                <textarea id="descripcion" name="descripcion" required placeholder="Explique qué necesita y para qué actividad."></textarea>
            </div>
            <?php if (!empty($supportsJustification)): ?>
                <div class="form-group full">
                    <label for="justificacion">Justificación</label>
                    <textarea id="justificacion" name="justificacion" required placeholder="Explique el impacto operativo o académico de la solicitud."></textarea>
                </div>
            <?php endif; ?>
            <div class="form-group full"><button class="btn btn-success" type="submit">Enviar solicitud</button></div>
        </form>
    </section>

    <section class="card">
        <p class="eyebrow">Estado de solicitudes</p>
        <h2>Mis necesidades registradas</h2>
        <?php if ($needs): ?>
            <div class="table-wrap">
                <table>
                    <caption>Solicitudes registradas desde su portal.</caption>
                    <thead><tr><th scope="col">Tipo</th><?php if (!empty($supportsNeedQuantity)): ?><th scope="col">Cantidad</th><?php endif; ?><?php if (!empty($supportsAnioObjetivo)): ?><th scope="col">Año objetivo</th><?php endif; ?><th scope="col">Fecha</th><th scope="col">Estado</th></tr></thead>
                    <tbody>
                    <?php foreach ($needs as $need): ?>
                        <tr>
                            <td><?= e($need['tipo_necesidad']) ?><br><span class="small muted"><?= e($need['descripcion']) ?></span><?php if (!empty($need['justificacion'])): ?><br><span class="small muted">Justificación: <?= e($need['justificacion']) ?></span><?php endif; ?></td>
                            <?php if (!empty($supportsNeedQuantity)): ?><td><?= e((string) ($need['cantidad'] ?? 1)) ?></td><?php endif; ?>
                            <?php if (!empty($supportsAnioObjetivo)): ?><td><?= e((string) ($need['anio_objetivo'] ?: '-')) ?></td><?php endif; ?>
                            <td><?= e(date('d/m/Y', strtotime($need['created_at']))) ?></td>
                            <td>
                                <?= status_badge($need['estado']) ?>
                                <?php if (!empty($need['respuesta_administrativa'] ?? $need['comentario_resolucion'] ?? '')): ?>
                                    <br><span class="small muted"><?= e($need['respuesta_administrativa'] ?? $need['comentario_resolucion']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($needHistory[$need['id']] ?? [])): ?>
                                    <details><summary>Historial</summary>
                                        <ul class="list">
                                            <?php foreach ($needHistory[$need['id']] as $history): ?>
                                                <li class="small muted">
                                                    <?= e($history['created_at']) ?>:
                                                    <?= e($history['estado_anterior'] ?: 'Sin estado anterior') ?> -> <?= e($history['estado_nuevo']) ?>
                                                    <?php if (!empty($history['nombre_usuario'])): ?> · <?= e($history['nombre_usuario']) ?><?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </details>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?><div class="no-data">No ha registrado solicitudes.</div><?php endif; ?>
    </section>
</div>

<section class="card section-spaced">
    <p class="eyebrow">Devoluciones</p>
    <h2>Estado de mis devoluciones</h2>
    <?php if (!empty($returns)): ?>
        <div class="table-wrap">
            <table>
                <caption>Seguimiento de devoluciones solicitadas desde el portal.</caption>
                <thead><tr><th scope="col">Activo</th><th scope="col">Motivo</th><th scope="col">Estado físico recibido</th><th scope="col">Solicitud</th><th scope="col">Estado</th><th scope="col">Resultado</th></tr></thead>
                <tbody>
                <?php foreach ($returns as $return): ?>
                    <tr>
                        <td><?= e($return['codigo_activo']) ?><br><span class="small muted"><?= e($return['equipo_nombre']) ?></span></td>
                        <td><?= e($return['motivo']) ?></td>
                        <td><?= e($return['estado_fisico'] ?: 'Pendiente de recepción') ?></td>
                        <td><?= e($return['fecha_devolucion']) ?></td>
                        <td><?= status_badge($return['estado_devolucion']) ?></td>
                        <td>
                            <?= e($return['resultado'] ?: 'Pendiente') ?>
                            <?php if (!empty($return['observacion_tecnica'])): ?><br><span class="small muted"><?= e($return['observacion_tecnica']) ?></span><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-data">No tiene devoluciones registradas.</div>
    <?php endif; ?>
</section>

<section class="card section-spaced">
    <p class="eyebrow">Auditoría del portal</p>
    <h2>Historial de accesos</h2>
    <?php if ($history): ?>
        <div class="table-wrap">
            <table><caption>Últimos accesos registrados en el portal.</caption><thead><tr><th scope="col">Fecha y hora</th><th scope="col">IP</th></tr></thead><tbody>
            <?php foreach ($history as $access): ?><tr><td><?= e($access['accessed_at']) ?></td><td><?= e($access['ip']) ?></td></tr><?php endforeach; ?>
            </tbody></table>
        </div>
    <?php else: ?><div class="no-data">Sin historial de accesos.</div><?php endif; ?>
</section>

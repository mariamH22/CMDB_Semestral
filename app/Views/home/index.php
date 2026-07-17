<section class="hero">
    <div>
        <p class="eyebrow">Mesa operativa CMDB</p>
        <h1>Controle el ciclo de vida de activos, licencias y custodias con trazabilidad.</h1>
        <p>
            CMDB Integral organiza hardware, software, custodias, solicitudes, depreciación
            y reportes en una mesa operativa diseñada para seguimiento administrativo.
        </p>
        <div class="button-row">
            <a class="btn btn-primary" href="<?= url('login') ?>">Acceder al sistema</a>
            <a class="btn btn-light" href="<?= url('news') ?>">Ver noticias</a>
        </div>
    </div>
    <aside class="hero-card">
        <strong>Controles visibles desde la CMDB</strong>
        <ul>
            <li>Custodia de activos por colaborador.</li>
            <li>Estados de inventario y revisión técnica.</li>
            <li>Licencias con cupos asignables.</li>
            <li>Reportes exportables y filtros reales.</li>
            <li>Integridad HMAC para campos críticos.</li>
        </ul>
    </aside>
</section>

<section class="section-header">
    <div>
        <p class="eyebrow">Ciclo de vida del activo</p>
        <h2>De ingreso a cierre operativo, cada estado deja evidencia</h2>
    </div>
</section>

<ol class="process-flow" aria-label="Flujo de vida del activo">
    <li><span>Ingreso</span><small>Registro inicial</small></li>
    <li><span>Inventario</span><small>Clasificación y costo</small></li>
    <li><span>Asignación</span><small>Custodia responsable</small></li>
    <li><span>Devolución</span><small>Motivo y evidencia</small></li>
    <li><span>Revisión</span><small>Diagnóstico técnico</small></li>
    <li><span>Cierre</span><small>Disponible, descarte o donación</small></li>
</ol>

<section class="section-header">
    <div>
        <p class="eyebrow">Qué resuelve</p>
        <h2>Control administrativo sin exponer datos sensibles</h2>
    </div>
</section>

<div class="grid grid-3 mb-md module-grid">
    <article class="card module-card">
        <h3>Inventario confiable</h3>
        <p class="muted">Activos, categorías, series, costos, vida útil, imágenes y QR se gestionan desde una interfaz consistente.</p>
    </article>
    <article class="card module-card">
        <h3>Trazabilidad operativa</h3>
        <p class="muted">Asignaciones, devoluciones, revisiones, descartes y donaciones quedan registradas con bitácora.</p>
    </article>
    <article class="card module-card">
        <h3>Seguridad práctica</h3>
        <p class="muted">El sistema aplica CSRF, PDO, roles, contraseñas con hash y ocultamiento de datos sensibles en pantalla.</p>
    </article>
</div>

<section class="section-header">
    <div>
        <p class="eyebrow">Base de conocimiento operativa</p>
        <h2>Una CMDB conecta activos, personas, estados y decisiones</h2>
    </div>
</section>

<div class="grid grid-2 mb-md">
    <article class="card">
        <h3>Alcance del sistema</h3>
        <p class="muted">La CMDB conserva el inventario tecnológico, custodias, licencias, devoluciones, revisiones, solicitudes, presupuesto y reportes necesarios para operar activos durante todo su ciclo de vida.</p>
    </article>
    <article class="card">
        <h3>Gobierno y seguridad</h3>
        <p class="muted">Los flujos aplican controles OWASP habituales: sesiones protegidas, CSRF, autorización por permisos, consultas PDO preparadas, validación backend, bitácora con hash chain, HMAC de integridad y contratos RSA/HMAC desacoplados.</p>
    </article>
</div>

<section class="section-header">
    <div>
        <p class="eyebrow">Mercadeo y actualización tecnológica</p>
        <h2>Noticias de hardware y software</h2>
    </div>
    <a class="btn btn-primary" href="<?= url('news') ?>">Ver todas</a>
</section>

<?php if ($news): ?>
    <div class="grid grid-3">
        <?php foreach (array_slice($news, 0, 3) as $article): ?>
            <article class="card news-card">
                <?php if ($article['imagen']): ?>
                    <img src="<?= url($article['imagen']) ?>" alt="<?= e($article['titulo']) ?>" loading="lazy">
                <?php endif; ?>
                <p class="eyebrow"><?= e(date('d/m/Y', strtotime($article['created_at']))) ?></p>
                <h3><?= e($article['titulo']) ?></h3>
                <p><?= e($article['resumen']) ?></p>
                <span class="small muted">Publicado por <?= e($article['autor'] ?? 'CMDB Integral') ?></span>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="no-data">Aún no hay noticias publicadas. El administrador puede agregarlas desde el módulo de Noticias.</div>
<?php endif; ?>

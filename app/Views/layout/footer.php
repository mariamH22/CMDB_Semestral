</main>

<?php if (\App\Core\Auth::check()): ?>
        <footer class="app-footer">
            <strong>CMDB Integral</strong>
            <span>Inventario, custodia y trazabilidad operativa.</span>
        </footer>
    </div>
</div>
<?php else: ?>
    <footer class="site-footer">
        <div class="container footer-grid">
            <strong>CMDB Integral</strong>
            <span>Gestión segura de inventario de hardware y software.</span>
            <span class="footer-note">PHP MVC · PDO · CSRF · Bitácora · HMAC</span>
        </div>
    </footer>
<?php endif; ?>

<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>

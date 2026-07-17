<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;
use App\Models\InventoryQr;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
require_once dirname(__DIR__, 2) . '/app/Core/helpers.php';

Config::load(require dirname(__DIR__, 2) . '/app/Config/config.php');

$db = Database::instance();
$qr = new InventoryQr($db);
$marker = 'QA_QR_' . date('YmdHis') . '_' . bin2hex(random_bytes(3));
$categoryId = null;
$inventoryId = null;
$qrId = null;

$ok = static function (string $message): void {
    echo "[OK] {$message}\n";
};

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$cleanup = static function () use ($db, &$categoryId, &$inventoryId): void {
    if ($inventoryId !== null) {
        $db->execute('DELETE FROM inventario_qr WHERE inventario_id = :id', ['id' => $inventoryId]);
        $db->execute('DELETE FROM inventario_estado_historial WHERE inventario_id = :id', ['id' => $inventoryId]);
        $db->execute('DELETE FROM inventario WHERE id = :id', ['id' => $inventoryId]);
    }
    if ($categoryId !== null) {
        $db->execute('DELETE FROM categorias WHERE id = :id', ['id' => $categoryId]);
    }
    $db->execute("DELETE q FROM inventario_qr q INNER JOIN inventario i ON i.id = q.inventario_id WHERE i.codigo_activo LIKE 'QA_QR_%'");
    $db->execute("DELETE FROM inventario_estado_historial WHERE observacion LIKE 'QA_QR_%'");
    $db->execute("DELETE FROM inventario WHERE codigo_activo LIKE 'QA_QR_%'");
    $db->execute("DELETE FROM categorias WHERE nombre LIKE 'QA_QR_%'");
};

try {
    $cleanup();
    $assert($qr->schemaReady(), 'La tabla inventario_qr no esta disponible.');

    $categoryId = $db->insert(
        "INSERT INTO categorias (nombre, tipo, descripcion, activo)
         VALUES (:nombre, 'SOFTWARE', 'Categoria temporal para verificar QR publico.', 1)",
        ['nombre' => $marker . '_Categoria']
    );

    $inventoryId = $db->insert(
        "INSERT INTO inventario
            (categoria_id, codigo_activo, nombre, tipo_activo, subcategoria, marca, modelo, serie, costo, fecha_ingreso, vida_util_meses, estado, es_licencia, clave_licencia, notas, firma_integridad, activo)
         VALUES
            (:categoria_id, :codigo, 'Licencia QA QR', 'SOFTWARE', 'Licencia', 'Marca QR', 'Modelo QR', :serie, 987.65, '2026-02-20', 12, 'DISPONIBLE', 1, 'SECRETO-QA-QR', :notas, :firma, 1)",
        [
            'categoria_id' => $categoryId,
            'codigo' => $marker,
            'serie' => $marker . '_SERIE',
            'notas' => 'Registro temporal para verificar QR publico.',
            'firma' => hash('sha256', $marker),
        ]
    );

    $row = $qr->ensureForInventory($inventoryId);
    $assert(is_array($row) && !empty($row['token']), 'Debe generarse token QR.');
    $qrId = (int) $row['id'];

    $asset = $qr->findPublicAssetByToken((string) $row['token']);
    $assert(is_array($asset), 'El token publico debe resolver un activo.');
    $assert($asset['codigo_activo'] === $marker, 'El QR publico debe mostrar codigo.');
    $assert($asset['nombre'] === 'Licencia QA QR', 'El QR publico debe mostrar nombre.');
    $assert($asset['categoria_nombre'] === $marker . '_Categoria', 'El QR publico debe mostrar categoria.');
    $assert($asset['marca'] === 'Marca QR', 'El QR publico debe mostrar marca.');
    $assert($asset['estado'] === 'DISPONIBLE', 'El QR publico debe mostrar estado.');
    $assert((float) $asset['costo'] === 987.65, 'El QR publico debe mostrar precio/costo.');
    $assert($asset['fecha_ingreso'] === '2026-02-20', 'El QR publico debe mostrar fecha de adquisicion.');
    $assert(!array_key_exists('clave_licencia', $asset), 'El QR publico no debe exponer claves.');
    $assert(!array_key_exists('token', $asset), 'El QR publico no debe exponer token.');
    $ok('Payload publico incluye codigo, nombre, categoria, marca, estado, precio y fecha de adquisicion sin secretos.');

    $before = $db->fetch('SELECT access_count FROM inventario_qr WHERE id = :id', ['id' => $qrId]);
    $qr->recordAccess($qrId);
    $after = $db->fetch('SELECT access_count, last_accessed_at FROM inventario_qr WHERE id = :id', ['id' => $qrId]);
    $assert((int) ($after['access_count'] ?? 0) === (int) ($before['access_count'] ?? 0) + 1, 'El acceso publico debe incrementar contador.');
    $assert(!empty($after['last_accessed_at']), 'El acceso publico debe registrar fecha de acceso.');
    $ok('Acceso publico registra contador y fecha.');

    $qr->revokeForInventory($inventoryId, null, 'Verificacion QA QR');
    $assert($qr->findPublicAssetByToken((string) $row['token']) === null, 'Token revocado no debe resolver activo.');
    $ok('Token revocado queda bloqueado.');

    echo "\nVerificacion completa: QR publico cumple campos academicos y controles de seguridad en base real.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "[ERROR] {$exception->getMessage()}\n");
    exit(1);
} finally {
    $cleanup();
}

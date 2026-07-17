<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;
use App\Core\Exceptions\ValidationException;
use App\Core\InventoryImagePolicy;
use App\Core\InventoryStatus;
use App\Models\InventoryItem;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
require_once dirname(__DIR__, 2) . '/app/Core/helpers.php';

Config::load(require dirname(__DIR__, 2) . '/app/Config/config.php');

$db = Database::instance();
$inventory = new InventoryItem($db);
$marker = 'QA_IMG_' . date('YmdHis') . '_' . bin2hex(random_bytes(3));
$categoryId = null;
$inventoryId = null;

$ok = static function (string $message): void {
    echo "[OK] {$message}\n";
};

$cleanup = static function () use ($db, &$categoryId, &$inventoryId): void {
    if ($inventoryId !== null) {
        $db->execute('DELETE FROM inventario_estado_historial WHERE inventario_id = :id', ['id' => $inventoryId]);
        $db->execute('DELETE FROM inventario_imagenes WHERE inventario_id = :id', ['id' => $inventoryId]);
        $db->execute('DELETE FROM inventario WHERE id = :id', ['id' => $inventoryId]);
    }
    if ($categoryId !== null) {
        $db->execute('DELETE FROM categorias WHERE id = :id', ['id' => $categoryId]);
    }
    $db->execute("DELETE FROM inventario_estado_historial WHERE observacion LIKE 'QA_IMG_%'");
    $db->execute("DELETE ii FROM inventario_imagenes ii INNER JOIN inventario i ON i.id = ii.inventario_id WHERE i.codigo_activo LIKE 'QA_IMG_%'");
    $db->execute("DELETE FROM inventario WHERE codigo_activo LIKE 'QA_IMG_%'");
    $db->execute("DELETE FROM categorias WHERE nombre LIKE 'QA_IMG_%'");
};

$baseData = static function (int $categoryId, string $marker): array {
    return [
        'categoria_id' => $categoryId,
        'codigo_activo' => $marker,
        'nombre' => 'Hardware QA imagenes',
        'tipo_activo' => 'HARDWARE',
        'subcategoria' => 'Prueba',
        'marca' => 'QA',
        'modelo' => 'IMG',
        'serie' => $marker . '_SERIE',
        'costo' => 10.00,
        'fecha_ingreso' => date('Y-m-d'),
        'vida_util_meses' => 36,
        'estado' => InventoryStatus::DISPONIBLE,
        'notas' => 'Verificacion temporal de politica de imagenes.',
        'firma_integridad' => hash('sha256', $marker),
        'activo' => 1,
    ];
};

$expectValidation = static function (callable $callback, string $message): void {
    try {
        $callback();
    } catch (ValidationException) {
        echo "[OK] {$message}\n";
        return;
    }

    throw new RuntimeException($message);
};

try {
    $cleanup();
    $categoryId = $db->insert(
        "INSERT INTO categorias (nombre, tipo, descripcion, activo)
         VALUES (:nombre, 'HARDWARE', 'Categoria temporal para verificar imagenes.', 1)",
        ['nombre' => $marker . '_Categoria']
    );

    $expectValidation(
        fn () => $inventory->create($baseData($categoryId, $marker . '_CERO')),
        'Hardware con cero imagenes rechazado antes de persistir.'
    );

    $oneImage = $baseData($categoryId, $marker . '_UNO');
    $oneImage['imagen_principal'] = 'uploads/equipment/' . strtolower($marker) . '_principal.png';
    $expectValidation(
        fn () => $inventory->create($oneImage),
        'Hardware con una sola imagen rechazado antes de persistir.'
    );

    $twoImages = $baseData($categoryId, $marker . '_DOS');
    $twoImages['imagen_principal'] = 'uploads/equipment/' . strtolower($marker) . '_principal.png';
    $twoImages['thumbnail'] = 'uploads/equipment/thumb_' . strtolower($marker) . '_principal.png';
    $twoImages['new_image_path'] = 'uploads/equipment/' . strtolower($marker) . '_extra.png';
    $inventoryId = $inventory->create($twoImages);
    $inventory->addImage($inventoryId, $twoImages['new_image_path'], false);

    $item = $inventory->find($inventoryId);
    if (!$item || InventoryImagePolicy::existingImageCount($item) < 2) {
        throw new RuntimeException('La base no conservo las dos imagenes del hardware.');
    }

    $ok('Hardware con dos imagenes fue persistido y consultado con dos rutas en MySQL real.');
    echo "\nVerificacion completa: politica de dos imagenes para hardware cumple en base real.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "[ERROR] {$exception->getMessage()}\n");
    exit(1);
} finally {
    $cleanup();
}

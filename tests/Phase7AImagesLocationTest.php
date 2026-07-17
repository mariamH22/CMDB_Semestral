<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Core\Exceptions\ValidationException;
use App\Core\InventoryImagePolicy;
use App\Core\Validator;
use App\Models\Collaborator;

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$expectException = static function (callable $callback, string $message): void {
    try {
        $callback();
    } catch (ValidationException) {
        return;
    }

    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$imageFile = static function (string $name): array {
    $tmp = tempnam(sys_get_temp_dir(), 'cmdb-img-');
    if (function_exists('imagecreatetruecolor') && function_exists('imagepng')) {
        $image = imagecreatetruecolor(32, 32);
        imagefilledrectangle($image, 0, 0, 31, 31, imagecolorallocate($image, 20, 92, 110));
        imagepng($image, $tmp);
        imagedestroy($image);
    } else {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAAAAAA6fptVAAAACklEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=');
        file_put_contents($tmp, $png);
    }

    return [
        'name' => $name,
        'type' => 'image/png',
        'tmp_name' => $tmp,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($tmp),
    ];
};

$valid = $imageFile('activo.png');
Validator::image($valid);
$assert(is_file($valid['tmp_name']), 'La imagen de prueba debe existir.');

$badExtension = $imageFile('activo.txt');
$expectException(static fn () => Validator::image($badExtension), 'Debe rechazar extensiones no permitidas.');

$doubleExtension = $imageFile('shell.php.png');
$expectException(static fn () => Validator::image($doubleExtension), 'Debe rechazar doble extensión peligrosa.');

$hardware = ['tipo_activo' => 'HARDWARE', 'es_licencia' => 0];
$software = ['tipo_activo' => 'SOFTWARE', 'es_licencia' => 0];
$main = $imageFile('principal.png');
$extra = $imageFile('extra.png');

InventoryImagePolicy::assertNewHardwareImages($hardware, [
    InventoryImagePolicy::MAIN_FIELD => $main,
    InventoryImagePolicy::EXTRA_FIELD => $extra,
]);
$expectException(
    static fn () => InventoryImagePolicy::assertNewHardwareImages($hardware, [InventoryImagePolicy::MAIN_FIELD => $main]),
    'Hardware nuevo debe rechazar una sola imagen.'
);
$expectException(
    static fn () => InventoryImagePolicy::assertNewHardwareImages($hardware, []),
    'Hardware nuevo debe rechazar cero imágenes.'
);

InventoryImagePolicy::assertNewHardwareImages($software, []);

InventoryImagePolicy::assertPersistedHardwareImages([
    'tipo_activo' => 'HARDWARE',
    'imagen_principal' => 'uploads/equipment/principal.png',
    'new_image_path' => 'uploads/equipment/extra.png',
]);
$expectException(
    static fn () => InventoryImagePolicy::assertPersistedHardwareImages([
        'tipo_activo' => 'HARDWARE',
        'imagen_principal' => 'uploads/equipment/principal.png',
    ]),
    'El modelo no debe persistir hardware con una sola imagen.'
);

$warning = InventoryImagePolicy::legacyWarning([
    'tipo_activo' => 'HARDWARE',
    'es_licencia' => 0,
    'imagenes' => [['ruta' => 'uploads/equipment/uno.png']],
]);
$assert($warning !== null, 'Hardware sin dos imágenes debe mostrar advertencia.');
$assert(InventoryImagePolicy::legacyWarning(['tipo_activo' => 'SOFTWARE', 'imagenes' => []]) === null, 'Software no debe exigir dos imágenes.');

InventoryImagePolicy::assertExistingHardwareImages($hardware, [], [
    'tipo_activo' => 'HARDWARE',
    'imagen_principal' => 'uploads/equipment/uno.png',
    'imagenes' => [['ruta' => 'uploads/equipment/dos.png']],
]);
$expectException(
    static fn () => InventoryImagePolicy::assertExistingHardwareImages($hardware, [], [
        'tipo_activo' => 'HARDWARE',
        'imagen_principal' => 'uploads/equipment/uno.png',
        'imagenes' => [],
    ]),
    'Hardware editado no debe quedar con menos de dos imágenes.'
);
InventoryImagePolicy::assertExistingHardwareImages($hardware, [InventoryImagePolicy::EXTRA_FIELD => $extra], [
    'tipo_activo' => 'HARDWARE',
    'imagen_principal' => 'uploads/equipment/uno.png',
    'imagenes' => [],
]);

$controllerSource = (string) file_get_contents(dirname(__DIR__) . '/app/Core/Controller.php');
$assert(!str_contains($controllerSource, "'thumbnail' => \$relative"), 'No debe reutilizar la imagen original como miniatura.');
$assert(str_contains($controllerSource, 'La extensión GD es obligatoria para generar miniaturas reales.'), 'Debe fallar si GD no puede generar miniaturas.');

$inventoryControllerSource = (string) file_get_contents(dirname(__DIR__) . '/app/Controllers/InventoryController.php');
$assert(str_contains($inventoryControllerSource, '$inventoryPersisted = false;'), 'Inventario debe rastrear si la BD confirmo la persistencia.');
$assert(str_contains($inventoryControllerSource, 'if (!$inventoryPersisted)'), 'Debe limpiar archivos subidos solo si la persistencia aun no fue confirmada.');
$assert(str_contains($inventoryControllerSource, '$this->cleanupUploadedImages($uploadedImages);'), 'Debe eliminar imagenes subidas cuando falla la persistencia.');

$formSource = (string) file_get_contents(dirname(__DIR__) . '/app/Views/inventory/form.php');
$assert(str_contains($formSource, '$hardwareImagesRequired'), 'El formulario debe marcar imágenes requeridas para altas de hardware.');
$assert(str_contains($formSource, 'Hardware requiere dos imágenes'), 'El formulario debe comunicar que hardware requiere dos imágenes.');

$assert(Collaborator::normalizeLocation('Edificio 303 - Oficina 12') === 'OFICINA', 'Debe clasificar oficina.');
$assert(Collaborator::normalizeLocation('Casa 257') === 'CASA', 'Debe clasificar casa.');
$assert(Collaborator::normalizeLocation('') === 'OTRO', 'Ubicación vacía se clasifica como OTRO si se necesita tipo.');

foreach ([$valid, $badExtension, $doubleExtension, $main, $extra] as $file) {
    if (is_file($file['tmp_name'])) {
        unlink($file['tmp_name']);
    }
}

echo "OK Phase7AImagesLocationTest\n";

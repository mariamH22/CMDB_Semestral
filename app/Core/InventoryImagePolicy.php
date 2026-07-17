<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\ValidationException;

final class InventoryImagePolicy
{
    public const MAIN_FIELD = 'imagen_principal';
    public const EXTRA_FIELD = 'imagen_adicional';
    public const MIN_NEW_HARDWARE_IMAGES = 2;
    public const MAX_IMAGES_PER_REQUEST = 2;

    public static function assertNewHardwareImages(array $data, array $files): void
    {
        $count = self::uploadedCount($files, [self::MAIN_FIELD, self::EXTRA_FIELD]);
        if ($count > self::MAX_IMAGES_PER_REQUEST) {
            throw new ValidationException('Solo se permiten dos imágenes por registro desde este formulario.');
        }

        if (self::requiresTwoImages($data) && $count < self::MIN_NEW_HARDWARE_IMAGES) {
            throw new ValidationException('Los activos de hardware deben registrarse con mínimo dos imágenes.');
        }
    }

    public static function assertExistingHardwareImages(array $data, array $files, ?array $current): void
    {
        $uploaded = self::uploadedCount($files, [self::MAIN_FIELD, self::EXTRA_FIELD]);
        if ($uploaded > self::MAX_IMAGES_PER_REQUEST) {
            throw new ValidationException('Solo se permiten dos imágenes por actualización desde este formulario.');
        }

        if (!self::requiresTwoImages($data)) {
            return;
        }

        $currentCount = self::existingImageCount($current ?? []);
        if ($currentCount + $uploaded < self::MIN_NEW_HARDWARE_IMAGES) {
            throw new ValidationException('Los activos de hardware deben conservar mínimo dos imágenes.');
        }
    }

    public static function legacyWarning(array $item): ?string
    {
        if (!self::requiresTwoImages($item)) {
            return null;
        }

        return self::existingImageCount($item) < self::MIN_NEW_HARDWARE_IMAGES
            ? 'Este activo de hardware necesita mínimo dos imágenes para cumplir la política actual.'
            : null;
    }

    public static function assertPersistedHardwareImages(array $data): void
    {
        if (self::requiresTwoImages($data) && self::existingImageCount($data) < self::MIN_NEW_HARDWARE_IMAGES) {
            throw new ValidationException('Los activos de hardware deben guardarse con mínimo dos imágenes.');
        }
    }

    public static function requiresTwoImages(array $data): bool
    {
        return strtoupper((string) ($data['tipo_activo'] ?? '')) === 'HARDWARE';
    }

    public static function uploadedCount(array $files, array $fields): int
    {
        $count = 0;

        foreach ($fields as $field) {
            if (!empty($files[$field]) && self::hasUploadedFile($files[$field])) {
                $count++;
            }
        }

        return $count;
    }

    private static function hasUploadedFile(array $file): bool
    {
        return ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    public static function existingImageCount(array $item): int
    {
        $paths = [];
        if (!empty($item['imagen_principal'])) {
            $paths[] = (string) $item['imagen_principal'];
        }
        if (!empty($item['new_image_path'])) {
            $paths[] = (string) $item['new_image_path'];
        }

        foreach (($item['imagenes'] ?? []) as $image) {
            if (!empty($image['ruta'])) {
                $paths[] = (string) $image['ruta'];
            }
        }

        return count(array_unique(array_filter($paths)));
    }
}

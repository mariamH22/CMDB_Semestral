<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\ValidationException;

final class Validator
{
    public static function optional(string $value = ''): ?string
    {
        // Permite campos opcionales: cuando llega vacío, regresa null.
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    public static function required(string $value, string $field): string
    {
        if (trim($value) === '') {
            throw new ValidationException("El campo {$field} es obligatorio.");
        }

        return $value;
    }

    public static function email(string $email): string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('El correo electrónico no es válido.');
        }

        return $email;
    }

    public static function password(string $password): string
    {
        $min = (int) Config::get('security.password_min_length', 8);
        $max = (int) Config::get('security.password_max_length', 64);

        $length = self::stringLength($password);
        if ($length < $min || $length > $max) {
            throw new ValidationException("La contraseña debe contener entre {$min} y {$max} caracteres.");
        }

        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password)
            || !preg_match('/\d/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
            throw new ValidationException('La contraseña debe incluir mayúscula, minúscula, número y carácter especial.');
        }

        return $password;
    }

    public static function positiveNumber(float $number, string $field): float
    {
        if ($number < 0) {
            throw new ValidationException("El campo {$field} no puede ser negativo.");
        }

        return $number;
    }

    public static function integerRange(int $number, int $min, int $max, string $field): int
    {
        if ($number < $min || $number > $max) {
            throw new ValidationException("El campo {$field} debe estar entre {$min} y {$max}.");
        }

        return $number;
    }

    public static function date(string $value, string $field): string
    {
        $value = self::required(trim($value), $field);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new ValidationException("El campo {$field} debe tener una fecha válida en formato YYYY-MM-DD.");
        }

        return $value;
    }

    public static function optionalDate(string $value, string $field): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return self::date($value, $field);
    }

    public static function optionalUrl(string $value, string $field): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            throw new ValidationException("El campo {$field} debe ser una URL válida.");
        }

        return $value;
    }

    public static function image(array $file, bool $required = false): void
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            if ($required) {
                throw new ValidationException('Debe seleccionar una imagen.');
            }
            return;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new ValidationException('No fue posible subir la imagen.');
        }

        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            throw new ValidationException('La imagen no puede superar los 2 MB.');
        }

        self::assertImageExtension($file);

        // finfo lee el contenido real del archivo; el navegador puede enviar MIME falso.
        $mime = self::imageMime($file);

        if (!in_array($mime, self::allowedImageMimes(), true)) {
            throw new ValidationException('Solo se permiten imágenes JPG, PNG o WEBP.');
        }

        // getimagesize confirma que el binario corresponde a una imagen procesable.
        $dimensions = @getimagesize($file['tmp_name']);
        if ($dimensions === false) {
            throw new ValidationException('El archivo no contiene una imagen válida.');
        }

        self::assertImageDimensions((int) $dimensions[0], (int) $dimensions[1]);
        self::assertImageDecodable((string) $file['tmp_name'], $mime);
    }

    public static function imageMime(array $file): string
    {
        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_file($tmpName)) {
            throw new ValidationException('No fue posible validar la imagen.');
        }

        // Evita depender de mime_content_type, que puede variar entre entornos PHP.
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpName);

        if (!is_string($mime)) {
            throw new ValidationException('No fue posible validar el tipo de imagen.');
        }

        return $mime;
    }

    private static function allowedImageMimes(): array
    {
        return ['image/jpeg', 'image/png', 'image/webp'];
    }

    private static function allowedImageExtensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'webp'];
    }

    private static function assertImageExtension(array $file): void
    {
        $name = (string) ($file['name'] ?? '');
        $basename = basename($name);
        $extension = strtolower((string) pathinfo($basename, PATHINFO_EXTENSION));

        if ($extension === '' || !in_array($extension, self::allowedImageExtensions(), true)) {
            throw new ValidationException('La extensión de imagen no es válida.');
        }

        $parts = explode('.', strtolower($basename));
        $dangerous = ['php', 'phtml', 'phar', 'html', 'htm', 'js', 'svg', 'exe', 'sh', 'bat', 'cmd', 'pl', 'py', 'cgi'];
        foreach (array_slice($parts, 0, -1) as $part) {
            if (in_array($part, $dangerous, true)) {
                throw new ValidationException('No se permiten archivos con doble extensión peligrosa.');
            }
        }
    }

    private static function assertImageDimensions(int $width, int $height): void
    {
        if ($width < 1 || $height < 1) {
            throw new ValidationException('La imagen no tiene dimensiones válidas.');
        }

        if ($width > 8000 || $height > 8000) {
            throw new ValidationException('La imagen supera las dimensiones máximas permitidas.');
        }
    }

    private static function assertImageDecodable(string $path, string $mime): void
    {
        $factory = match ($mime) {
            'image/jpeg' => function_exists('imagecreatefromjpeg') ? 'imagecreatefromjpeg' : null,
            'image/png' => function_exists('imagecreatefrompng') ? 'imagecreatefrompng' : null,
            'image/webp' => function_exists('imagecreatefromwebp') ? 'imagecreatefromwebp' : null,
            default => null,
        };

        if ($factory === null) {
            throw new ValidationException('La extensión GD es obligatoria para validar y procesar imágenes.');
        }

        $image = @$factory($path);
        if (!$image) {
            throw new ValidationException('La imagen no pudo decodificarse correctamente.');
        }

        imagedestroy($image);
    }

    private static function stringLength(string $value): int
    {
        return function_exists('mb_strlen') ? \mb_strlen($value) : strlen($value);
    }
}

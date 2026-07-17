<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\DatabaseException;
use App\Core\Exceptions\ValidationException;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function redirect(string $path): never
    {
        Response::redirect($path);
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            flash('error', 'Debe iniciar sesión para continuar.');
            $this->redirect('login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->authorize('system.admin');
    }

    protected function requireInternal(): void
    {
        $this->authorize('dashboard.view');
    }

    protected function authorize(string $permission): void
    {
        $this->requireAuth();

        // Las reglas de rol viven en Authorization para mantener los controladores simples.
        if (!Auth::can($permission)) {
            flash('error', 'No tiene permisos para acceder a este módulo.');
            $this->redirect(Auth::isCollaborator() ? 'portal' : 'dashboard');
        }
    }

    protected function requireCollaborator(): void
    {
        $this->requireAuth();

        if (!Auth::can('portal.view')) {
            flash('error', 'Esta sección corresponde al Portal del Colaborador.');
            $this->redirect('dashboard');
        }
    }

    protected function csrf(): void
    {
        Csrf::validate($_POST['csrf_token'] ?? null);
    }

    protected function formError(\Throwable $exception, string $path): never
    {
        if ($exception instanceof HttpException) {
            throw $exception;
        }

        // Solo se muestran mensajes controlados; errores tecnicos quedan para el log global.
        $message = $exception instanceof ValidationException || $exception instanceof \RuntimeException
            ? $exception->getMessage()
            : 'No fue posible completar la operación. Intente nuevamente o contacte al administrador.';
        if ($exception instanceof DatabaseException) {
            $message = $this->databaseErrorMessage($exception);
        }

        $this->rememberOldInput();
        flash('error', $message);
        $this->redirect($path);
    }

    private function databaseErrorMessage(DatabaseException $exception): string
    {
        $technical = $exception->getPrevious()?->getMessage() ?? $exception->getMessage();

        if (str_contains($technical, 'Duplicate entry')) {
            return 'Ya existe un registro con ese código, serie, correo u otro valor único. Revise los datos e intente nuevamente.';
        }

        if (str_contains($technical, 'Unknown column') || str_contains($technical, "doesn't exist")) {
            return 'La base de datos local no tiene todas las columnas o tablas requeridas. Aplique las migraciones pendientes o use el script de instalación actualizado.';
        }

        return 'No fue posible guardar los datos. Revise que la base de datos esté actualizada e intente nuevamente.';
    }

    private function rememberOldInput(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || empty($_POST)) {
            return;
        }

        $old = [];
        foreach ($_POST as $key => $value) {
            if ($this->isSensitiveOldInputKey((string) $key)) {
                continue;
            }

            $old[$key] = $this->normalizeOldInputValue($value);
        }

        $_SESSION['_old_input'] = $old;
    }

    private function isSensitiveOldInputKey(string $key): bool
    {
        $normalized = strtolower($key);

        return $normalized === 'csrf_token'
            || str_contains($normalized, 'password')
            || str_contains($normalized, 'contrasena')
            || str_contains($normalized, 'contraseña')
            || str_contains($normalized, 'token')
            || str_contains($normalized, 'clave');
    }

    private function normalizeOldInputValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->normalizeOldInputValue($item), $value);
        }

        if (is_scalar($value) || $value === null) {
            return (string) $value;
        }

        return '';
    }

    protected function uploadImage(string $field, string $folder): ?array
    {
        $file = $_FILES[$field] ?? null;
        if (!$file) {
            return null;
        }

        Validator::image($file);
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        // Se reutiliza el MIME validado con finfo para no confiar en la extension del archivo.
        $mime = Validator::imageMime($file);
        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new ValidationException('Tipo de imagen no permitido.')
        };

        $name = bin2hex(random_bytes(12)) . '.' . $extension;
        $relative = 'uploads/' . trim($folder, '/') . '/' . $name;
        $destination = dirname(__DIR__, 2) . '/public/' . $relative;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new ValidationException('No fue posible guardar la imagen.');
        }

        $thumbRelative = 'uploads/' . trim($folder, '/') . '/thumb_' . $name;
        $thumbDestination = dirname(__DIR__, 2) . '/public/' . $thumbRelative;

        try {
            $this->createThumbnail($destination, $thumbDestination, $mime);
        } catch (\Throwable $exception) {
            if (is_file($destination)) {
                @unlink($destination);
            }
            throw $exception;
        }

        return ['path' => $relative, 'thumbnail' => $thumbRelative];
    }

    private function createThumbnail(string $source, string $target, string $mime): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            throw new ValidationException('La extensión GD es obligatoria para generar miniaturas reales.');
        }

        $image = match ($mime) {
            'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($source) : null,
            'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($source) : null,
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : null,
            default => null
        };

        if (!$image) {
            throw new ValidationException('No fue posible decodificar la imagen para generar la miniatura.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $targetWidth = 280;
        $targetHeight = max(1, (int) (($height / $width) * $targetWidth));

        $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $saved = match ($mime) {
            'image/jpeg' => imagejpeg($thumbnail, $target, 85),
            'image/png' => imagepng($thumbnail, $target, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($thumbnail, $target, 85) : false,
            default => false
        };

        imagedestroy($image);
        imagedestroy($thumbnail);

        if (!$saved || !is_file($target)) {
            throw new ValidationException('No fue posible generar una miniatura redimensionada.');
        }
    }
}

<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Contracts\ErrorRendererInterface;
use App\Core\Exceptions\HttpException;
use App\Core\Exceptions\ValidationException;

final class WebErrorRenderer implements ErrorRendererInterface
{
    public function render(\Throwable $exception): never
    {
        $status = $exception instanceof HttpException
            ? $exception->statusCode()
            : ($exception instanceof ValidationException ? 422 : 500);

        http_response_code($status);

        // Los errores no controlados no deben revelar SQL, rutas locales ni stack traces.
        $message = $exception instanceof ValidationException || $exception instanceof HttpException
            ? $exception->getMessage()
            : 'Ocurrió un error inesperado. Intente nuevamente o contacte al administrador.';

        $title = match ($status) {
            403 => 'Acceso denegado',
            404 => 'Página no encontrada',
            419 => 'Solicitud expirada',
            default => $status >= 500 ? 'Error del sistema' : 'Solicitud no procesada',
        };

        $view = in_array($status, [403, 404, 419, 500], true)
            ? 'errors/' . $status
            : 'errors/error';

        View::render($view, [
            'title' => $title,
            'message' => $message,
            'exception' => $exception,
        ]);

        exit;
    }
}

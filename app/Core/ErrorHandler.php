<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Contracts\ErrorRendererInterface;

final class ErrorHandler
{
    public static function register(ErrorRendererInterface $renderer): void
    {
        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(static function (\Throwable $exception) use ($renderer): void {
            self::log($exception);
            $renderer->render($exception);
        });
    }

    private static function log(\Throwable $exception): void
    {
        $directory = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $line = sprintf(
            "[%s] %s: %s in %s:%d\n",
            date('Y-m-d H:i:s'),
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        error_log($line, 3, $directory . '/app.log');
    }
}

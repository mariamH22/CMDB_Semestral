<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        $viewFile = dirname(__DIR__) . '/Views/' . $view . '.php';

        if (!is_file($viewFile)) {
            throw new \RuntimeException("La vista {$view} no existe.");
        }

        extract($data, EXTR_SKIP);

        try {
            require dirname(__DIR__) . '/Views/layout/header.php';
            require dirname(__DIR__) . '/Views/layout/flash.php';
            require $viewFile;
            require dirname(__DIR__) . '/Views/layout/footer.php';
        } finally {
            unset($_SESSION['_old_input']);
        }
    }
}

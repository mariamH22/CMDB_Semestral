<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\HttpException;

final class Router
{
    private array $routes = [];
    private $controllerFactory;

    public function __construct(?callable $controllerFactory = null)
    {
        $this->controllerFactory = $controllerFactory ?? static function (string $class): object {
            return new $class(ModelFactory::default());
        };
    }

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $this->routes[$method][rtrim($path, '/') ?: '/'] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = rtrim((string) Config::get('app.base_path', ''), '/');

        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }

        $path = rtrim($path, '/') ?: '/';
        // HEAD debe resolver como GET para que las comprobaciones de cabeceras no den falso 404.
        $lookupMethod = $method === 'HEAD' ? 'GET' : $method;
        $handler = $this->routes[$lookupMethod][$path] ?? null;

        if (!$handler) {
            throw new HttpException(404, 'La ruta solicitada no existe.');
        }

        [$class, $action] = $handler;
        $controller = ($this->controllerFactory)($class);
        $controller->$action();
    }
}

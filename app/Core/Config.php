<?php
declare(strict_types=1);

namespace App\Core;

final class Config
{
    private static array $data = [];

    public static function load(array $data): void
    {
        // La configuracion base puede ser sobrescrita por config.local.php sin versionar.
        self::$data = $data;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        // Acceso por puntos: Config::get('db.host') lee ['db']['host'].
        $segments = explode('.', $key);
        $value = self::$data;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}

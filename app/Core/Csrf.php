<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exceptions\CsrfException;

final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KEY];
    }

    public static function validate(?string $token): void
    {
        $stored = $_SESSION[self::KEY] ?? '';

        if (!is_string($token) || !hash_equals($stored, $token)) {
            throw new CsrfException();
        }
    }
}

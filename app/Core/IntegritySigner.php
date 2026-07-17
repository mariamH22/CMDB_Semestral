<?php

declare(strict_types=1);

namespace App\Core;

final class IntegritySigner
{
    public static function isConfigured(): bool
    {
        return ServiceContainer::integritySigner()->isConfigured();
    }

    public static function sign(array $fields): string
    {
        // Firma HMAC de campos criticos del activo para detectar cambios fuera del flujo normal.
        return ServiceContainer::integritySigner()->sign($fields);
    }

    public static function verify(array $fields, ?string $signature): bool
    {
        if (!$signature || !ServiceContainer::integritySigner()->isConfigured()) {
            return false;
        }

        return ServiceContainer::integritySigner()->verify($fields, $signature);
    }
}

<?php
declare(strict_types=1);

namespace App\Core;

final class LicensePolicy
{
    public const ACTIVA = 'ACTIVA';
    public const INACTIVA = 'INACTIVA';
    public const VENCIDA = 'VENCIDA';

    public static function statuses(): array
    {
        return [self::ACTIVA, self::INACTIVA, self::VENCIDA];
    }

    public static function available(int $total, int $assigned): int
    {
        return max(0, $total - $assigned);
    }

    public static function assertQuantity(int $quantity, int $total, int $assigned): void
    {
        if ($quantity < 1) {
            throw new \RuntimeException('La cantidad de licencia debe ser mayor que cero.');
        }

        if ($total < 1) {
            throw new \RuntimeException('La licencia debe tener una cantidad total mayor que cero.');
        }

        if ($quantity > self::available($total, $assigned)) {
            throw new \RuntimeException('No hay cupos suficientes para esa licencia.');
        }
    }

    public static function assertAssignable(?string $expirationDate, string $status, bool $authorized = false, ?string $today = null): void
    {
        $status = strtoupper(trim($status));
        if (!in_array($status, self::statuses(), true)) {
            throw new \RuntimeException('Estado de licencia inválido.');
        }

        $today ??= date('Y-m-d');
        $expired = $expirationDate !== null && trim($expirationDate) !== '' && $expirationDate < $today;

        if (!$authorized && ($status !== self::ACTIVA || $expired)) {
            throw new \RuntimeException('No se puede asignar una licencia vencida o inactiva sin autorización.');
        }
    }
}

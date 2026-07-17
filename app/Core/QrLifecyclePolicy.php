<?php
declare(strict_types=1);

namespace App\Core;

final class QrLifecyclePolicy
{
    public static function isActive(array $qr): bool
    {
        $status = strtoupper((string) ($qr['estado'] ?? 'ACTIVO'));

        return (int) ($qr['activo'] ?? 0) === 1
            && empty($qr['revoked_at'])
            && $status !== 'REVOCADO';
    }

    public static function assertActive(array $qr): void
    {
        if (!self::isActive($qr)) {
            throw new \RuntimeException('El QR está revocado o inactivo.');
        }
    }

    public static function assertBelongsToInventory(array $qr, int $inventoryId): void
    {
        if ((int) ($qr['inventario_id'] ?? 0) !== $inventoryId) {
            throw new \RuntimeException('El token QR no corresponde al activo solicitado.');
        }
    }
}

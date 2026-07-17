<?php
declare(strict_types=1);

namespace App\Core;

final class QrPublicPayload
{
    private const PUBLIC_FIELDS = [
        'codigo_activo',
        'nombre',
        'categoria_nombre',
        'marca',
        'estado',
        'costo',
        'fecha_ingreso',
    ];

    public static function fromAsset(array $asset): array
    {
        $payload = [];
        foreach (self::PUBLIC_FIELDS as $field) {
            $payload[$field] = $asset[$field] ?? null;
        }

        return $payload;
    }

    public static function hasOnlyPublicFields(array $payload): bool
    {
        $allowed = array_flip(self::PUBLIC_FIELDS);

        return array_diff_key($payload, $allowed) === [];
    }
}

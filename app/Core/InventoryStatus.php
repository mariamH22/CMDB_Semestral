<?php
declare(strict_types=1);

namespace App\Core;

final class InventoryStatus
{
    public const DISPONIBLE = 'DISPONIBLE';
    public const ASIGNADO = 'ASIGNADO';
    public const DEVOLUCION_REGISTRADA = 'DEVOLUCION_REGISTRADA';
    public const REVISION_TECNICA = 'REVISION_TECNICA';
    public const EN_REPARACION = 'EN_REPARACION';
    public const DANADO = 'DANADO';
    public const DESCARTE = 'DESCARTE';
    public const DONADO = 'DONADO';

    // Estado legado conservado para instalaciones que aun no migran el ENUM.
    public const MANTENIMIENTO = 'MANTENIMIENTO';

    private const TRANSITIONS = [
        self::DISPONIBLE => [self::ASIGNADO, self::DANADO, self::EN_REPARACION],
        self::ASIGNADO => [self::DEVOLUCION_REGISTRADA],
        self::DEVOLUCION_REGISTRADA => [self::REVISION_TECNICA],
        self::REVISION_TECNICA => [self::DISPONIBLE, self::EN_REPARACION, self::DESCARTE, self::DONADO],
        self::EN_REPARACION => [self::REVISION_TECNICA, self::DANADO],
        self::DANADO => [self::EN_REPARACION, self::REVISION_TECNICA],
        self::DESCARTE => [],
        self::DONADO => [],
        self::MANTENIMIENTO => [self::REVISION_TECNICA, self::EN_REPARACION, self::DANADO],
    ];

    public static function values(): array
    {
        return [
            self::DISPONIBLE,
            self::ASIGNADO,
            self::DEVOLUCION_REGISTRADA,
            self::REVISION_TECNICA,
            self::EN_REPARACION,
            self::DANADO,
            self::DESCARTE,
            self::DONADO,
            self::MANTENIMIENTO,
        ];
    }

    public static function creationTransitions(): array
    {
        return [
            self::DISPONIBLE,
            self::DANADO,
            self::EN_REPARACION,
        ];
    }

    public static function manualTransitions(?string $current = null): array
    {
        if ($current === null || $current === '') {
            return [self::DANADO, self::EN_REPARACION];
        }

        return array_values(array_filter(
            self::TRANSITIONS[$current] ?? [],
            static fn (string $status): bool => !in_array($status, [self::ASIGNADO, self::DEVOLUCION_REGISTRADA, self::REVISION_TECNICA], true)
        ));
    }

    public static function reviewResults(): array
    {
        return [
            self::DISPONIBLE,
            self::EN_REPARACION,
            self::DANADO,
            self::DESCARTE,
            self::DONADO,
        ];
    }

    public static function assertCanCreate(string $status): void
    {
        if (!in_array($status, self::creationTransitions(), true)) {
            throw new \RuntimeException('No puede crear activos en ese estado. Use el flujo formal de estados.');
        }
    }

    public static function assertTransition(string $from, string $to, string $origin = 'manual'): void
    {
        if ($from === $to) {
            throw new \RuntimeException('El estado nuevo debe ser diferente al estado actual.');
        }

        if ($origin === 'manual' && in_array($to, [self::DESCARTE, self::DONADO], true)) {
            throw new \RuntimeException('Descarte y donación solo pueden registrarse desde una revisión técnica.');
        }

        if (!in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
            throw new \RuntimeException("Transición de estado no permitida: {$from} → {$to}.");
        }
    }

    public static function label(string $status): string
    {
        return match ($status) {
            self::DANADO => 'DAÑADO',
            self::DEVOLUCION_REGISTRADA => 'DEVOLUCIÓN REGISTRADA',
            self::REVISION_TECNICA => 'REVISIÓN TÉCNICA',
            self::EN_REPARACION => 'EN REPARACIÓN',
            self::MANTENIMIENTO => 'MANTENIMIENTO (LEGADO)',
            default => $status,
        };
    }
}

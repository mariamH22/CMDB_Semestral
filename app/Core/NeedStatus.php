<?php
declare(strict_types=1);

namespace App\Core;

final class NeedStatus
{
    public const EN_ESPERA = 'EN_ESPERA';
    public const EN_TRAMITE = 'EN_TRAMITE';
    public const APROBADA = 'APROBADA';
    public const RECHAZADA = 'RECHAZADA';

    private const LEGACY_MAP = [
        'PENDIENTE' => self::EN_ESPERA,
        'EN_REVISION' => self::EN_TRAMITE,
        'ATENDIDA' => self::APROBADA,
        'CANCELADA' => self::RECHAZADA,
    ];

    private const LEGACY_STORAGE_MAP = [
        self::EN_ESPERA => 'PENDIENTE',
        self::EN_TRAMITE => 'EN_REVISION',
        self::APROBADA => 'ATENDIDA',
        self::RECHAZADA => 'CANCELADA',
    ];

    private const TRANSITIONS = [
        self::EN_ESPERA => [self::EN_TRAMITE, self::APROBADA, self::RECHAZADA],
        self::EN_TRAMITE => [self::APROBADA, self::RECHAZADA],
        self::APROBADA => [],
        self::RECHAZADA => [],
    ];

    public static function values(): array
    {
        return [self::EN_ESPERA, self::EN_TRAMITE, self::APROBADA, self::RECHAZADA];
    }

    public static function normalize(string $status): string
    {
        $status = strtoupper(trim($status));

        return self::LEGACY_MAP[$status] ?? $status;
    }

    public static function storageStatus(string $status, bool $formalSchema): string
    {
        $status = self::normalize($status);

        return $formalSchema ? $status : (self::LEGACY_STORAGE_MAP[$status] ?? $status);
    }

    public static function assertValid(string $status): void
    {
        if (!in_array(self::normalize($status), self::values(), true)) {
            throw new \RuntimeException('Estado de solicitud inválido.');
        }
    }

    public static function assertTransition(string $from, string $to): void
    {
        $from = self::normalize($from);
        $to = self::normalize($to);

        self::assertValid($from);
        self::assertValid($to);

        if ($from === $to) {
            return;
        }

        if (!in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
            throw new \RuntimeException("Transición de solicitud no permitida: {$from} -> {$to}.");
        }
    }

    public static function requiresProcessor(string $status): bool
    {
        return in_array(self::normalize($status), [self::APROBADA, self::RECHAZADA], true);
    }

    public static function requiresSignature(string $status): bool
    {
        return self::requiresProcessor($status);
    }

    public static function label(string $status): string
    {
        return match (self::normalize($status)) {
            self::EN_ESPERA => 'EN ESPERA',
            self::EN_TRAMITE => 'EN TRÁMITE',
            self::APROBADA => 'APROBADA',
            self::RECHAZADA => 'RECHAZADA',
            default => strtoupper($status),
        };
    }
}

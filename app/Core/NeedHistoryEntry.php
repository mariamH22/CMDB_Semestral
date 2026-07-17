<?php
declare(strict_types=1);

namespace App\Core;

final class NeedHistoryEntry
{
    public static function build(
        int $needId,
        ?int $actorId,
        ?string $previousStatus,
        string $newStatus,
        string $observation,
        ?int $signatureId,
        ?int $auditId
    ): array {
        return [
            'necesidad_id' => $needId,
            'usuario_id' => $actorId,
            'estado_anterior' => $previousStatus !== null ? NeedStatus::normalize($previousStatus) : null,
            'estado_nuevo' => NeedStatus::normalize($newStatus),
            'observacion' => $observation,
            'respuesta_administrativa' => $observation,
            'firma_id' => $signatureId,
            'audit_id' => $auditId,
        ];
    }
}

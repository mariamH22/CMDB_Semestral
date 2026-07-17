<?php
declare(strict_types=1);

namespace App\Core;

final class NeedAccessPolicy
{
    public static function assertCollaboratorOwnsNeed(int $needCollaboratorId, int $sessionCollaboratorId): void
    {
        if ($needCollaboratorId < 1 || $needCollaboratorId !== $sessionCollaboratorId) {
            throw new \RuntimeException('No tiene permiso para consultar esta solicitud.');
        }
    }

    public static function canViewPrivateDetail(?string $role, int $needCollaboratorId, ?int $sessionCollaboratorId): bool
    {
        if (Authorization::can($role, 'needs.view')) {
            return true;
        }

        return $role === 'COLABORADOR'
            && $sessionCollaboratorId !== null
            && $needCollaboratorId === $sessionCollaboratorId;
    }
}

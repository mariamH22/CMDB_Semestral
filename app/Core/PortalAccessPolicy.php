<?php
declare(strict_types=1);

namespace App\Core;

final class PortalAccessPolicy
{
    public static function assertAssignmentBelongsToCollaborator(int $assignmentCollaboratorId, int $sessionCollaboratorId): void
    {
        if ($assignmentCollaboratorId < 1 || $assignmentCollaboratorId !== $sessionCollaboratorId) {
            throw new \RuntimeException('No tiene permiso para operar esta asignación.');
        }
    }

    public static function assertReturnCanBeRequested(string $assignmentStatus, string $inventoryStatus): void
    {
        if ($assignmentStatus !== 'ACTIVA' || $inventoryStatus !== InventoryStatus::ASIGNADO) {
            throw new \RuntimeException('La devolución solo puede solicitarse para equipos activos asignados a usted.');
        }
    }
}

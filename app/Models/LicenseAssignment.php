<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\LicensePolicy;

final class LicenseAssignment
{
    public function __construct(private Database $db)
    {
    }

    public function schemaReady(): bool
    {
        return $this->db->tableExists('licencia_asignaciones');
    }

    public function activeByInventory(int $inventoryId): array
    {
        if (!$this->schemaReady()) {
            return [];
        }

        // Devuelve asignaciones activas para saber el estado real de consumo de cupos de una licencia.
        return $this->db->fetchAll(
            "SELECT la.*, CONCAT(c.nombres, ' ', c.apellidos) AS colaborador_nombre, c.departamento
             FROM licencia_asignaciones la
             INNER JOIN colaboradores c ON c.id = la.colaborador_id
             WHERE la.inventario_id = :id AND la.estado = 'ACTIVA'
             ORDER BY la.fecha_asignacion DESC",
            ['id' => $inventoryId]
        );
    }

    public function activeByCollaborator(int $collaboratorId): array
    {
        if (!$this->schemaReady()) {
            return [];
        }

        return $this->db->fetchAll(
            "SELECT la.*, i.codigo_activo, i.nombre AS licencia_nombre,
                    i.marca, i.modelo, i.proveedor_licencia, i.url_licencia,
                    i.fecha_vencimiento_licencia, i.cantidad AS cupos_totales"
                    . $this->optionalSelect('i') . "
             FROM licencia_asignaciones la
             INNER JOIN inventario i ON i.id = la.inventario_id
             WHERE la.colaborador_id = :id AND la.estado = 'ACTIVA'
             ORDER BY la.fecha_asignacion DESC",
            ['id' => $collaboratorId]
        );
    }

    public function usedQuantity(int $inventoryId): int
    {
        if (!$this->schemaReady()) {
            return 0;
        }

        $row = $this->db->fetch(
            "SELECT COALESCE(SUM(cantidad), 0) AS total
             FROM licencia_asignaciones
             WHERE inventario_id = :id AND estado = 'ACTIVA'",
            ['id' => $inventoryId]
        );

        return (int) ($row['total'] ?? 0);
    }

    public function availableQuantity(int $inventoryId): int
    {
        $item = $this->db->fetch(
            "SELECT cantidad FROM inventario WHERE id = :id AND es_licencia = 1",
            ['id' => $inventoryId]
        );

        if (!$item) {
            return 0;
        }

        return LicensePolicy::available((int) $item['cantidad'], $this->usedQuantity($inventoryId));
    }

    public function assign(int $inventoryId, int $collaboratorId, int $quantity, string $date, string $notes, ?int $userId): void
    {
        if (!$this->schemaReady()) {
            throw new \RuntimeException('La migración de licencias no está aplicada.');
        }

        // Bloquea fila de licencia y asientos activos para asegurar cupo consistente en concurrencia.
        $this->db->transaction(function (Database $db) use ($inventoryId, $collaboratorId, $quantity, $date, $notes, $userId): void {
            $item = $db->fetch("SELECT * FROM inventario WHERE id = :id AND es_licencia = 1 FOR UPDATE", ['id' => $inventoryId]);
            if (!$item) {
                throw new \RuntimeException('Licencia no encontrada.');
            }

            $activeAssignments = $db->fetchAll(
                "SELECT cantidad
                 FROM licencia_asignaciones
                 WHERE inventario_id = :id AND estado = 'ACTIVA'
                 FOR UPDATE",
                ['id' => $inventoryId]
            );
            $used = array_sum(array_map(static fn (array $row): int => (int) $row['cantidad'], $activeAssignments));
            $status = $this->db->columnExists('inventario', 'estado_licencia')
                ? (string) ($item['estado_licencia'] ?? LicensePolicy::ACTIVA)
                : ((int) ($item['activo'] ?? 0) === 1 ? LicensePolicy::ACTIVA : LicensePolicy::INACTIVA);

            LicensePolicy::assertAssignable($item['fecha_vencimiento_licencia'] ?? null, $status);
            LicensePolicy::assertQuantity($quantity, (int) $item['cantidad'], $used);

            $db->insert(
                "INSERT INTO licencia_asignaciones
                 (inventario_id, colaborador_id, usuario_id, cantidad, fecha_asignacion, observaciones, estado)
                 VALUES
                 (:inventario_id, :colaborador_id, :usuario_id, :cantidad, :fecha_asignacion, :observaciones, 'ACTIVA')",
                [
                    'inventario_id' => $inventoryId,
                    'colaborador_id' => $collaboratorId,
                    'usuario_id' => $userId,
                    'cantidad' => $quantity,
                    'fecha_asignacion' => $date,
                    'observaciones' => $notes,
                ]
            );
        });
    }

    public function release(int $id, ?int $inventoryId = null): void
    {
        if (!$this->schemaReady()) {
            return;
        }

        $params = ['id' => $id];
        $scope = '';
        if ($inventoryId !== null && $inventoryId > 0) {
            $scope = ' AND inventario_id = :inventario_id';
            $params['inventario_id'] = $inventoryId;
        }

        $this->db->execute(
            "UPDATE licencia_asignaciones
             SET estado = 'LIBERADA', fecha_fin = CURDATE()
             WHERE id = :id AND estado = 'ACTIVA'{$scope}",
            $params
        );
    }

    private function optionalSelect(string $alias): string
    {
        $columns = [];
        foreach (['tipo_licencia', 'fecha_adquisicion_licencia', 'estado_licencia'] as $column) {
            if ($this->db->columnExists('inventario', $column)) {
                $columns[] = "{$alias}.{$column}";
            }
        }

        return $columns ? ', ' . implode(', ', $columns) : '';
    }
}

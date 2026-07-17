<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class InventoryStateHistory
{
    public function __construct(private Database $db)
    {
    }

    public function schemaReady(): bool
    {
        return $this->db->tableExists('inventario_estado_historial');
    }

    public function record(
        int $inventoryId,
        ?int $userId,
        ?string $oldStatus,
        string $newStatus,
        string $reason = '',
        string $observation = '',
        ?int $signatureId = null,
        array $context = []
    ): void
    {
        if (!$this->schemaReady()) {
            return;
        }

        $columns = ['inventario_id', 'usuario_id', 'estado_anterior', 'estado_nuevo', 'motivo', 'observacion', 'firma_id'];
        $data = [
            'inventario_id' => $inventoryId,
            'usuario_id' => $userId,
            'estado_anterior' => $oldStatus,
            'estado_nuevo' => $newStatus,
            'motivo' => $reason !== '' ? $reason : null,
            'observacion' => $observation !== '' ? $observation : null,
            'firma_id' => $signatureId,
        ];

        $optional = [
            'entidad_origen' => $context['entidad_origen'] ?? null,
            'entidad_id' => $context['entidad_id'] ?? null,
            'audit_id' => $context['audit_id'] ?? null,
        ];

        foreach ($optional as $column => $value) {
            if ($this->db->columnExists('inventario_estado_historial', $column)) {
                $columns[] = $column;
                $data[$column] = $value;
            }
        }

        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

        $this->db->insert(
            "INSERT INTO inventario_estado_historial (" . implode(', ', $columns) . ")
             VALUES (" . implode(', ', $placeholders) . ")",
            array_intersect_key($data, array_flip($columns))
        );
    }

    public function forInventory(int $inventoryId): array
    {
        if (!$this->schemaReady()) {
            return [];
        }

        return $this->db->fetchAll(
            "SELECT h.*, u.nombre_usuario
             FROM inventario_estado_historial h
             LEFT JOIN usuarios u ON u.id = h.usuario_id
             WHERE h.inventario_id = :id
             ORDER BY h.created_at DESC",
            ['id' => $inventoryId]
        );
    }

    public function all(array $filters = []): array
    {
        if (!$this->schemaReady()) {
            return [];
        }

        $sql = "SELECT h.*, i.codigo_activo, i.nombre AS equipo_nombre, i.tipo_activo,
                       i.estado AS estado_actual, i.categoria_id, c.nombre AS categoria_nombre,
                       u.nombre_usuario
                FROM inventario_estado_historial h
                INNER JOIN inventario i ON i.id = h.inventario_id
                LEFT JOIN categorias c ON c.id = i.categoria_id
                LEFT JOIN usuarios u ON u.id = h.usuario_id
                WHERE i.activo = 1";
        $params = [];

        if (!empty($filters['tipo'])) {
            $sql .= " AND i.tipo_activo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND (h.estado_nuevo = :estado_historial OR i.estado = :estado_actual)";
            $params['estado_historial'] = $filters['estado'];
            $params['estado_actual'] = $filters['estado'];
        }

        if (!empty($filters['categoria_id'])) {
            $sql .= " AND i.categoria_id = :categoria_id";
            $params['categoria_id'] = (int) $filters['categoria_id'];
        }

        if (!empty($filters['buscar'])) {
            $sql .= " AND (i.codigo_activo LIKE :buscar_codigo OR i.nombre LIKE :buscar_nombre OR i.serie LIKE :buscar_serie OR h.motivo LIKE :buscar_motivo)";
            $like = '%' . $filters['buscar'] . '%';
            $params['buscar_codigo'] = $like;
            $params['buscar_nombre'] = $like;
            $params['buscar_serie'] = $like;
            $params['buscar_motivo'] = $like;
        }

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND DATE(h.created_at) >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND DATE(h.created_at) <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        $sql .= " ORDER BY h.created_at DESC, h.id DESC";

        return $this->db->fetchAll($sql, $params);
    }
}

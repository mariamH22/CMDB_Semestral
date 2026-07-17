<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\DigitalSignature;
use App\Core\NeedAccessPolicy;
use App\Core\NeedHistoryEntry;
use App\Core\NeedStatus;

final class NeedRequest
{
    public function __construct(private Database $db)
    {
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT n.*
             FROM necesidades n
             WHERE n.id = :id",
            ['id' => $id]
        );
    }

    public function all(?int $collaboratorId = null, array $filters = []): array
    {
        $processorSelect = $this->supportsProcessor()
            ? ', up.nombre_usuario AS procesador_nombre'
            : ', NULL AS procesador_nombre';
        $processorJoin = $this->supportsProcessor()
            ? ' LEFT JOIN usuarios up ON up.id = n.usuario_procesador_id'
            : '';

        $sql = "SELECT n.*, c.nombre AS categoria_nombre,
                       CONCAT(co.nombres, ' ', co.apellidos) AS colaborador_nombre,
                       co.departamento{$processorSelect}
                FROM necesidades n
                INNER JOIN colaboradores co ON co.id = n.colaborador_id
                LEFT JOIN categorias c ON c.id = n.categoria_id
                {$processorJoin}";
        $where = [];
        $params = [];

        if ($collaboratorId !== null) {
            $where[] = 'n.colaborador_id = :id';
            $params['id'] = $collaboratorId;
        }
        if (!empty($filters['categoria_id'])) {
            $where[] = 'n.categoria_id = :categoria_id';
            $params['categoria_id'] = (int) $filters['categoria_id'];
        }
        if (!empty($filters['ubicacion'])) {
            $where[] = 'co.ubicacion = :ubicacion';
            $params['ubicacion'] = (string) $filters['ubicacion'];
        }
        if (!empty($filters['tipo'])) {
            $types = (string) $filters['tipo'] === 'HARDWARE'
                ? ['EQUIPO']
                : ['SOFTWARE', 'LICENCIA'];
            $placeholders = [];
            foreach ($types as $index => $type) {
                $key = 'tipo_necesidad_' . $index;
                $placeholders[] = ':' . $key;
                $params[$key] = $type;
            }
            $where[] = 'n.tipo_necesidad IN (' . implode(', ', $placeholders) . ')';
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= " ORDER BY n.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function create(array $data): int
    {
        // Se guardan solo columnas existentes para no romper instalaciones legacy.
        $columns = ['colaborador_id', 'categoria_id', 'tipo_necesidad', 'descripcion', 'prioridad', 'estado'];
        $data['estado'] = NeedStatus::storageStatus(NeedStatus::EN_ESPERA, $this->supportsFormalStatus());

        if ($this->supportsJustification() && array_key_exists('justificacion', $data)) {
            $columns[] = 'justificacion';
        }
        if ($this->supportsEstimatedCost() && array_key_exists('costo_estimado', $data)) {
            $columns[] = 'costo_estimado';
        }
        if ($this->supportsUnitEstimatedCost() && array_key_exists('costo_unitario_estimado', $data)) {
            $columns[] = 'costo_unitario_estimado';
        }
        if ($this->supportsQuantity() && array_key_exists('cantidad', $data)) {
            $columns[] = 'cantidad';
        }
        if ($this->supportsAnioObjetivo() && array_key_exists('anio_objetivo', $data)) {
            $columns[] = 'anio_objetivo';
        }

        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

        return $this->db->insert(
            "INSERT INTO necesidades (" . implode(', ', $columns) . ")
             VALUES (" . implode(', ', $placeholders) . ")",
            array_intersect_key($data, array_flip($columns))
        );
    }

    public function updateStatus(int $id, string $status, string $comment = '', ?int $actorId = null, ?int $auditId = null): void
    {
        $this->transitionStatus($id, $status, $comment, $actorId, $auditId);
    }

    public function transitionStatus(int $id, string $status, string $response = '', ?int $actorId = null, ?int $auditId = null): ?int
    {
        // Guarda auditoría de estado solo cuando cambia el valor.
        $need = $this->find($id);
        if (!$need) {
            throw new \RuntimeException('La solicitud no existe.');
        }

        $normalizedStatus = NeedStatus::normalize($status);
        NeedStatus::assertTransition((string) $need['estado'], $normalizedStatus);

        $response = trim($response);
        if (NeedStatus::requiresProcessor($normalizedStatus) && $actorId === null) {
            throw new \RuntimeException('La aprobación o rechazo requiere usuario procesador.');
        }

        if (NeedStatus::requiresProcessor($normalizedStatus) && $response === '') {
            throw new \RuntimeException('La respuesta administrativa es obligatoria al aprobar o rechazar.');
        }

        $signatureId = null;
        if (NeedStatus::requiresSignature($normalizedStatus)) {
            $signatureId = DigitalSignature::signAction($this->db, $actorId, 'NECESIDADES', 'PROCESAR_SOLICITUD', 'necesidades', $id, [
                'estado_anterior' => NeedStatus::normalize((string) $need['estado']),
                'estado_nuevo' => $normalizedStatus,
                'respuesta_administrativa' => $response,
                'procesador_id' => $actorId,
            ], $auditId);
        }

        $sets = [
            'estado = :estado',
            'comentario_resolucion = :comentario',
        ];
        $params = [
            'id' => $id,
            'estado' => NeedStatus::storageStatus($normalizedStatus, $this->supportsFormalStatus()),
            'comentario' => $response,
        ];

        if ($this->supportsAdminResponse()) {
            $sets[] = 'respuesta_administrativa = :respuesta_administrativa';
            $params['respuesta_administrativa'] = $response;
        }

        if ($this->supportsProcessor() && NeedStatus::requiresProcessor($normalizedStatus)) {
            $sets[] = 'usuario_procesador_id = :usuario_procesador_id';
            $sets[] = 'fecha_procesamiento = NOW()';
            $params['usuario_procesador_id'] = $actorId;
        }

        if ($this->supportsAuditColumn()) {
            $sets[] = 'audit_id = :audit_id';
            $params['audit_id'] = $auditId;
        }

        if ($this->supportsSignatureColumn()) {
            $sets[] = 'firma_id = :firma_id';
            $params['firma_id'] = $signatureId;
        }

        $this->db->execute(
            "UPDATE necesidades SET " . implode(', ', $sets) . " WHERE id = :id",
            $params
        );

        if ($this->supportsHistory() && NeedStatus::normalize((string) $need['estado']) !== $normalizedStatus) {
            $this->recordHistory(
                $id,
                $actorId,
                NeedStatus::normalize((string) $need['estado']),
                $normalizedStatus,
                $response,
                $signatureId,
                $auditId
            );
        }

        return $signatureId;
    }

    public function history(int $needId): array
    {
        // Historial formal por cada cambio de estado.
        if (!$this->supportsHistory()) {
            return [];
        }

        return $this->db->fetchAll(
            "SELECT h.*, u.nombre_usuario
             FROM necesidades_historial h
             LEFT JOIN usuarios u ON u.id = h.usuario_id
             WHERE h.necesidad_id = :id
             ORDER BY h.created_at ASC, h.id ASC",
            ['id' => $needId]
        );
    }

    public function historyMap(array $needIds): array
    {
        // Agrupa todo el historial por necesidad para pintar tablas detalle.
        if (!$this->supportsHistory() || $needIds === []) {
            return [];
        }

        $ids = array_values(array_unique(array_map('intval', $needIds)));
        if (!$ids) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->fetchAll(
            "SELECT h.*, u.nombre_usuario
             FROM necesidades_historial h
             LEFT JOIN usuarios u ON u.id = h.usuario_id
             WHERE h.necesidad_id IN ({$placeholders})
             ORDER BY h.created_at ASC, h.id ASC",
            $ids
        );

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(int) $row['necesidad_id']][] = $row;
        }

        return $grouped;
    }

    public function assertVisibleToCollaborator(int $needId, int $collaboratorId): array
    {
        $need = $this->find($needId);
        if (!$need) {
            throw new \RuntimeException('La solicitud no existe.');
        }

        NeedAccessPolicy::assertCollaboratorOwnsNeed((int) $need['colaborador_id'], $collaboratorId);

        return $need;
    }

    public function supportsFormalStatus(): bool
    {
        return $this->db->columnExists('necesidades', 'usuario_procesador_id');
    }

    public function supportsJustification(): bool
    {
        return $this->db->columnExists('necesidades', 'justificacion');
    }

    public function supportsEstimatedCost(): bool
    {
        return $this->db->columnExists('necesidades', 'costo_estimado');
    }

    public function supportsUnitEstimatedCost(): bool
    {
        return $this->db->columnExists('necesidades', 'costo_unitario_estimado');
    }

    public function supportsQuantity(): bool
    {
        return $this->db->columnExists('necesidades', 'cantidad');
    }

    public function supportsAnioObjetivo(): bool
    {
        return $this->db->columnExists('necesidades', 'anio_objetivo');
    }

    public function supportsHistory(): bool
    {
        return $this->db->tableExists('necesidades_historial');
    }

    public function supportsProcessor(): bool
    {
        return $this->db->columnExists('necesidades', 'usuario_procesador_id')
            && $this->db->columnExists('necesidades', 'fecha_procesamiento');
    }

    public function supportsAdminResponse(): bool
    {
        return $this->db->columnExists('necesidades', 'respuesta_administrativa');
    }

    private function supportsAuditColumn(): bool
    {
        return $this->db->columnExists('necesidades', 'audit_id');
    }

    private function supportsSignatureColumn(): bool
    {
        return $this->db->columnExists('necesidades', 'firma_id');
    }

    private function recordHistory(
        int $needId,
        ?int $actorId,
        ?string $previousStatus,
        string $newStatus,
        string $observation,
        ?int $signatureId,
        ?int $auditId
    ): void {
        $columns = ['necesidad_id', 'usuario_id', 'estado_anterior', 'estado_nuevo', 'observacion'];
        $data = NeedHistoryEntry::build($needId, $actorId, $previousStatus, $newStatus, $observation, $signatureId, $auditId);

        foreach ([
            'firma_id' => $data['firma_id'],
            'audit_id' => $data['audit_id'],
            'respuesta_administrativa' => $data['respuesta_administrativa'],
        ] as $column => $value) {
            if ($this->db->columnExists('necesidades_historial', $column)) {
                $columns[] = $column;
                $data[$column] = $value;
            }
        }

        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

        $this->db->insert(
            "INSERT INTO necesidades_historial (" . implode(', ', $columns) . ")
             VALUES (" . implode(', ', $placeholders) . ")",
            array_intersect_key($data, array_flip($columns))
        );
    }
}

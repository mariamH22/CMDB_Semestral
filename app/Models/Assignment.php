<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\DigitalSignature;
use App\Core\IntegritySigner;
use App\Core\InventoryStatus;
use App\Core\PortalAccessPolicy;

final class Assignment
{
    public function __construct(private Database $db)
    {
    }

    public function all(bool $activeOnly = false, array $filters = []): array
    {
        $assignerSelect = $this->db->columnExists('asignaciones', 'usuario_asignador_id')
            ? ', ua.nombre_usuario AS asignador_nombre'
            : ', NULL AS asignador_nombre';
        $assignerJoin = $this->db->columnExists('asignaciones', 'usuario_asignador_id')
            ? ' LEFT JOIN usuarios ua ON ua.id = a.usuario_asignador_id'
            : '';
        $sql = "SELECT a.*, i.codigo_activo, i.nombre AS equipo_nombre, i.serie, i.tipo_activo,
                       i.estado AS inventario_estado,
                       CONCAT(c.nombres, ' ', c.apellidos) AS colaborador_nombre,
                       c.departamento, c.ubicacion{$assignerSelect}
                FROM asignaciones a
                INNER JOIN inventario i ON i.id = a.inventario_id
                INNER JOIN colaboradores c ON c.id = a.colaborador_id
                {$assignerJoin}";
        $where = [];
        $params = [];

        if ($activeOnly) {
            $where[] = "a.estado = 'ACTIVA'";
        }
        if (!empty($filters['tipo'])) {
            $where[] = 'i.tipo_activo = :tipo';
            $params['tipo'] = (string) $filters['tipo'];
        }
        if (!empty($filters['estado'])) {
            $where[] = 'i.estado = :estado';
            $params['estado'] = (string) $filters['estado'];
        }
        if (!empty($filters['categoria_id'])) {
            $where[] = 'i.categoria_id = :categoria_id';
            $params['categoria_id'] = (int) $filters['categoria_id'];
        }
        if (!empty($filters['ubicacion'])) {
            $where[] = 'c.ubicacion = :ubicacion';
            $params['ubicacion'] = (string) $filters['ubicacion'];
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= " ORDER BY a.fecha_asignacion DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function forCollaborator(int $collaboratorId): array
    {
        return $this->db->fetchAll(
            "SELECT a.*, i.codigo_activo, i.nombre, i.marca, i.modelo, i.serie, i.imagen_principal, i.estado,
                    c.departamento, c.ubicacion
             FROM asignaciones a
             INNER JOIN inventario i ON i.id = a.inventario_id
             INNER JOIN colaboradores c ON c.id = a.colaborador_id
             WHERE a.colaborador_id = :id AND a.estado = 'ACTIVA'
             ORDER BY a.fecha_asignacion DESC",
            ['id' => $collaboratorId]
        );
    }

    public function supportsFormalReturns(): bool
    {
        return $this->db->tableExists('devoluciones')
            && $this->db->tableExists('revisiones_tecnicas')
            && $this->db->enumValueExists('inventario', 'estado', InventoryStatus::DEVOLUCION_REGISTRADA)
            && $this->db->enumValueExists('inventario', 'estado', InventoryStatus::REVISION_TECNICA)
            && $this->db->enumValueExists('inventario', 'estado', InventoryStatus::EN_REPARACION)
            && $this->db->enumValueExists('revisiones_tecnicas', 'resultado', InventoryStatus::EN_REPARACION);
    }

    public function create(array $data, ?int $userId = null, ?int $auditId = null): int
    {
        return $this->db->transaction(function (Database $db) use ($data, $userId, $auditId): int {
            // Se vuelve a validar dentro de la transacción para evitar asignar
            // un activo que otro usuario haya tomado entre la carga del formulario y el envío.
            $available = $db->fetch(
                "SELECT i.*
                 FROM inventario i
                 LEFT JOIN asignaciones a ON a.inventario_id = i.id AND a.estado = 'ACTIVA'
                 WHERE i.id = :id
                   AND i.activo = 1
                   AND i.estado = 'DISPONIBLE'
                   AND a.id IS NULL
                 FOR UPDATE",
                ['id' => $data['inventario_id']]
            );

            if (!$available) {
                throw new \RuntimeException('El activo seleccionado ya no está disponible para asignación.');
            }

            $columns = ['inventario_id', 'colaborador_id', 'fecha_asignacion', 'ip_asignada', 'observaciones', 'estado'];
            $data['estado'] = 'ACTIVA';
            foreach ([
                'usuario_asignador_id' => $userId,
                'audit_id' => $auditId,
            ] as $column => $value) {
                if ($db->columnExists('asignaciones', $column)) {
                    $columns[] = $column;
                    $data[$column] = $value;
                }
            }

            $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

            $id = $db->insert(
                "INSERT INTO asignaciones (" . implode(', ', $columns) . ")
                 VALUES (" . implode(', ', $placeholders) . ")",
                array_intersect_key($data, array_flip($columns))
            );

            $newStatus = 'ASIGNADO';
            InventoryStatus::assertTransition((string) $available['estado'], $newStatus, 'asignacion');
            $signature = IntegritySigner::sign([
                $available['serie'],
                $available['tipo_activo'],
                $newStatus,
                $available['fecha_ingreso'],
            ]);

            $signatureId = DigitalSignature::signAction($db, $userId, 'ASIGNACIONES', 'ASIGNAR', 'asignaciones', $id, [
                'asignacion_id' => $id,
                'inventario_id' => (int) $data['inventario_id'],
                'colaborador_id' => (int) $data['colaborador_id'],
                'estado_nuevo' => $newStatus,
            ], $auditId);

            if ($signatureId !== null && $db->columnExists('asignaciones', 'firma_id')) {
                $db->execute(
                    "UPDATE asignaciones SET firma_id = :firma_id WHERE id = :id",
                    ['id' => $id, 'firma_id' => $signatureId]
                );
            }

            $db->execute(
                "UPDATE inventario SET estado = :estado, firma_integridad = :firma WHERE id = :id",
                [
                    'id' => $data['inventario_id'],
                    'estado' => $newStatus,
                    'firma' => $signature,
                ]
            );

            (new InventoryStateHistory($db))->record(
                (int) $data['inventario_id'],
                $userId,
                (string) $available['estado'],
                $newStatus,
                'Asignación a colaborador',
                'Activo asignado desde el módulo de asignaciones.',
                $signatureId,
                [
                    'entidad_origen' => 'asignaciones',
                    'entidad_id' => $id,
                    'audit_id' => $auditId,
                ]
            );

            return $id;
        });
    }

    public function close(int $id, string $observation = '', array $returnData = [], ?int $userId = null, ?int $auditId = null, ?int $collaboratorScopeId = null): void
    {
        $this->db->transaction(function (Database $db) use ($id, $observation, $returnData, $userId, $auditId, $collaboratorScopeId): void {
            $this->assertFormalReturnsReady();

            $assignment = $db->fetch(
                "SELECT a.*, i.serie, i.tipo_activo, i.fecha_ingreso, i.estado AS estado_actual
                 FROM asignaciones a
                 INNER JOIN inventario i ON i.id = a.inventario_id
                 WHERE a.id = :id
                 FOR UPDATE",
                ['id' => $id]
            );

            if (!$assignment || $assignment['estado'] !== 'ACTIVA') {
                if ($collaboratorScopeId !== null) {
                    throw new \RuntimeException('La asignación no está activa o ya fue devuelta.');
                }
                return;
            }

            if ($collaboratorScopeId !== null) {
                PortalAccessPolicy::assertAssignmentBelongsToCollaborator((int) $assignment['colaborador_id'], $collaboratorScopeId);
                PortalAccessPolicy::assertReturnCanBeRequested((string) $assignment['estado'], (string) $assignment['estado_actual']);
            }

            $openReturn = $db->fetch(
                "SELECT id
                 FROM devoluciones
                 WHERE asignacion_id = :id
                   AND estado IN ('PENDIENTE_REVISION', 'EN_REVISION')
                 LIMIT 1
                 FOR UPDATE",
                ['id' => $id]
            );
            if ($openReturn) {
                throw new \RuntimeException('Ya existe una devolución pendiente para esta asignación.');
            }

            $reviewStatus = InventoryStatus::DEVOLUCION_REGISTRADA;
            InventoryStatus::assertTransition((string) $assignment['estado_actual'], $reviewStatus, 'devolucion');
            $signature = IntegritySigner::sign([
                $assignment['serie'],
                $assignment['tipo_activo'],
                $reviewStatus,
                $assignment['fecha_ingreso'],
            ]);

            $signatureId = DigitalSignature::signAction($db, $userId, 'ASIGNACIONES', 'DEVOLUCION', 'asignaciones', $id, [
                'asignacion_id' => $id,
                'inventario_id' => (int) $assignment['inventario_id'],
                'estado_nuevo' => $reviewStatus,
                'motivo' => $returnData['motivo'] ?? '',
                'estado_fisico' => $returnData['estado_fisico'] ?? '',
            ], $auditId);

            $db->execute(
                "UPDATE inventario SET estado = :estado, firma_integridad = :firma WHERE id = :id",
                [
                    'id' => $assignment['inventario_id'],
                    'estado' => $reviewStatus,
                    'firma' => $signature,
                ]
            );

            $returnId = (new ReturnReview($db))->createReturn([
                'asignacion_id' => $id,
                'inventario_id' => (int) $assignment['inventario_id'],
                'solicitado_por' => $userId,
                'recibido_por' => null,
                'motivo' => (string) ($returnData['motivo'] ?? 'Devolución de activo'),
                'estado_fisico' => $returnData['estado_fisico'] ?? null,
                'observaciones' => (string) ($returnData['observaciones'] ?? $observation),
                'evidencia' => (string) ($returnData['evidencia'] ?? ''),
                'fecha_recepcion' => null,
                'accesorios_recibidos' => null,
                'observacion_recepcion' => null,
                'firma_id' => $signatureId,
            ]);

            (new InventoryStateHistory($db))->record(
                (int) $assignment['inventario_id'],
                $userId,
                (string) $assignment['estado_actual'],
                $reviewStatus,
                'Devolución de asignación',
                (string) ($returnData['observaciones'] ?? $observation),
                $signatureId,
                [
                    'entidad_origen' => 'devoluciones',
                    'entidad_id' => $returnId ?? $id,
                    'audit_id' => $auditId,
                ]
            );
        });
    }

    private function assertFormalReturnsReady(): void
    {
        if (!$this->supportsFormalReturns()) {
            throw new \RuntimeException('La base de datos no tiene aplicado el ciclo de vida formal de activos. Ejecute la migración database/migrations/2026_07_13_0007_ciclo_vida_activo_formal.sql y vuelva a intentar la devolución.');
        }
    }
}

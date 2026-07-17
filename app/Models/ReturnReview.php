<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\DigitalSignature;
use App\Core\IntegritySigner;
use App\Core\InventoryStatus;

final class ReturnReview
{
    public function __construct(private Database $db)
    {
    }

    public function schemaReady(): bool
    {
        return $this->db->tableExists('devoluciones')
            && $this->db->tableExists('revisiones_tecnicas')
            && $this->db->enumValueExists('inventario', 'estado', InventoryStatus::DEVOLUCION_REGISTRADA)
            && $this->db->enumValueExists('inventario', 'estado', InventoryStatus::REVISION_TECNICA)
            && $this->db->enumValueExists('inventario', 'estado', InventoryStatus::EN_REPARACION)
            && $this->db->enumValueExists('revisiones_tecnicas', 'resultado', InventoryStatus::EN_REPARACION)
            && $this->db->columnExists('devoluciones', 'fecha_recepcion')
            && $this->db->columnExists('devoluciones', 'accesorios_recibidos')
            && $this->db->columnExists('devoluciones', 'observacion_recepcion');
    }

    public function createReturn(array $data): ?int
    {
        if (!$this->schemaReady()) {
            return null;
        }

        $columns = ['asignacion_id', 'inventario_id', 'solicitado_por', 'recibido_por', 'motivo', 'estado_fisico', 'observaciones', 'estado'];
        $data['estado'] = 'PENDIENTE_REVISION';
        $data['solicitado_por'] ??= null;
        $data['recibido_por'] ??= null;

        foreach (['evidencia', 'fecha_recepcion', 'accesorios_recibidos', 'observacion_recepcion', 'firma_id'] as $column) {
            if ($this->db->columnExists('devoluciones', $column)) {
                $columns[] = $column;
                $data[$column] ??= null;
            }
        }

        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

        return $this->db->insert(
            "INSERT INTO devoluciones (" . implode(', ', $columns) . ")
             VALUES (" . implode(', ', $placeholders) . ")",
            array_intersect_key($data, array_flip($columns))
        );
    }

    public function pending(array $filters = []): array
    {
        if (!$this->schemaReady()) {
            return [];
        }

        $where = ["d.estado IN ('PENDIENTE_REVISION', 'EN_REVISION')"];
        $params = [];

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

        return $this->db->fetchAll(
            "SELECT d.*, i.codigo_activo, i.nombre AS equipo_nombre, i.serie,
                    CONCAT(c.nombres, ' ', c.apellidos) AS colaborador_nombre,
                    us.nombre_usuario AS solicitante_nombre,
                    ur.nombre_usuario AS receptor_nombre
             FROM devoluciones d
             INNER JOIN asignaciones a ON a.id = d.asignacion_id
             INNER JOIN inventario i ON i.id = d.inventario_id
             INNER JOIN colaboradores c ON c.id = a.colaborador_id
             LEFT JOIN usuarios us ON us.id = d.solicitado_por
             LEFT JOIN usuarios ur ON ur.id = d.recibido_por
             WHERE " . implode(' AND ', $where) . "
             ORDER BY d.created_at ASC",
            $params
        );
    }

    public function all(): array
    {
        if (!$this->schemaReady()) {
            return [];
        }

        return $this->db->fetchAll(
            "SELECT d.id AS devolucion_id, d.motivo, d.estado_fisico, d.observaciones AS observacion_devolucion,
                    d.estado AS estado_devolucion, d.created_at AS fecha_devolucion,
                    d.fecha_recepcion, d.accesorios_recibidos, d.observacion_recepcion,
                    r.id AS revision_id, r.resultado, r.observacion_tecnica, r.evidencia, r.created_at AS fecha_revision,
                    i.codigo_activo, i.nombre AS equipo_nombre, i.serie,
                    CONCAT(c.nombres, ' ', c.apellidos) AS colaborador_nombre,
                    u.nombre_usuario AS tecnico
             FROM devoluciones d
             INNER JOIN asignaciones a ON a.id = d.asignacion_id
             INNER JOIN inventario i ON i.id = d.inventario_id
             INNER JOIN colaboradores c ON c.id = a.colaborador_id
             LEFT JOIN revisiones_tecnicas r ON r.devolucion_id = d.id
             LEFT JOIN usuarios u ON u.id = r.tecnico_id
             ORDER BY d.created_at DESC"
        );
    }

    public function forCollaborator(int $collaboratorId): array
    {
        if (!$this->schemaReady()) {
            return [];
        }

        return $this->db->fetchAll(
            "SELECT d.id AS devolucion_id, d.motivo, d.estado_fisico, d.observaciones AS observacion_devolucion,
                    d.estado AS estado_devolucion, d.created_at AS fecha_devolucion,
                    d.fecha_recepcion, d.accesorios_recibidos, d.observacion_recepcion,
                    r.resultado, r.observacion_tecnica, r.created_at AS fecha_revision,
                    i.codigo_activo, i.nombre AS equipo_nombre, i.serie
             FROM devoluciones d
             INNER JOIN asignaciones a ON a.id = d.asignacion_id
             INNER JOIN inventario i ON i.id = d.inventario_id
             LEFT JOIN revisiones_tecnicas r ON r.devolucion_id = d.id
             WHERE a.colaborador_id = :id
             ORDER BY d.created_at DESC",
             ['id' => $collaboratorId]
        );
    }

    public function receiveReturn(int $returnId, int $receiverId, ?int $auditId = null, array $receiptData = []): void
    {
        if (!$this->schemaReady()) {
            throw new \RuntimeException('La base de datos no tiene aplicado el ciclo de vida formal de activos. Ejecute la migración database/migrations/2026_07_13_0007_ciclo_vida_activo_formal.sql y vuelva a intentar la recepción.');
        }

        $this->db->transaction(function (Database $db) use ($returnId, $receiverId, $auditId, $receiptData): void {
            $return = $db->fetch(
                "SELECT d.*, i.serie, i.tipo_activo, i.fecha_ingreso, i.estado AS estado_actual
                 FROM devoluciones d
                 INNER JOIN inventario i ON i.id = d.inventario_id
                 WHERE d.id = :id
                 FOR UPDATE",
                ['id' => $returnId]
            );

            if (!$return) {
                throw new \RuntimeException('Devolución no encontrada.');
            }
            if ((string) $return['estado'] !== 'PENDIENTE_REVISION') {
                throw new \RuntimeException('La devolución ya fue recibida o resuelta.');
            }
            if (!empty($return['recibido_por']) || !empty($return['fecha_recepcion'])) {
                throw new \RuntimeException('La devolución ya tiene recepción física registrada.');
            }
            if ((int) ($return['solicitado_por'] ?? 0) > 0 && (int) $return['solicitado_por'] === $receiverId) {
                throw new \RuntimeException('La recepción física debe registrarla un usuario distinto al solicitante.');
            }

            InventoryStatus::assertTransition((string) $return['estado_actual'], InventoryStatus::REVISION_TECNICA, 'recepcion');

            $signature = IntegritySigner::sign([
                $return['serie'],
                $return['tipo_activo'],
                InventoryStatus::REVISION_TECNICA,
                $return['fecha_ingreso'],
            ]);

            $signatureId = DigitalSignature::signAction($db, $receiverId, 'ASIGNACIONES', 'RECIBIR_DEVOLUCION', 'devoluciones', $returnId, [
                'devolucion_id' => $returnId,
                'inventario_id' => (int) $return['inventario_id'],
                'estado_fisico' => $receiptData['estado_fisico'] ?? $return['estado_fisico'],
                'accesorios_recibidos' => $receiptData['accesorios_recibidos'] ?? '',
                'observacion_recepcion' => $receiptData['observacion_recepcion'] ?? '',
            ], $auditId);

            $sets = [
                "estado = 'EN_REVISION'",
                'recibido_por = :recibido_por',
                'fecha_recepcion = NOW()',
                'firma_id = :firma_id',
            ];
            $params = [
                'id' => $returnId,
                'recibido_por' => $receiverId,
                'firma_id' => $signatureId,
            ];

            foreach (['estado_fisico', 'evidencia', 'accesorios_recibidos', 'observacion_recepcion'] as $column) {
                if ($db->columnExists('devoluciones', $column) && array_key_exists($column, $receiptData)) {
                    $sets[] = "{$column} = :{$column}";
                    $params[$column] = $receiptData[$column];
                }
            }

            $db->execute(
                'UPDATE devoluciones SET ' . implode(', ', $sets) . ' WHERE id = :id',
                $params
            );

            $db->execute(
                "UPDATE inventario SET estado = :estado, firma_integridad = :firma WHERE id = :id",
                [
                    'id' => (int) $return['inventario_id'],
                    'estado' => InventoryStatus::REVISION_TECNICA,
                    'firma' => $signature,
                ]
            );

            (new InventoryStateHistory($db))->record(
                (int) $return['inventario_id'],
                $receiverId,
                (string) $return['estado_actual'],
                InventoryStatus::REVISION_TECNICA,
                'Recepción física de devolución',
                (string) ($receiptData['observacion_recepcion'] ?? ''),
                $signatureId,
                [
                    'entidad_origen' => 'devoluciones',
                    'entidad_id' => $returnId,
                    'audit_id' => $auditId,
                ]
            );
        });
    }

    public function completeReview(int $returnId, int $technicianId, string $result, string $observation, ?int $userId, ?int $auditId = null, array $reviewData = []): void
    {
        if (!$this->schemaReady()) {
            throw new \RuntimeException('La base de datos no tiene aplicado el ciclo de vida formal de activos. Ejecute la migración database/migrations/2026_07_13_0007_ciclo_vida_activo_formal.sql y vuelva a intentar la revisión.');
        }

        $this->db->transaction(function (Database $db) use ($returnId, $technicianId, $result, $observation, $userId, $auditId, $reviewData): void {
            $return = $db->fetch(
                "SELECT d.*, i.serie, i.tipo_activo, i.fecha_ingreso, i.estado AS estado_actual
                 FROM devoluciones d
                 INNER JOIN inventario i ON i.id = d.inventario_id
                 WHERE d.id = :id
                 FOR UPDATE",
                ['id' => $returnId]
            );

            if (!$return) {
                throw new \RuntimeException('Devolución no encontrada.');
            }
            if ((string) $return['estado'] !== 'EN_REVISION' || empty($return['recibido_por']) || empty($return['fecha_recepcion'])) {
                throw new \RuntimeException('La devolución debe tener recepción física registrada antes de la revisión técnica.');
            }

            if ((string) $return['estado_actual'] !== InventoryStatus::REVISION_TECNICA) {
                throw new \RuntimeException('El activo debe estar en revisión técnica antes de cerrar la revisión.');
            }
            InventoryStatus::assertTransition(InventoryStatus::REVISION_TECNICA, $result, 'revision');

            if (in_array($result, [InventoryStatus::DESCARTE, InventoryStatus::DONADO], true) && trim($observation) === '') {
                throw new \RuntimeException('La opinión técnica es obligatoria para descarte o donación.');
            }

            $signatureId = DigitalSignature::signAction($db, $userId, 'ASIGNACIONES', 'REVISION_TECNICA', 'devoluciones', $returnId, [
                'devolucion_id' => $returnId,
                'inventario_id' => (int) $return['inventario_id'],
                'resultado' => $result,
                'observacion' => $observation,
                'diagnostico' => $reviewData['diagnostico'] ?? '',
                'recomendacion' => $reviewData['recomendacion'] ?? '',
            ], $auditId);

            $reviewColumns = ['devolucion_id', 'inventario_id', 'tecnico_id', 'resultado', 'observacion_tecnica', 'firma_id'];
            $reviewParams = [
                'devolucion_id' => $returnId,
                'inventario_id' => (int) $return['inventario_id'],
                'tecnico_id' => $technicianId ?: null,
                'resultado' => $result,
                'observacion_tecnica' => $observation,
                'firma_id' => $signatureId,
            ];

            foreach (['diagnostico', 'opinion_tecnica', 'recomendacion', 'evidencia', 'aprobador_id'] as $column) {
                if ($db->columnExists('revisiones_tecnicas', $column)) {
                    $reviewColumns[] = $column;
                    $reviewParams[$column] = $column === 'aprobador_id'
                        ? ($reviewData[$column] ?? $userId)
                        : ($reviewData[$column] ?? null);
                }
            }

            $reviewPlaceholders = array_map(static fn (string $column): string => ':' . $column, $reviewColumns);

            $db->insert(
                "INSERT INTO revisiones_tecnicas (" . implode(', ', $reviewColumns) . ")
                 VALUES (" . implode(', ', $reviewPlaceholders) . ")",
                array_intersect_key($reviewParams, array_flip($reviewColumns))
            );

            $signature = IntegritySigner::sign([
                $return['serie'],
                $return['tipo_activo'],
                $result,
                $return['fecha_ingreso'],
            ]);

            $sets = [
                'estado = :estado',
                'firma_integridad = :firma',
            ];
            $params = [
                'id' => (int) $return['inventario_id'],
                'estado' => $result,
                'firma' => $signature,
            ];

            foreach ($this->inventoryOptionalColumns($db) as $column) {
                if (array_key_exists($column, $reviewData)) {
                    $sets[] = "{$column} = :{$column}";
                    $params[$column] = $reviewData[$column];
                }
            }

            if ($result === InventoryStatus::DONADO && $db->columnExists('inventario', 'activo')) {
                $sets[] = 'activo = 0';
            }

            $db->execute(
                "UPDATE inventario SET " . implode(', ', $sets) . " WHERE id = :id",
                $params
            );

            $db->execute(
                "UPDATE devoluciones
                 SET estado = 'APROBADA'
                 WHERE id = :id",
                ['id' => $returnId]
            );

            $db->execute(
                "UPDATE asignaciones
                 SET estado = 'DEVUELTA',
                     fecha_devolucion = CURDATE(),
                     observaciones = TRIM(CONCAT(COALESCE(observaciones, ''), '\n', :observacion))
                 WHERE id = :id
                   AND estado = 'ACTIVA'",
                ['id' => (int) $return['asignacion_id'], 'observacion' => $observation]
            );

            (new InventoryStateHistory($db))->record(
                (int) $return['inventario_id'],
                $userId,
                InventoryStatus::REVISION_TECNICA,
                $result,
                'Revisión técnica de devolución',
                $observation,
                $signatureId,
                [
                    'entidad_origen' => 'revisiones_tecnicas',
                    'entidad_id' => $returnId,
                    'audit_id' => $auditId,
                ]
            );
        });
    }

    private function inventoryOptionalColumns(Database $db): array
    {
        $columns = [
            'responsable_donacion',
            'beneficiario_donacion',
            'evidencia_donacion',
            'observacion_donacion',
            'fecha_donacion',
            'observacion_tecnica_descarte',
            'evaluador_descarte_id',
            'fecha_evaluacion_descarte',
            'evidencia_descarte',
            'valor_donacion',
            'autorizador_donacion_id',
            'responsable_descarte_id',
            'motivo_descarte',
        ];

        return array_values(array_filter(
            $columns,
            static fn (string $column): bool => $db->columnExists('inventario', $column)
        ));
    }
}

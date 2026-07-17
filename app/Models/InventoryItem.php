<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\DepreciationCalculator;
use App\Core\DigitalSignature;
use App\Core\IntegritySigner;
use App\Core\InventoryImagePolicy;
use App\Core\InventoryStatus;

final class InventoryItem
{
    public function __construct(private Database $db)
    {
    }

    public function all(array $filters = []): array
    {
        $sql = "SELECT i.*, c.nombre AS categoria_nombre,
                       a.id AS asignacion_actual,
                       CONCAT(co.nombres, ' ', co.apellidos) AS asignado_a
                FROM inventario i
                LEFT JOIN categorias c ON c.id = i.categoria_id
                LEFT JOIN asignaciones a ON a.inventario_id = i.id AND a.estado = 'ACTIVA'
                LEFT JOIN colaboradores co ON co.id = a.colaborador_id
                WHERE " . (!empty($filters['include_inactive']) ? '1 = 1' : 'i.activo = 1');
        $params = [];

        if (!empty($filters['tipo'])) {
            $sql .= " AND i.tipo_activo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND i.estado = :estado";
            $params['estado'] = $filters['estado'];
        }

        if (!empty($filters['categoria_id'])) {
            $sql .= " AND i.categoria_id = :categoria_id";
            $params['categoria_id'] = (int) $filters['categoria_id'];
        }

        if (!empty($filters['sin_asignar'])) {
            $sql .= " AND a.id IS NULL";
        }

        if (!empty($filters['licencias'])) {
            $sql .= " AND i.es_licencia = 1 AND a.id IS NULL";
        }

        if (!empty($filters['buscar'])) {
            $sql .= " AND (i.codigo_activo LIKE :buscar_codigo OR i.nombre LIKE :buscar_nombre OR i.serie LIKE :buscar_serie OR i.marca LIKE :buscar_marca)";
            $like = '%' . $filters['buscar'] . '%';
            $params['buscar_codigo'] = $like;
            $params['buscar_nombre'] = $like;
            $params['buscar_serie'] = $like;
            $params['buscar_marca'] = $like;
        }

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND i.fecha_ingreso >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND i.fecha_ingreso <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        $sql .= " ORDER BY i.created_at DESC";

        $items = $this->db->fetchAll($sql, $params);

        foreach ($items as &$item) {
            // Cada fila incluye verificacion HMAC para mostrar si el activo fue alterado.
            $item['integridad_valida'] = IntegritySigner::verify(
                [$item['serie'], $item['tipo_activo'], $item['estado'], $item['fecha_ingreso']],
                $item['firma_integridad']
            );
            $item['fecha_limite_depreciacion'] = DepreciationCalculator::limitDate(
                (string) $item['fecha_ingreso'],
                (int) $item['vida_util_meses']
            );
        }

        return $items;
    }

    public function donationsHistorical(array $filters = []): array
    {
        if (!empty($filters['estado']) && (string) $filters['estado'] !== InventoryStatus::DONADO) {
            return [];
        }

        $sql = "SELECT i.*, c.nombre AS categoria_nombre,
                       a.id AS asignacion_actual,
                       CONCAT(co.nombres, ' ', co.apellidos) AS asignado_a
                FROM inventario i
                LEFT JOIN categorias c ON c.id = i.categoria_id
                LEFT JOIN asignaciones a ON a.inventario_id = i.id AND a.estado = 'ACTIVA'
                LEFT JOIN colaboradores co ON co.id = a.colaborador_id
                WHERE i.estado = :estado_donado";
        $params = ['estado_donado' => InventoryStatus::DONADO];

        if (!empty($filters['tipo'])) {
            $sql .= " AND i.tipo_activo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }

        if (!empty($filters['categoria_id'])) {
            $sql .= " AND i.categoria_id = :categoria_id";
            $params['categoria_id'] = (int) $filters['categoria_id'];
        }

        if (!empty($filters['sin_asignar'])) {
            $sql .= " AND a.id IS NULL";
        }

        if (!empty($filters['licencias'])) {
            $sql .= " AND i.es_licencia = 1 AND a.id IS NULL";
        }

        if (!empty($filters['buscar'])) {
            $sql .= " AND (
                i.codigo_activo LIKE :buscar_codigo
                OR i.nombre LIKE :buscar_nombre
                OR i.serie LIKE :buscar_serie
                OR i.marca LIKE :buscar_marca
                OR i.responsable_donacion LIKE :buscar_responsable
                OR i.beneficiario_donacion LIKE :buscar_beneficiario
            )";
            $like = '%' . $filters['buscar'] . '%';
            $params['buscar_codigo'] = $like;
            $params['buscar_nombre'] = $like;
            $params['buscar_serie'] = $like;
            $params['buscar_marca'] = $like;
            $params['buscar_responsable'] = $like;
            $params['buscar_beneficiario'] = $like;
        }

        if (!empty($filters['fecha_desde'])) {
            $sql .= " AND COALESCE(i.fecha_donacion, i.fecha_ingreso) >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $sql .= " AND COALESCE(i.fecha_donacion, i.fecha_ingreso) <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }

        $sql .= " ORDER BY COALESCE(i.fecha_donacion, DATE(i.updated_at), DATE(i.created_at)) DESC, i.created_at DESC";

        $items = $this->db->fetchAll($sql, $params);

        foreach ($items as &$item) {
            $item['integridad_valida'] = IntegritySigner::verify(
                [$item['serie'], $item['tipo_activo'], $item['estado'], $item['fecha_ingreso']],
                $item['firma_integridad']
            );
            $item['fecha_limite_depreciacion'] = DepreciationCalculator::limitDate(
                (string) $item['fecha_ingreso'],
                (int) $item['vida_util_meses']
            );
        }

        return $items;
    }

    public function find(int $id): ?array
    {
        $item = $this->db->fetch(
            "SELECT i.*, c.nombre AS categoria_nombre
             FROM inventario i
             LEFT JOIN categorias c ON c.id = i.categoria_id
             WHERE i.id = :id",
            ['id' => $id]
        );

        if ($item) {
            $item['integridad_valida'] = IntegritySigner::verify(
                [$item['serie'], $item['tipo_activo'], $item['estado'], $item['fecha_ingreso']],
                $item['firma_integridad']
            );
            $item['imagenes'] = $this->images($id);
        }

        return $item;
    }

    public function supportsEncryptedLicenseKeys(): bool
    {
        return $this->db->columnExists('inventario', 'clave_licencia_cifrada');
    }

    public function available(): array
    {
        return $this->db->fetchAll(
            "SELECT i.*
             FROM inventario i
             LEFT JOIN asignaciones a ON a.inventario_id = i.id AND a.estado = 'ACTIVA'
             WHERE i.activo = 1 AND i.estado = 'DISPONIBLE' AND a.id IS NULL
             ORDER BY i.nombre"
        );
    }

    public function nearDepreciation(int $days = 90, array $filters = []): array
    {
        $cutoff = date('Y-m-d', strtotime('+' . $days . ' days'));
        [$join, $where, $params] = $this->dashboardFilterSql($filters);
        $where[] = "DATE_ADD(i.fecha_ingreso, INTERVAL i.vida_util_meses MONTH) <= :cutoff";
        $where[] = "i.estado NOT IN ('DONADO', 'DESCARTE')";
        $params['cutoff'] = $cutoff;

        return $this->db->fetchAll(
            "SELECT i.*, c.nombre AS categoria_nombre,
                    DATE_ADD(i.fecha_ingreso, INTERVAL i.vida_util_meses MONTH) AS fecha_limite_depreciacion
             FROM inventario i
             LEFT JOIN categorias c ON c.id = i.categoria_id
             {$join}
             WHERE " . implode(' AND ', $where) . "
             ORDER BY fecha_limite_depreciacion ASC",
            $params
        );
    }

    public function create(array $data): int
    {
        InventoryStatus::assertCanCreate((string) $data['estado']);
        InventoryImagePolicy::assertPersistedHardwareImages($data);

        // La firma se recalcula a partir de campos estables del activo.
        $data['firma_integridad'] = IntegritySigner::sign([
            $data['serie'], $data['tipo_activo'], $data['estado'], $data['fecha_ingreso']
        ]);

        // availableColumns permite que el codigo funcione con instalaciones que aplican migraciones gradualmente.
        $columns = $this->availableColumns($this->requiredColumns());

        foreach ($this->optionalColumns() as $column) {
            if (array_key_exists($column, $data)) {
                $columns[] = $column;
            }
        }

        foreach (['imagen_principal', 'thumbnail'] as $column) {
            if (!empty($data[$column]) && $this->db->columnExists('inventario', $column)) {
                $columns[] = $column;
            }
        }

        $params = array_intersect_key($data, array_flip($columns));
        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

        $id = $this->db->insert(
            "INSERT INTO inventario (" . implode(', ', $columns) . ")
             VALUES (" . implode(', ', $placeholders) . ")",
            $params
        );

        if (!empty($data['imagen_principal']) && $this->db->tableExists('inventario_imagenes')) {
            $this->addImage($id, $data['imagen_principal'], true);
        }

        (new InventoryStateHistory($this->db))->record($id, null, null, $data['estado'], 'Registro inicial', 'Activo creado en inventario.');

        return $id;
    }

    public function update(int $id, array $data): void
    {
        $current = $this->find($id);
        if ($current && (string) $current['estado'] !== (string) $data['estado']) {
            throw new \RuntimeException('El estado no se edita desde el formulario general. Use el flujo formal de estados.');
        }

        $newImagePath = $data['new_image_path'] ?? null;
        unset($data['new_image_path']);

        $data['firma_integridad'] = IntegritySigner::sign([
            $data['serie'], $data['tipo_activo'], $data['estado'], $data['fecha_ingreso']
        ]);
        $params = $data;
        $params['id'] = $id;

        $paramNames = ['id'];
        $sets = [];

        foreach ($this->availableColumns($this->requiredColumns()) as $column) {
            if (array_key_exists($column, $params)) {
                $sets[] = "{$column} = :{$column}";
                $paramNames[] = $column;
            }
        }

        foreach ($this->optionalColumns() as $column) {
            if (array_key_exists($column, $params)) {
                $sets[] = "{$column} = :{$column}";
                $paramNames[] = $column;
            }
        }

        if (!empty($data['imagen_principal']) && $this->db->columnExists('inventario', 'imagen_principal')) {
            $sets[] = 'imagen_principal = :imagen_principal';
            $paramNames[] = 'imagen_principal';
            if ($this->db->columnExists('inventario', 'thumbnail')) {
                $sets[] = 'thumbnail = :thumbnail';
                $paramNames[] = 'thumbnail';
            }
        } else {
            unset($params['imagen_principal'], $params['thumbnail']);
        }

        $sql = "UPDATE inventario SET " . implode(', ', $sets) . " WHERE id = :id";

        $this->db->execute($sql, array_intersect_key($params, array_flip($paramNames)));

        if ($newImagePath && $this->db->tableExists('inventario_imagenes')) {
            $this->addImage($id, $newImagePath, false);
        }

    }

    public function addImage(int $itemId, string $path, bool $main): void
    {
        if (!$this->db->tableExists('inventario_imagenes')) {
            return;
        }

        $this->db->insert(
            "INSERT INTO inventario_imagenes (inventario_id, ruta, es_principal) VALUES (:id, :ruta, :principal)",
            ['id' => $itemId, 'ruta' => $path, 'principal' => $main ? 1 : 0]
        );
    }

    public function images(int $itemId): array
    {
        if (!$this->db->tableExists('inventario_imagenes')) {
            return [];
        }

        return $this->db->fetchAll(
            "SELECT * FROM inventario_imagenes WHERE inventario_id = :id ORDER BY es_principal DESC, created_at DESC",
            ['id' => $itemId]
        );
    }

    public function setStatus(int $id, string $status, array $meta = [], ?int $userId = null, ?int $auditId = null): void
    {
        $item = $this->find($id);
        if (!$item) {
            return;
        }

        // Todas las transiciones de estado pasan por la politica central del ciclo de vida.
        InventoryStatus::assertTransition(
            (string) $item['estado'],
            $status,
            (string) ($meta['origen'] ?? 'manual')
        );

        $signature = IntegritySigner::sign([$item['serie'], $item['tipo_activo'], $status, $item['fecha_ingreso']]);
        $signatureId = DigitalSignature::signAction($this->db, $userId, 'INVENTARIO', 'CAMBIAR_ESTADO', 'inventario', $id, [
            'estado_anterior' => $item['estado'],
            'estado_nuevo' => $status,
            'meta' => $meta,
        ], $auditId);

        $params = [
            'id' => $id,
            'estado' => $status,
            'estado_donacion' => $status,
            'estado_activo' => $status,
            'responsable' => $meta['responsable_donacion'] ?? null,
            'firma' => $signature,
        ];

        $sets = [
            'estado = :estado',
            'responsable_donacion = :responsable',
            "fecha_donacion = CASE WHEN :estado_donacion = 'DONADO' THEN CURDATE() ELSE fecha_donacion END",
            "activo = CASE WHEN :estado_activo = 'DONADO' THEN 0 ELSE activo END",
            'firma_integridad = :firma',
        ];

        foreach ($this->optionalColumns() as $column) {
            if (array_key_exists($column, $meta)) {
                $sets[] = "{$column} = :{$column}";
                $params[$column] = $meta[$column];
            }
        }

        $this->db->execute(
            "UPDATE inventario SET " . implode(', ', $sets) . " WHERE id = :id",
            $params
        );

        (new InventoryStateHistory($this->db))->record(
            $id,
            $userId,
            (string) $item['estado'],
            $status,
            (string) ($meta['motivo'] ?? 'Cambio manual de estado'),
            (string) ($meta['observacion'] ?? ''),
            $signatureId,
            [
                'entidad_origen' => $meta['entidad_origen'] ?? 'inventario',
                'entidad_id' => $meta['entidad_id'] ?? $id,
                'audit_id' => $auditId,
            ]
        );
    }

    public function dashboardCounts(array $filters = []): array
    {
        [$join, $where, $params] = $this->dashboardFilterSql($filters);

        return $this->db->fetch(
            "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(i.estado = 'DISPONIBLE'), 0) AS disponibles,
                COALESCE(SUM(i.estado = 'ASIGNADO'), 0) AS asignados,
                COALESCE(SUM(i.estado = 'DANADO'), 0) AS danados,
                COALESCE(SUM(i.estado = 'DESCARTE'), 0) AS descarte,
                COALESCE(SUM(i.estado IN ('DANADO', 'DESCARTE')), 0) AS danados_descarte,
                COALESCE(SUM(i.es_licencia = 1), 0) AS licencias,
                MAX(i.updated_at) AS ultimo_cambio
             FROM inventario i
             {$join}
             WHERE " . implode(' AND ', $where),
            $params
        ) ?? [];
    }

    public function dashboardFilterOptions(): array
    {
        return [
            'tipos' => $this->db->fetchAll(
                "SELECT DISTINCT tipo_activo AS value
                 FROM inventario
                 WHERE activo = 1 AND tipo_activo IS NOT NULL AND tipo_activo <> ''
                 ORDER BY tipo_activo"
            ),
            'estados' => $this->db->fetchAll(
                "SELECT DISTINCT estado AS value
                 FROM inventario
                 WHERE activo = 1 AND estado IS NOT NULL AND estado <> ''
                 ORDER BY estado"
            ),
            'ubicaciones' => $this->db->fetchAll(
                "SELECT DISTINCT c.ubicacion AS value
                 FROM asignaciones a
                 INNER JOIN colaboradores c ON c.id = a.colaborador_id
                 INNER JOIN inventario i ON i.id = a.inventario_id
                 WHERE a.estado = 'ACTIVA'
                   AND i.activo = 1
                   AND c.ubicacion IS NOT NULL
                   AND c.ubicacion <> ''
                 ORDER BY c.ubicacion"
            ),
        ];
    }

    public function licenseSummary(array $filters = []): array
    {
        [$join, $where, $params] = $this->dashboardFilterSql($filters);
        $where[] = 'i.es_licencia = 1';
        $quantityColumn = $this->db->columnExists('inventario', 'cantidad') ? 'i.cantidad' : '1';
        $expirationColumn = $this->db->columnExists('inventario', 'fecha_vencimiento_licencia') ? 'i.fecha_vencimiento_licencia' : 'NULL';
        $statusColumn = $this->db->columnExists('inventario', 'estado_licencia') ? 'i.estado_licencia' : "'ACTIVA'";
        $licenseJoin = '';
        $usedQuantity = '0';

        if ($this->db->tableExists('licencia_asignaciones')) {
            $licenseJoin = "LEFT JOIN (
                    SELECT inventario_id, SUM(cantidad) AS usados
                    FROM licencia_asignaciones
                    WHERE estado = 'ACTIVA'
                    GROUP BY inventario_id
                ) licencia_uso ON licencia_uso.inventario_id = i.id";
            $usedQuantity = 'COALESCE(licencia_uso.usados, 0)';
        }

        return $this->db->fetch(
            "SELECT
                COUNT(*) AS registradas,
                COALESCE(SUM(GREATEST({$quantityColumn} - {$usedQuantity}, 0)), 0) AS cupos_disponibles,
                COALESCE(SUM(GREATEST({$quantityColumn} - {$usedQuantity}, 0) > 0), 0) AS con_cupos,
                COALESCE(SUM({$expirationColumn} IS NOT NULL AND {$expirationColumn} < CURDATE()), 0) AS vencidas,
                COALESCE(SUM({$expirationColumn} IS NOT NULL AND {$expirationColumn} >= CURDATE() AND {$expirationColumn} <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)), 0) AS proximas_vencer,
                COALESCE(SUM({$statusColumn} = 'VENCIDA'), 0) AS estado_vencida
             FROM inventario i
             {$join}
             {$licenseJoin}
             WHERE " . implode(' AND ', $where),
            $params
        ) ?? [];
    }

    private function dashboardFilterSql(array $filters): array
    {
        $join = '';
        $where = ['i.activo = 1'];
        $params = [];

        if (!empty($filters['tipo'])) {
            $where[] = 'i.tipo_activo = :dashboard_tipo';
            $params['dashboard_tipo'] = (string) $filters['tipo'];
        }

        if (!empty($filters['estado'])) {
            $where[] = 'i.estado = :dashboard_estado';
            $params['dashboard_estado'] = (string) $filters['estado'];
        }

        if (!empty($filters['categoria_id'])) {
            $where[] = 'i.categoria_id = :dashboard_categoria_id';
            $params['dashboard_categoria_id'] = (int) $filters['categoria_id'];
        }

        if (!empty($filters['ubicacion'])) {
            $join .= " LEFT JOIN asignaciones dashboard_asignacion
                        ON dashboard_asignacion.inventario_id = i.id
                       AND dashboard_asignacion.estado = 'ACTIVA'
                       LEFT JOIN colaboradores dashboard_colaborador
                        ON dashboard_colaborador.id = dashboard_asignacion.colaborador_id";
            $where[] = 'dashboard_colaborador.ubicacion = :dashboard_ubicacion';
            $params['dashboard_ubicacion'] = (string) $filters['ubicacion'];
        }

        return [$join, $where, $params];
    }

    private function optionalColumns(): array
    {
        $columns = [
            'es_licencia',
            'clave_licencia',
            'cantidad',
            'responsable_donacion',
            'fecha_donacion',
            'proveedor_licencia',
            'tipo_licencia',
            'fecha_adquisicion_licencia',
            'url_licencia',
            'fecha_vencimiento_licencia',
            'observaciones_licencia',
            'estado_licencia',
            'clave_licencia_cifrada',
            'clave_licencia_hash',
            'clave_licencia_algoritmo',
            'clave_licencia_migrada_at',
            'beneficiario_donacion',
            'evidencia_donacion',
            'observacion_donacion',
            'observacion_tecnica_descarte',
            'evaluador_descarte_id',
            'responsable_descarte_id',
            'motivo_descarte',
            'fecha_evaluacion_descarte',
            'evidencia_descarte',
            'valor_donacion',
            'autorizador_donacion_id',
        ];

        return $this->availableColumns($columns);
    }

    private function requiredColumns(): array
    {
        return [
            'categoria_id',
            'codigo_activo',
            'nombre',
            'tipo_activo',
            'subcategoria',
            'marca',
            'modelo',
            'serie',
            'costo',
            'fecha_ingreso',
            'vida_util_meses',
            'estado',
            'notas',
            'firma_integridad',
            'activo',
        ];
    }

    private function availableColumns(array $columns): array
    {
        return array_values(array_filter(
            $columns,
            fn (string $column): bool => $this->db->columnExists('inventario', $column)
        ));
    }
}

<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Collaborator
{
    public const LOCATION_TYPES = ['OFICINA', 'EDIFICIO', 'CASA', 'SEDE', 'DIRECCION', 'OTRO'];

    public function __construct(private Database $db)
    {
    }

    public function locationHistoryReady(): bool
    {
        return $this->db->tableExists('ubicaciones_historial');
    }

    public function all(bool $activeOnly = false): array
    {
        $sql = "SELECT * FROM colaboradores";
        if ($activeOnly) {
            $sql .= " WHERE activo = 1";
        }
        $sql .= " ORDER BY nombres, apellidos";

        return $this->db->fetchAll($sql);
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM colaboradores WHERE id = :id", ['id' => $id]);
    }

    public function findByIdentification(string $identification): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM colaboradores WHERE identificacion = :identificacion",
            ['identificacion' => $identification]
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO colaboradores
            (nombres, apellidos, identificacion, departamento, ubicacion, direccion, telefono, email, foto, activo)
            VALUES
            (:nombres, :apellidos, :identificacion, :departamento, :ubicacion, :direccion, :telefono, :email, :foto, :activo)",
            $data
        );
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $sql = "UPDATE colaboradores SET
                nombres = :nombres, apellidos = :apellidos, identificacion = :identificacion,
                departamento = :departamento, ubicacion = :ubicacion, direccion = :direccion,
                telefono = :telefono, email = :email, activo = :activo";

        if (!empty($data['foto'])) {
            $sql .= ", foto = :foto";
        } else {
            unset($data['foto']);
        }

        $sql .= " WHERE id = :id";
        $this->db->execute($sql, $data);
    }

    public function recordLocationHistory(
        int $collaboratorId,
        ?string $previousLocation,
        ?string $newLocation,
        ?int $actorId,
        ?int $auditId,
        string $reason
    ): void {
        if (!$this->locationHistoryReady()) {
            return;
        }

        $previousLocation = self::cleanLocation($previousLocation);
        $newLocation = self::cleanLocation($newLocation);
        if ($previousLocation === $newLocation && $previousLocation !== null) {
            return;
        }

        $this->closeOpenLocation($collaboratorId);

        $columns = ['colaborador_id', 'ubicacion_anterior', 'ubicacion_nueva', 'tipo'];
        $data = [
            'colaborador_id' => $collaboratorId,
            'ubicacion_anterior' => $previousLocation,
            'ubicacion_nueva' => $newLocation,
            'tipo' => ($newLocation !== null && $newLocation !== '') ? self::normalizeLocation($newLocation) : null,
        ];

        foreach ([
            'fecha_inicio' => date('Y-m-d'),
            'fecha_fin' => null,
            'motivo' => trim($reason) !== '' ? $reason : null,
            'usuario_id' => $actorId,
            'audit_id' => $auditId,
        ] as $column => $value) {
            if ($this->db->columnExists('ubicaciones_historial', $column)) {
                $columns[] = $column;
                $data[$column] = $value;
            }
        }

        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

        $this->db->insert(
            "INSERT INTO ubicaciones_historial (" . implode(', ', $columns) . ")
             VALUES (" . implode(', ', $placeholders) . ")",
            array_intersect_key($data, array_flip($columns))
        );
    }

    public function locationHistory(int $collaboratorId): array
    {
        if (!$this->locationHistoryReady()) {
            return [];
        }

        return $this->db->fetchAll(
            "SELECT h.*, u.nombre_usuario
             FROM ubicaciones_historial h
             LEFT JOIN usuarios u ON u.id = h.usuario_id
             WHERE h.colaborador_id = :id
             ORDER BY h.created_at DESC, h.id DESC",
            ['id' => $collaboratorId]
        );
    }

    public static function normalizeLocation(string $location): string
    {
        $trimmed = trim($location);
        if ($trimmed === '') {
            return 'OTRO';
        }

        $upper = strtoupper($trimmed);
        foreach (self::LOCATION_TYPES as $type) {
            if (str_contains($upper, $type)) {
                return $type;
            }
        }

        return 'OTRO';
    }

    private function closeOpenLocation(int $collaboratorId): void
    {
        if (!$this->db->columnExists('ubicaciones_historial', 'fecha_fin')) {
            return;
        }

        $this->db->execute(
            "UPDATE ubicaciones_historial
             SET fecha_fin = CURDATE()
             WHERE colaborador_id = :id AND fecha_fin IS NULL",
            ['id' => $collaboratorId]
        );
    }

    private static function cleanLocation(?string $location): ?string
    {
        $trimmed = trim((string) $location);

        return $trimmed === '' ? null : $trimmed;
    }
}

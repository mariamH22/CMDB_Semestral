<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Category
{
    public function __construct(private Database $db)
    {
    }

    public function all(bool $onlyActive = false): array
    {
        $sql = "SELECT * FROM categorias";
        if ($onlyActive) {
            $sql .= " WHERE activo = 1";
        }
        $sql .= " ORDER BY tipo, nombre";

        return $this->db->fetchAll($sql);
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM categorias WHERE id = :id", ['id' => $id]);
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO categorias (nombre, tipo, descripcion, activo)
             VALUES (:nombre, :tipo, :descripcion, :activo)",
            $data
        );
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $this->db->execute(
            "UPDATE categorias
             SET nombre = :nombre, tipo = :tipo, descripcion = :descripcion, activo = :activo
             WHERE id = :id",
            $data
        );
    }

    public function deactivate(int $id): void
    {
        $this->db->execute("UPDATE categorias SET activo = 0 WHERE id = :id", ['id' => $id]);
    }
}

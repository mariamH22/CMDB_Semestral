<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class News
{
    public function __construct(private Database $db)
    {
    }

    public function published(): array
    {
        return $this->db->fetchAll(
            "SELECT n.*, u.nombre_usuario AS autor
             FROM noticias n
             LEFT JOIN usuarios u ON u.id = n.usuario_id
             WHERE n.publicada = 1
             ORDER BY n.created_at DESC"
        );
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            "SELECT n.*, u.nombre_usuario AS autor
             FROM noticias n
             LEFT JOIN usuarios u ON u.id = n.usuario_id
             ORDER BY n.created_at DESC"
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM noticias WHERE id = :id", ['id' => $id]);
    }

    public function create(array $data): int
    {
        return $this->db->insert(
            "INSERT INTO noticias (usuario_id, titulo, resumen, contenido, imagen, publicada)
             VALUES (:usuario_id, :titulo, :resumen, :contenido, :imagen, :publicada)",
            $data
        );
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $sql = "UPDATE noticias SET titulo = :titulo, resumen = :resumen, contenido = :contenido, publicada = :publicada";

        if (!empty($data['imagen'])) {
            $sql .= ", imagen = :imagen";
        } else {
            unset($data['imagen']);
        }

        $sql .= " WHERE id = :id";
        $this->db->execute($sql, $data);
    }
}

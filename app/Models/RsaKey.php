<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\ServiceContainer;

final class RsaKey
{
    public function __construct(private Database $db)
    {
    }

    public function schemaReady(): bool
    {
        return $this->db->tableExists('llaves_rsa');
    }

    public function activeForUser(int $userId): ?array
    {
        if (!$this->schemaReady() || $userId < 1) {
            return null;
        }

        return $this->db->fetch(
            "SELECT *
             FROM llaves_rsa
             WHERE usuario_id = :usuario_id
               AND estado = 'ACTIVA'
             ORDER BY created_at DESC, id DESC
             LIMIT 1",
            ['usuario_id' => $userId]
        );
    }

    public function all(): array
    {
        if (!$this->schemaReady()) {
            return [];
        }

        return $this->db->fetchAll(
            "SELECT k.*, u.nombre_usuario
             FROM llaves_rsa k
             LEFT JOIN usuarios u ON u.id = k.usuario_id
             ORDER BY k.created_at DESC, k.id DESC"
        );
    }

    public function find(int $id): ?array
    {
        if (!$this->schemaReady() || $id < 1) {
            return null;
        }

        return $this->db->fetch(
            "SELECT k.*, u.nombre_usuario
             FROM llaves_rsa k
             LEFT JOIN usuarios u ON u.id = k.usuario_id
             WHERE k.id = :id",
            ['id' => $id]
        );
    }

    public function generateForUser(int $userId, string $name = 'Llave RSA de usuario'): int
    {
        if (!$this->supportsLifecycle()) {
            throw new \RuntimeException('La migración completa de llaves RSA no está aplicada.');
        }

        if (!ServiceContainer::keyManagement()->isConfigured()) {
            throw new \RuntimeException('El almacén seguro de llaves no está configurado.');
        }

        if ($this->activeForUser($userId)) {
            throw new \RuntimeException('El usuario ya tiene una llave RSA activa. Use rotación si desea reemplazarla.');
        }

        $key = ServiceContainer::keyManagement()->generateForUser($userId, $name);
        if ($key === null) {
            throw new \RuntimeException('No fue posible generar la llave RSA protegida.');
        }

        return $this->registerGenerated($key);
    }

    public function rotateForUser(int $userId, string $name = 'Llave RSA rotada'): int
    {
        if (!$this->supportsLifecycle()) {
            throw new \RuntimeException('La migración completa de llaves RSA no está aplicada.');
        }

        if (!ServiceContainer::keyManagement()->isConfigured()) {
            throw new \RuntimeException('El almacén seguro de llaves no está configurado.');
        }

        return $this->db->transaction(function (Database $db) use ($userId, $name): int {
            $active = $db->fetch(
                "SELECT *
                 FROM llaves_rsa
                 WHERE usuario_id = :usuario_id
                   AND estado = 'ACTIVA'
                 ORDER BY created_at DESC, id DESC
                 LIMIT 1
                 FOR UPDATE",
                ['usuario_id' => $userId]
            );

            if (!$active) {
                throw new \RuntimeException('El usuario no tiene una llave activa para rotar.');
            }

            $sets = ["estado = 'REEMPLAZADA'"];
            if ($this->db->columnExists('llaves_rsa', 'replaced_at')) {
                $sets[] = 'replaced_at = NOW()';
            }

            $db->execute(
                "UPDATE llaves_rsa SET " . implode(', ', $sets) . " WHERE id = :id",
                ['id' => (int) $active['id']]
            );

            $key = ServiceContainer::keyManagement()->generateForUser($userId, $name);
            if ($key === null) {
                throw new \RuntimeException('No fue posible generar la nueva llave RSA.');
            }

            return $this->registerGenerated($key);
        });
    }

    public function revoke(int $id, string $reason, ?int $actorId = null): void
    {
        if (!$this->supportsLifecycle()) {
            throw new \RuntimeException('La migración completa de llaves RSA no está aplicada.');
        }

        if (!$this->find($id)) {
            throw new \RuntimeException('La llave RSA indicada no existe.');
        }

        $sets = [
            "estado = 'REVOCADA'",
            'revoked_at = NOW()',
        ];
        $params = ['id' => $id];

        if ($this->db->columnExists('llaves_rsa', 'revocation_reason')) {
            $sets[] = 'revocation_reason = :reason';
            $params['reason'] = $reason;
        }

        if ($this->db->columnExists('llaves_rsa', 'revoked_by')) {
            $sets[] = 'revoked_by = :actor_id';
            $params['actor_id'] = $actorId;
        }

        $this->db->execute(
            "UPDATE llaves_rsa SET " . implode(', ', $sets) . " WHERE id = :id",
            $params
        );
    }

    public function supportsLifecycle(): bool
    {
        if (!$this->schemaReady()) {
            return false;
        }

        foreach (['key_store_reference', 'private_key_encrypted', 'algoritmo', 'bits', 'replaced_at', 'revocation_reason', 'revoked_by'] as $column) {
            if (!$this->db->columnExists('llaves_rsa', $column)) {
                return false;
            }
        }

        return true;
    }

    public function registerGenerated(array $key): int
    {
        if (!$this->schemaReady()) {
            throw new \RuntimeException('La migración de llaves RSA no está aplicada.');
        }

        $columns = ['usuario_id', 'nombre', 'public_key', 'fingerprint', 'estado'];
        $data = [
            'usuario_id' => (int) $key['usuario_id'],
            'nombre' => (string) $key['nombre'],
            'public_key' => (string) $key['public_key'],
            'fingerprint' => (string) $key['fingerprint'],
            'estado' => (string) ($key['estado'] ?? 'ACTIVA'),
        ];

        foreach (['key_store_reference', 'private_key_encrypted', 'algoritmo', 'bits'] as $column) {
            if ($this->db->columnExists('llaves_rsa', $column) && array_key_exists($column, $key)) {
                $columns[] = $column;
                $data[$column] = $key[$column];
            }
        }

        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

        return $this->db->insert(
            "INSERT INTO llaves_rsa (" . implode(', ', $columns) . ")
             VALUES (" . implode(', ', $placeholders) . ")",
            array_intersect_key($data, array_flip($columns))
        );
    }
}

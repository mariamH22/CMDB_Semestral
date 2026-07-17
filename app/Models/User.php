<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Config;
use App\Core\Database;
use App\Core\Contracts\PasswordHasherInterface;
use App\Core\ServiceContainer;

final class User
{
    private PasswordHasherInterface $passwordHasher;

    public function __construct(private Database $db, ?PasswordHasherInterface $passwordHasher = null)
    {
        $this->passwordHasher = $passwordHasher ?? ServiceContainer::passwordHasher();
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            "SELECT u.*, CONCAT(COALESCE(c.nombres,''), ' ', COALESCE(c.apellidos,'')) AS colaborador_nombre
             FROM usuarios u
             LEFT JOIN colaboradores c ON c.id = u.colaborador_id
             ORDER BY u.created_at DESC"
        );
    }

    public function activeCollaborators(): array
    {
        return $this->db->fetchAll(
            "SELECT c.*
             FROM colaboradores c
             LEFT JOIN usuarios u ON u.colaborador_id = c.id
             WHERE c.activo = 1 AND u.id IS NULL
             ORDER BY c.nombres, c.apellidos"
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM usuarios WHERE id = :id", ['id' => $id]);
    }

    public function findByLogin(string $login): ?array
{
    return $this->db->fetch(
        "SELECT * FROM usuarios 
         WHERE email = :email 
            OR nombre_usuario = :username 
         LIMIT 1",
        [
            'email' => $login,
            'username' => $login,
        ]
    );
}

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch("SELECT * FROM usuarios WHERE email = :email", ['email' => $email]);
    }

    public function create(array $data): int
    {
        $hashedPassword = $this->passwordHasher->hash((string) $data['password']);

        return $this->db->insert(
            "INSERT INTO usuarios (colaborador_id, nombre_usuario, email, password_hash, rol, activo, estado_cuenta)
             VALUES (:colaborador_id, :nombre_usuario, :email, :password_hash, :rol, :activo, 'ACTIVO')",
            [
                'colaborador_id' => $data['colaborador_id'] ?: null,
                'nombre_usuario' => $data['nombre_usuario'],
                'email' => $data['email'],
                'password_hash' => $hashedPassword,
                'rol' => $data['rol'],
                'activo' => $data['activo'],
            ]
        );
    }

    public function update(int $id, array $data): void
    {
        $params = [
            'id' => $id,
            'colaborador_id' => $data['colaborador_id'] ?: null,
            'nombre_usuario' => $data['nombre_usuario'],
            'email' => $data['email'],
            'rol' => $data['rol'],
            'activo' => $data['activo'],
        ];

        $sql = "UPDATE usuarios
                SET colaborador_id = :colaborador_id, nombre_usuario = :nombre_usuario,
                    email = :email, rol = :rol, activo = :activo";

        if (!empty($data['password'])) {
            $sql .= ", password_hash = :password_hash";
            $params['password_hash'] = $this->passwordHasher->hash((string) $data['password']);
        }

        $sql .= " WHERE id = :id";

        $this->db->execute($sql, $params);
    }

    public function setActive(int $id, int $active): void
    {
        $this->db->execute(
            "UPDATE usuarios SET activo = :activo WHERE id = :id",
            ['id' => $id, 'activo' => $active]
        );
    }

    public function recordFailure(int $id): array
    {
        $this->db->execute(
            "UPDATE usuarios SET intentos_fallidos = intentos_fallidos + 1 WHERE id = :id",
            ['id' => $id]
        );

        $user = $this->find($id);

        // El umbral de bloqueo queda en configuracion para no duplicar numeros magicos.
        $maxAttempts = (int) Config::get('security.max_login_attempts', 3);

        if ($user && (int) $user['intentos_fallidos'] >= $maxAttempts) {
            $this->db->execute(
                "UPDATE usuarios SET estado_cuenta = 'BLOQUEADO' WHERE id = :id",
                ['id' => $id]
            );
            $user['estado_cuenta'] = 'BLOQUEADO';
        }

        return $user ?? [];
    }

    public function resetFailuresAndLogin(int $id): void
    {
        $this->db->execute(
            "UPDATE usuarios SET intentos_fallidos = 0, ultimo_login_at = NOW() WHERE id = :id",
            ['id' => $id]
        );
    }

    public function changePassword(int $id, string $password): void
    {
        $this->db->execute(
            "UPDATE usuarios SET password_hash = :hash WHERE id = :id",
            ['id' => $id, 'hash' => $this->passwordHasher->hash($password)]
        );
    }

    public function verifyPassword(array $user, string $password): bool
    {
        $storedHash = (string) ($user['password_hash'] ?? '');
        if (!$storedHash) {
            return false;
        }

        $valid = $this->passwordHasher->verify($password, $storedHash);
        if (!$valid) {
            return false;
        }

        if ($this->passwordHasher->needsRehash($storedHash)) {
            $this->db->execute(
                "UPDATE usuarios SET password_hash = :hash WHERE id = :id",
                ['id' => (int) $user['id'], 'hash' => $this->passwordHasher->hash($password)]
            );
        }

        return true;
    }

    public function unlock(int $id): void
    {
        $this->db->execute(
            "UPDATE usuarios SET estado_cuenta = 'ACTIVO', intentos_fallidos = 0 WHERE id = :id",
            ['id' => $id]
        );
    }
}

<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Security\PasswordHasher;

final class PasswordReset
{
    private PasswordHasher $passwordHasher;

    public function __construct(private Database $db)
    {
        $this->passwordHasher = new PasswordHasher();
    }

    public function create(int $userId, string $plainToken): void
    {
        $this->db->execute("UPDATE password_resets SET usado = 1 WHERE usuario_id = :id", ['id' => $userId]);

        $this->db->insert(
            "INSERT INTO password_resets (usuario_id, token_hash, expira_at)
             VALUES (:usuario_id, :token_hash, DATE_ADD(NOW(), INTERVAL 30 MINUTE))",
            [
                'usuario_id' => $userId,
                'token_hash' => $this->passwordHasher->hash($plainToken),
            ]
        );
    }

    public function findValidByToken(string $plainToken): ?array
    {
        $records = $this->db->fetchAll(
            "SELECT * FROM password_resets WHERE usado = 0 AND expira_at >= NOW() ORDER BY created_at DESC"
        );

        foreach ($records as $record) {
            if ($this->passwordHasher->verify($plainToken, (string) $record['token_hash'])) {
                return $record;
            }
        }

        return null;
    }

    public function use(int $id): void
    {
        $this->db->execute("UPDATE password_resets SET usado = 1 WHERE id = :id", ['id' => $id]);
    }
}

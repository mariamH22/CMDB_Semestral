<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\QrLifecyclePolicy;
use App\Core\QrPublicPayload;
use App\Core\QrToken;

final class InventoryQr
{
    public function __construct(private Database $db)
    {
    }

    public function schemaReady(): bool
    {
        return $this->db->tableExists('inventario_qr');
    }

    public function ensureForInventory(int $inventoryId): ?array
    {
        return $this->ensureForInventoryByUser($inventoryId, null);
    }

    public function ensureForInventoryByUser(int $inventoryId, ?int $userId): ?array
    {
        if (!$this->schemaReady()) {
            return null;
        }

        $qr = $this->activeForInventory($inventoryId);

        if ($qr) {
            return $qr;
        }

        return $this->createForInventory($inventoryId, $userId);
    }

    public function activeForInventory(int $inventoryId): ?array
    {
        if (!$this->schemaReady()) {
            return null;
        }

        return $this->db->fetch(
            "SELECT * FROM inventario_qr
             WHERE inventario_id = :id
               AND activo = 1
               AND revoked_at IS NULL
             ORDER BY id DESC
             LIMIT 1",
            ['id' => $inventoryId]
        );
    }

    public function regenerateForInventory(int $inventoryId, ?int $userId = null, string $reason = 'Regeneración manual'): ?array
    {
        if (!$this->schemaReady()) {
            return null;
        }

        // Regenerar revoca el QR anterior en la misma transaccion para que solo uno quede activo.
        return $this->db->transaction(function (Database $db) use ($inventoryId, $userId, $reason): ?array {
            $current = $db->fetch(
                "SELECT * FROM inventario_qr
                 WHERE inventario_id = :id
                   AND activo = 1
                   AND revoked_at IS NULL
                 ORDER BY id DESC
                 LIMIT 1
                 FOR UPDATE",
                ['id' => $inventoryId]
            );

            if ($current) {
                $this->revokeRow((int) $current['id'], $userId, $reason);
            }

            return $this->createForInventory($inventoryId, $userId, $current ? (int) $current['id'] : null);
        });
    }

    public function revokeForInventory(int $inventoryId, ?int $userId = null, string $reason = 'Revocación manual'): bool
    {
        if (!$this->schemaReady()) {
            return false;
        }

        $qr = $this->activeForInventory($inventoryId);
        if (!$qr) {
            return false;
        }

        $this->revokeRow((int) $qr['id'], $userId, $reason);

        return true;
    }

    public function findPublicAssetByToken(string $token, ?int $expectedInventoryId = null): ?array
    {
        if (!$this->schemaReady() || !QrToken::isValid($token)) {
            return null;
        }

        $token = QrToken::normalize($token);
        // Se busca por token_hash cuando existe para no depender de tokens en texto claro.
        $where = $this->db->columnExists('inventario_qr', 'token_hash')
            ? '(q.token_hash = :token_hash OR q.token = :token)'
            : 'q.token = :token';
        $params = ['token' => $token];
        if ($this->db->columnExists('inventario_qr', 'token_hash')) {
            $params['token_hash'] = QrToken::hash($token);
        }

        $row = $this->db->fetch(
            "SELECT q.id AS qr_id, q.inventario_id, q.token, q.payload_hash,
                    q.activo, q.revoked_at" . ($this->db->columnExists('inventario_qr', 'estado') ? ", q.estado" : ", 'ACTIVO' AS estado") . ",
                    i.codigo_activo, i.nombre, i.marca, i.estado, i.costo, i.fecha_ingreso,
                    c.nombre AS categoria_nombre
             FROM inventario_qr q
             INNER JOIN inventario i ON i.id = q.inventario_id
             LEFT JOIN categorias c ON c.id = i.categoria_id
             WHERE {$where}
               AND q.activo = 1
               AND q.revoked_at IS NULL
               AND i.activo = 1
             LIMIT 1",
            $params
        );

        if (!$row) {
            return null;
        }

        if (!QrLifecyclePolicy::isActive($row)) {
            return null;
        }

        if ($expectedInventoryId !== null && (int) $row['inventario_id'] !== $expectedInventoryId) {
            return null;
        }

        if (!QrToken::verifyPayloadHash((int) $row['inventario_id'], $token, (string) $row['payload_hash'])) {
            return null;
        }

        // El payload publico excluye datos sensibles y solo contiene informacion autorizada del activo.
        return array_merge(QrPublicPayload::fromAsset($row), [
            '_qr_id' => (int) $row['qr_id'],
            '_inventario_id' => (int) $row['inventario_id'],
        ]);
    }

    public function recordAccess(int $qrId): void
    {
        if (!$this->schemaReady() || $qrId < 1) {
            return;
        }

        $sets = [];
        if ($this->db->columnExists('inventario_qr', 'last_accessed_at')) {
            $sets[] = 'last_accessed_at = NOW()';
        }
        if ($this->db->columnExists('inventario_qr', 'access_count')) {
            $sets[] = 'access_count = access_count + 1';
        }

        if (!$sets) {
            return;
        }

        $this->db->execute(
            "UPDATE inventario_qr SET " . implode(', ', $sets) . " WHERE id = :id",
            ['id' => $qrId]
        );
    }

    private function createForInventory(int $inventoryId, ?int $userId = null, ?int $regeneratedFromId = null): array
    {
        $token = QrToken::generate();
        // payload_hash une el token con el activo para detectar QR manipulados o reutilizados.
        $columns = ['inventario_id', 'token', 'payload_hash', 'activo'];
        $data = [
            'inventario_id' => $inventoryId,
            'token' => $token,
            'payload_hash' => QrToken::payloadHash($inventoryId, $token),
            'activo' => 1,
        ];

        foreach ([
            'token_hash' => QrToken::hash($token),
            'created_by' => $userId,
            'regenerated_from_id' => $regeneratedFromId,
        ] as $column => $value) {
            if ($this->db->columnExists('inventario_qr', $column)) {
                $columns[] = $column;
                $data[$column] = $value;
            }
        }

        if ($this->db->columnExists('inventario_qr', 'estado')) {
            $columns[] = 'estado';
            $data['estado'] = 'ACTIVO';
        }

        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);
        $id = $this->db->insert(
            "INSERT INTO inventario_qr (" . implode(', ', $columns) . ")
             VALUES (" . implode(', ', $placeholders) . ")",
            array_intersect_key($data, array_flip($columns))
        );

        return array_merge($data, ['id' => $id]);
    }

    private function revokeRow(int $qrId, ?int $userId, string $reason): void
    {
        $params = ['id' => $qrId];
        $sets = ['activo = 0', 'revoked_at = NOW()'];

        foreach ([
            'revoked_by' => $userId,
            'revoked_reason' => $reason,
            'estado' => 'REVOCADO',
        ] as $column => $value) {
            if ($this->db->columnExists('inventario_qr', $column)) {
                $sets[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
        }

        $this->db->execute(
            "UPDATE inventario_qr SET " . implode(', ', $sets) . " WHERE id = :id",
            $params
        );
    }
}

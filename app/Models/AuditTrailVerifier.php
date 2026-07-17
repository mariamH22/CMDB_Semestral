<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Security\AuditTrailService;
use App\Core\ServiceContainer;

final class AuditTrailVerifier
{
    public function __construct(private Database $db)
    {
    }

    public function schemaReady(): bool
    {
        return (new AuditLog($this->db))->supportsTrail();
    }

    public function verify(int $limit = 300): array
    {
        if (!$this->schemaReady()) {
            return [
                'schemaReady' => false,
                'total' => 0,
                'valid' => 0,
                'invalid' => 0,
                'notVerifiable' => 0,
                'results' => [],
            ];
        }

        $limit = max(1, min($limit, 1000));
        $rows = $this->db->fetchAll(
            "SELECT *
             FROM bitacora
             ORDER BY id ASC
             LIMIT {$limit}"
        );

        $results = ServiceContainer::auditTrail()->verifyRows(
            $rows,
            fn (array $row): ?string => $this->verifySignatureStatus($row)
        );

        $valid = 0;
        $notVerifiable = 0;
        foreach ($results as $result) {
            if ($result['status'] === AuditTrailService::STATUS_VALID) {
                $valid++;
            }
            if ($result['status'] === AuditTrailService::STATUS_NOT_VERIFIABLE) {
                $notVerifiable++;
            }
        }

        return [
            'schemaReady' => true,
            'total' => count($results),
            'valid' => $valid,
            'invalid' => count($results) - $valid - $notVerifiable,
            'notVerifiable' => $notVerifiable,
            'results' => $results,
        ];
    }

    private function verifySignatureStatus(array $row): ?string
    {
        $signatureId = (int) ($row['firma_id'] ?? 0);
        if ($signatureId < 1) {
            return null;
        }

        if (!$this->db->tableExists('firmas_digitales') || !$this->db->tableExists('llaves_rsa')) {
            return null;
        }

        $signature = $this->db->fetch(
            "SELECT f.payload_hash, f.firma, k.public_key, k.estado AS llave_estado
             FROM firmas_digitales f
             INNER JOIN llaves_rsa k ON k.id = f.llave_id
             WHERE f.id = :id",
            ['id' => $signatureId]
        );

        if (!$signature) {
            return null;
        }

        return ServiceContainer::signatureVerification()->verify(
            (string) $signature['payload_hash'],
            (string) $signature['firma'],
            (string) $signature['public_key'],
            (string) $signature['llave_estado']
        );
    }
}

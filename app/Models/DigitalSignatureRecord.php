<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\ServiceContainer;
use App\Core\Security\SignatureVerificationService;

final class DigitalSignatureRecord
{
    public function __construct(private Database $db)
    {
    }

    public function schemaReady(): bool
    {
        return $this->db->tableExists('firmas_digitales') && $this->db->tableExists('llaves_rsa');
    }

    public function verifiedRecent(int $limit = 80): array
    {
        if (!$this->schemaReady()) {
            return [];
        }

        $limit = max(1, min($limit, 300));
        $fingerprintSelect = $this->db->columnExists('firmas_digitales', 'fingerprint')
            ? 'COALESCE(f.fingerprint, k.fingerprint) AS firma_fingerprint,'
            : 'k.fingerprint AS firma_fingerprint,';

        $rows = $this->db->fetchAll(
            "SELECT f.*, k.nombre AS llave_nombre, {$fingerprintSelect} k.estado AS llave_estado,
                    k.public_key, u.nombre_usuario
             FROM firmas_digitales f
             INNER JOIN llaves_rsa k ON k.id = f.llave_id
             LEFT JOIN usuarios u ON u.id = f.usuario_id
             ORDER BY f.created_at DESC
             LIMIT {$limit}"
        );

        foreach ($rows as &$row) {
            $row['fingerprint'] = $row['firma_fingerprint'] ?? '';
            $row['verificacion'] = $this->verifyRow($row);

            unset($row['public_key']);
        }

        return $rows;
    }

    public function verifyById(int $id): string
    {
        if (!$this->schemaReady() || $id < 1) {
            return SignatureVerificationService::NOT_VERIFIABLE;
        }

        $row = $this->db->fetch(
            "SELECT f.*, k.public_key, k.estado AS llave_estado
             FROM firmas_digitales f
             INNER JOIN llaves_rsa k ON k.id = f.llave_id
             WHERE f.id = :id",
            ['id' => $id]
        );

        if (!$row) {
            return SignatureVerificationService::NOT_VERIFIABLE;
        }

        $status = $this->verifyRow($row);
        $this->storeVerificationResult($id, $status);

        return $status;
    }

    private function verifyRow(array $row): string
    {
        $payloadJson = (string) ($row['payload_json'] ?? '');
        $payloadHash = (string) ($row['payload_hash'] ?? '');

        if ($payloadJson !== '' && !hash_equals(hash('sha256', $payloadJson), $payloadHash)) {
            return SignatureVerificationService::INVALID;
        }

        return ServiceContainer::signatureVerification()->verify(
            $payloadHash,
            (string) ($row['firma'] ?? ''),
            (string) ($row['public_key'] ?? ''),
            (string) ($row['llave_estado'] ?? '')
        );
    }

    private function storeVerificationResult(int $id, string $status): void
    {
        $sets = [];
        $params = ['id' => $id];

        if ($this->db->columnExists('firmas_digitales', 'resultado_verificacion')) {
            $sets[] = 'resultado_verificacion = :resultado';
            $params['resultado'] = $status;
        }

        if ($this->db->columnExists('firmas_digitales', 'verified_at')) {
            $sets[] = 'verified_at = NOW()';
        }

        if ($sets === []) {
            return;
        }

        $this->db->execute(
            "UPDATE firmas_digitales SET " . implode(', ', $sets) . " WHERE id = :id",
            $params
        );
    }
}

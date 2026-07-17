<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\AuditLog;

final class DigitalSignature
{
    public static function signAction(
        Database $db,
        ?int $userId,
        string $module,
        string $action,
        string $entity,
        ?int $entityId,
        array $payload,
        ?int $auditId = null
    ): ?int {
        if ($userId === null || !$db->tableExists('llaves_rsa') || !$db->tableExists('firmas_digitales')) {
            return null;
        }

        if (!ServiceContainer::keyManagement()->isConfigured()) {
            return null;
        }

        $key = $db->fetch(
            "SELECT *
             FROM llaves_rsa
             WHERE usuario_id = :usuario_id
               AND estado = 'ACTIVA'
             ORDER BY id DESC
             LIMIT 1",
            ['usuario_id' => $userId]
        );

        if (!$key) {
            return null;
        }

        $reference = (string) ($key['key_store_reference'] ?? $key['private_key_path'] ?? '');
        if ($reference === '') {
            return null;
        }

        $signedPayload = ServiceContainer::signedPayloadFactory()->build(
            $userId,
            $action,
            $entity,
            $entityId,
            [
                'modulo' => $module,
                'datos' => $payload,
            ],
            $auditId,
            (string) $key['fingerprint']
        );
        $payloadHash = ServiceContainer::signedPayloadFactory()->hash($signedPayload);
        $payloadJson = ServiceContainer::signedPayloadFactory()->canonical($signedPayload);

        $privateKey = ServiceContainer::keyManagement()->loadPrivateKey($reference);
        if ($privateKey === null) {
            return null;
        }

        $signature = ServiceContainer::digitalSignature()->sign($payloadHash, $privateKey);
        if ($signature === null) {
            return null;
        }

        $columns = ['llave_id', 'usuario_id', 'modulo', 'accion', 'entidad', 'entidad_id', 'payload_hash', 'firma', 'algoritmo'];
        $data = [
            'llave_id' => (int) $key['id'],
            'usuario_id' => $userId,
            'modulo' => $module,
            'accion' => $action,
            'entidad' => $entity,
            'entidad_id' => $entityId,
            'payload_hash' => $payloadHash,
            'firma' => $signature,
            'algoritmo' => 'RSA-SHA256',
        ];

        $optional = [
            'fingerprint' => $key['fingerprint'],
            'payload_version' => 1,
            'audit_id' => $auditId,
            'correlation_id' => $signedPayload['correlation_id'],
            'payload_json' => $payloadJson,
            'resultado_inicial' => 'VALIDA',
        ];

        foreach ($optional as $column => $value) {
            if ($db->columnExists('firmas_digitales', $column)) {
                $columns[] = $column;
                $data[$column] = $value;
            }
        }

        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

        $signatureId = $db->insert(
            "INSERT INTO firmas_digitales (" . implode(', ', $columns) . ")
             VALUES (" . implode(', ', $placeholders) . ")",
            array_intersect_key($data, array_flip($columns))
        );

        if ($auditId !== null) {
            (new AuditLog($db))->attachSignature($auditId, $signatureId);
        }

        return $signatureId;
    }

    public static function verifyRecorded(string $payloadHash, string $signature, string $publicKey): ?bool
    {
        if (!preg_match('/\A[a-f0-9]{64}\z/i', $payloadHash)) {
            return null;
        }

        return ServiceContainer::signatureVerifier()->verify($payloadHash, $signature, $publicKey);
    }

    private static function payloadHash(array $payload): ?string
    {
        return hash('sha256', ServiceContainer::canonicalPayload()->serialize($payload));
    }
}

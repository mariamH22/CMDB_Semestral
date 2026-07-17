<?php
declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Contracts\CanonicalPayloadInterface;

final class AuditTrailService
{
    public const STATUS_VALID = 'CADENA_VALIDA';
    public const STATUS_HASH_INVALID = 'HASH_INCORRECTO';
    public const STATUS_CHAIN_BROKEN = 'CADENA_ROTA';
    public const STATUS_PAYLOAD_ALTERED = 'PAYLOAD_ALTERADO';
    public const STATUS_SIGNATURE_INVALID = 'FIRMA_INVALIDA';
    public const STATUS_NOT_VERIFIABLE = 'EVENTO_NO_VERIFICABLE';

    public function __construct(
        private CanonicalPayloadInterface $serializer,
        private AuditDataSanitizer $sanitizer
    ) {
    }

    public function sanitize(array $data): array
    {
        return $this->sanitizer->sanitize($data);
    }

    public function payload(array $event): array
    {
        return [
            'version' => (int) ($event['payload_version'] ?? 1),
            'usuario_id' => $this->nullableInt($event['usuario_id'] ?? null),
            'modulo' => (string) ($event['modulo'] ?? ''),
            'accion' => (string) ($event['accion'] ?? ''),
            'entidad' => $event['entidad'] ?? null,
            'entidad_id' => $this->nullableInt($event['entidad_id'] ?? null),
            'fecha_hora' => (string) ($event['created_at'] ?? ''),
            'ip' => (string) ($event['ip'] ?? ''),
            'user_agent' => (string) ($event['user_agent'] ?? ''),
            'resultado' => (string) ($event['resultado'] ?? 'OK'),
            'motivo' => $event['motivo'] ?? null,
            'descripcion' => (string) ($event['descripcion'] ?? ''),
            'datos_anteriores' => $event['datos_anteriores'] ?? [],
            'datos_posteriores' => $event['datos_posteriores'] ?? [],
            'firma_id' => $this->nullableInt($event['firma_id'] ?? null),
            'fingerprint' => $event['fingerprint'] ?? null,
            'correlation_id' => (string) ($event['correlation_id'] ?? ''),
        ];
    }

    public function canonical(array $payload): string
    {
        return $this->serializer->serialize($payload);
    }

    public function hash(?string $previousHash, array $payload): string
    {
        return hash('sha256', (string) $previousHash . $this->canonical($payload));
    }

    public function eventFromRow(array $row): array
    {
        return [
            'payload_version' => (int) ($row['payload_version'] ?? 1),
            'usuario_id' => $row['usuario_id'] ?? null,
            'modulo' => $row['modulo'] ?? '',
            'accion' => $row['accion'] ?? '',
            'entidad' => $row['entidad'] ?? null,
            'entidad_id' => $row['entidad_id'] ?? null,
            'created_at' => $row['created_at'] ?? '',
            'ip' => $row['ip'] ?? '',
            'user_agent' => $row['user_agent'] ?? '',
            'resultado' => $row['resultado'] ?? 'OK',
            'motivo' => $row['motivo'] ?? null,
            'descripcion' => $row['descripcion'] ?? '',
            'datos_anteriores' => $this->decodeJson($row['datos_anteriores_json'] ?? null),
            'datos_posteriores' => $this->decodeJson($row['datos_posteriores_json'] ?? null),
            'firma_id' => $row['firma_id'] ?? null,
            'fingerprint' => $row['fingerprint'] ?? null,
            'correlation_id' => $row['correlation_id'] ?? '',
        ];
    }

    public function verifyRows(array $rows, ?callable $signatureVerifier = null): array
    {
        $results = [];
        $expectedPrevious = null;

        foreach ($rows as $row) {
            $storedPrevious = $row['previous_hash'] ?? null;
            $storedCurrent = (string) ($row['record_hash'] ?? '');
            $payload = $this->payload($this->eventFromRow($row));
            $expectedCurrent = $this->hash($storedPrevious ? (string) $storedPrevious : null, $payload);
            $status = self::STATUS_VALID;

            if (($storedPrevious ?: null) !== ($expectedPrevious ?: null)) {
                $status = self::STATUS_CHAIN_BROKEN;
            } elseif (!preg_match('/\A[a-f0-9]{64}\z/i', $storedCurrent)) {
                $status = self::STATUS_HASH_INVALID;
            } elseif (!hash_equals($expectedCurrent, $storedCurrent)) {
                $status = self::STATUS_PAYLOAD_ALTERED;
            }

            if ($status === self::STATUS_VALID && $signatureVerifier !== null) {
                $signatureStatus = $signatureVerifier($row);
                if ($signatureStatus === 'INVALIDA' || $signatureStatus === 'ERROR') {
                    $status = self::STATUS_SIGNATURE_INVALID;
                } elseif ($signatureStatus === null || $signatureStatus === 'NO_VERIFICABLE') {
                    $status = self::STATUS_NOT_VERIFIABLE;
                }
            }

            $results[] = [
                'id' => (int) ($row['id'] ?? 0),
                'status' => $status,
                'expected_hash' => $expectedCurrent,
                'stored_hash' => $storedCurrent,
                'expected_previous_hash' => $expectedPrevious,
                'stored_previous_hash' => $storedPrevious,
            ];

            $expectedPrevious = $storedCurrent ?: null;
        }

        return $results;
    }

    private function decodeJson(mixed $json): array
    {
        if (!is_string($json) || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}

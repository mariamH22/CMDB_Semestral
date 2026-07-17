<?php
declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Contracts\CanonicalPayloadInterface;

final class SignedPayloadFactory
{
    public function __construct(private CanonicalPayloadInterface $serializer)
    {
    }

    public function build(
        ?int $userId,
        string $action,
        string $entity,
        ?int $entityId,
        array $data,
        ?int $auditId,
        string $fingerprint,
        ?string $correlationId = null,
        ?string $timestamp = null
    ): array {
        $contentHash = hash('sha256', $this->serializer->serialize($data));

        return [
            'version' => 1,
            'usuario_id' => $userId,
            'accion' => $action,
            'entidad' => $entity,
            'entidad_id' => $entityId,
            'fecha_hora' => $timestamp ?? date(DATE_ATOM),
            'datos' => $data,
            'content_hash' => $contentHash,
            'audit_id' => $auditId,
            'fingerprint' => $fingerprint,
            'correlation_id' => $correlationId ?: bin2hex(random_bytes(16)),
        ];
    }

    public function canonical(array $payload): string
    {
        return $this->serializer->serialize($payload);
    }

    public function hash(array $payload): string
    {
        return hash('sha256', $this->canonical($payload));
    }
}


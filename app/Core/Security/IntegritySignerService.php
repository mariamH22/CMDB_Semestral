<?php
declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Config;
use App\Core\Contracts\CanonicalPayloadInterface;
use App\Core\Contracts\IntegrityServiceInterface;

final class IntegritySignerService implements IntegrityServiceInterface
{
    private bool $missingSecretLogged = false;

    public function __construct(private CanonicalPayloadInterface $serializer)
    {
    }

    public function isConfigured(): bool
    {
        return $this->resolveSecret() !== null;
    }

    public function sign(array $payload): string
    {
        $secret = $this->resolveSecret();
        if ($secret === null) {
            $this->logMissingSecretOnce();
            return '';
        }

        return hash_hmac('sha256', $this->serializer->serialize($payload), $secret);
    }

    public function verify(array $payload, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }

        $secret = $this->resolveSecret();
        if ($secret === null) {
            return false;
        }

        $expected = hash_hmac('sha256', $this->serializer->serialize($payload), $secret);

        return hash_equals($expected, (string) $signature);
    }

    private function resolveSecret(): ?string
    {
        $primary = $this->normalizeSecret(Config::get('security.integrity_key'));
        if ($primary !== null) {
            return $primary;
        }

        $legacy = $this->normalizeSecret(Config::get('security.integrity_legacy_key'));
        if ($legacy !== null) {
            return $legacy;
        }

        return null;
    }

    private function normalizeSecret(mixed $secret): ?string
    {
        if (!is_string($secret)) {
            return null;
        }

        $secret = trim($secret);
        if ($secret === '' || str_starts_with(strtolower($secret), 'cambiar_')) {
            return null;
        }

        return $secret;
    }

    private function logMissingSecretOnce(): void
    {
        if ($this->missingSecretLogged) {
            return;
        }

        $this->missingSecretLogged = true;
        error_log('CMDB security notice: integrity HMAC key is not configured; new integrity signature was skipped.');
    }
}

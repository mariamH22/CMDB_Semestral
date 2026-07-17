<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Contracts\EncryptionServiceInterface;

final class LicenseKeyProtector
{
    public function __construct(private EncryptionServiceInterface $encryption)
    {
    }

    public function isConfigured(): bool
    {
        return $this->encryption->isConfigured();
    }

    public function encryptForStorage(string $plainText): array
    {
        $plainText = trim($plainText);
        if ($plainText === '') {
            return ['ciphertext' => null, 'hash' => null, 'algorithm' => null];
        }

        $cipherText = $this->encryption->encrypt($plainText);
        if ($cipherText === null) {
            throw new \RuntimeException('La clave maestra de licencias no está configurada. No se guardó texto plano.');
        }

        return [
            'ciphertext' => $cipherText,
            'hash' => hash('sha256', $plainText),
            'algorithm' => $this->cipherName($cipherText),
        ];
    }

    public function decrypt(?string $cipherText, ?string $legacyValue = null): ?string
    {
        foreach ([$cipherText, $legacyValue] as $candidate) {
            $candidate = is_string($candidate) ? trim($candidate) : '';
            if ($candidate === '') {
                continue;
            }

            if ($this->isEncryptedPayload($candidate)) {
                return $this->encryption->decrypt($candidate);
            }
        }

        $legacyValue = is_string($legacyValue) ? trim($legacyValue) : '';

        return $legacyValue === '' ? null : $legacyValue;
    }

    public function mask(?string $cipherText, ?string $legacyValue = null): string
    {
        $plainText = $this->decrypt($cipherText, $legacyValue);
        if ($plainText === null) {
            return $this->hasAnyValue($cipherText, $legacyValue) ? '********' : '-';
        }

        return str_repeat('*', min(max(strlen($plainText), 8), 24));
    }

    public function isEncryptedPayload(?string $value): bool
    {
        $payload = json_decode((string) $value, true);

        return is_array($payload) && in_array((string) ($payload['cipher'] ?? ''), ['sodium-secretbox', 'aes-256-gcm'], true);
    }

    public function isLegacyPlaintext(?string $legacyValue, ?string $cipherText = null): bool
    {
        $legacyValue = is_string($legacyValue) ? trim($legacyValue) : '';
        if ($legacyValue === '') {
            return false;
        }

        return !$this->isEncryptedPayload($legacyValue) && !$this->isEncryptedPayload($cipherText);
    }

    public function cipherName(?string $cipherText): ?string
    {
        $payload = json_decode((string) $cipherText, true);

        return is_array($payload) ? ($payload['cipher'] ?? null) : null;
    }

    private function hasAnyValue(?string $cipherText, ?string $legacyValue): bool
    {
        return trim((string) $cipherText) !== '' || trim((string) $legacyValue) !== '';
    }
}

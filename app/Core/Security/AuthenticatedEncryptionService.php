<?php
declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Contracts\EncryptionServiceInterface;

final class AuthenticatedEncryptionService implements EncryptionServiceInterface
{
    private ?string $key;
    private AesGcmEncryptionService $aesFallback;

    public function __construct(?string $key)
    {
        $this->key = $this->normalizeKey($key);
        $this->aesFallback = new AesGcmEncryptionService($this->key);
    }

    public function isConfigured(): bool
    {
        return $this->key !== null && ($this->sodiumAvailable() || $this->aesFallback->isConfigured());
    }

    public function encrypt(string $plainText): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        if ($this->sodiumAvailable()) {
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $cipherText = sodium_crypto_secretbox($plainText, $nonce, $this->binaryKey());

            return json_encode([
                'version' => 1,
                'cipher' => 'sodium-secretbox',
                'nonce' => base64_encode($nonce),
                'ciphertext' => base64_encode($cipherText),
            ], JSON_UNESCAPED_SLASHES) ?: null;
        }

        return $this->aesFallback->encrypt($plainText);
    }

    public function decrypt(string $cipherText): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $payload = json_decode($cipherText, true);
        if (!is_array($payload)) {
            return null;
        }

        $cipher = (string) ($payload['cipher'] ?? '');
        if ($cipher === 'aes-256-gcm') {
            return $this->aesFallback->decrypt($cipherText);
        }

        if ($cipher !== 'sodium-secretbox' || !$this->sodiumAvailable()) {
            return null;
        }

        $nonce = base64_decode((string) ($payload['nonce'] ?? ''), true);
        $encrypted = base64_decode((string) ($payload['ciphertext'] ?? ''), true);
        if ($nonce === false || $encrypted === false) {
            return null;
        }

        $plain = sodium_crypto_secretbox_open($encrypted, $nonce, $this->binaryKey());

        return $plain === false ? null : $plain;
    }

    private function sodiumAvailable(): bool
    {
        return function_exists('sodium_crypto_secretbox')
            && function_exists('sodium_crypto_secretbox_open')
            && defined('SODIUM_CRYPTO_SECRETBOX_KEYBYTES')
            && defined('SODIUM_CRYPTO_SECRETBOX_NONCEBYTES');
    }

    private function normalizeKey(?string $key): ?string
    {
        if ($key === null) {
            return null;
        }

        $key = trim($key);
        if ($key === '' || str_starts_with(strtolower($key), 'cambiar_')) {
            return null;
        }

        return $key;
    }

    private function binaryKey(): string
    {
        return hash('sha256', (string) $this->key, true);
    }
}

<?php
declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Contracts\EncryptionServiceInterface;

final class AesGcmEncryptionService implements EncryptionServiceInterface
{
    private ?string $key;

    public function __construct(?string $key)
    {
        $this->key = $this->normalizeKey($key);
    }

    public function isConfigured(): bool
    {
        return $this->key !== null && function_exists('openssl_encrypt') && in_array('aes-256-gcm', openssl_get_cipher_methods(), true);
    }

    public function encrypt(string $plainText): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $iv = random_bytes(12);
        $tag = '';
        $cipherText = openssl_encrypt(
            $plainText,
            'aes-256-gcm',
            $this->binaryKey(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($cipherText === false || $tag === '') {
            return null;
        }

        return json_encode([
            'version' => 1,
            'cipher' => 'aes-256-gcm',
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
            'ciphertext' => base64_encode($cipherText),
        ], JSON_UNESCAPED_SLASHES) ?: null;
    }

    public function decrypt(string $cipherText): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $payload = json_decode($cipherText, true);
        if (!is_array($payload) || ($payload['cipher'] ?? '') !== 'aes-256-gcm') {
            return null;
        }

        $iv = base64_decode((string) ($payload['iv'] ?? ''), true);
        $tag = base64_decode((string) ($payload['tag'] ?? ''), true);
        $encrypted = base64_decode((string) ($payload['ciphertext'] ?? ''), true);
        if ($iv === false || $tag === false || $encrypted === false) {
            return null;
        }

        $plain = openssl_decrypt($encrypted, 'aes-256-gcm', $this->binaryKey(), OPENSSL_RAW_DATA, $iv, $tag);

        return $plain === false ? null : $plain;
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


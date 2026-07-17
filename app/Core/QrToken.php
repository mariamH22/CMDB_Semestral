<?php
declare(strict_types=1);

namespace App\Core;

final class QrToken
{
    private const HASH_CONTEXT = 'cmdb-qr-token-v1:';
    private const PAYLOAD_CONTEXT = 'cmdb-qr-payload-v1:';

    public static function generate(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function isValid(string $token): bool
    {
        return (bool) preg_match('/\A[a-f0-9]{64}\z/i', $token);
    }

    public static function normalize(string $token): string
    {
        return strtolower(trim($token));
    }

    public static function hash(string $token): string
    {
        return hash('sha256', self::HASH_CONTEXT . self::normalize($token));
    }

    public static function payloadHash(int $inventoryId, string $token): string
    {
        return hash('sha256', self::PAYLOAD_CONTEXT . $inventoryId . ':' . self::hash($token));
    }

    public static function legacyPayloadHash(int $inventoryId, string $token): string
    {
        return hash('sha256', 'inventory:' . $inventoryId . ':' . self::normalize($token));
    }

    public static function verifyPayloadHash(int $inventoryId, string $token, string $payloadHash): bool
    {
        return hash_equals($payloadHash, self::payloadHash($inventoryId, $token))
            || hash_equals($payloadHash, self::legacyPayloadHash($inventoryId, $token));
    }
}

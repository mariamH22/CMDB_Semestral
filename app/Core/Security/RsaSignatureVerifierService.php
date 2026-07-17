<?php
declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Contracts\SignatureVerifierInterface;

final class RsaSignatureVerifierService implements SignatureVerifierInterface
{
    public function verify(string $payload, string $signature, string $publicKeyPem): ?bool
    {
        if (!function_exists('openssl_verify') || trim($publicKeyPem) === '') {
            return null;
        }

        $decodedSignature = base64_decode($signature, true);
        if ($decodedSignature === false) {
            return null;
        }

        $result = openssl_verify($payload, $decodedSignature, $publicKeyPem, OPENSSL_ALGO_SHA256);
        if ($result === 1) {
            return true;
        }

        if ($result === 0) {
            return false;
        }

        return null;
    }
}


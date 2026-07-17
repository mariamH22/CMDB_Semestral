<?php
declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Contracts\DigitalSignatureInterface;

final class RsaDigitalSignatureService implements DigitalSignatureInterface
{
    public function sign(string $payload, string $privateKeyPem): ?string
    {
        if (!function_exists('openssl_sign') || trim($privateKeyPem) === '') {
            return null;
        }

        $signature = '';
        $signed = openssl_sign($payload, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);

        return $signed ? base64_encode($signature) : null;
    }
}


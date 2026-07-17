<?php
declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Contracts\SignatureVerifierInterface;

final class SignatureVerificationService
{
    public const VALID = 'VALIDA';
    public const INVALID = 'INVALIDA';
    public const REVOKED_KEY = 'LLAVE_REVOCADA';
    public const NOT_VERIFIABLE = 'NO_VERIFICABLE';
    public const ERROR = 'ERROR';

    public function __construct(private SignatureVerifierInterface $verifier)
    {
    }

    public function verify(string $payloadHash, string $signature, string $publicKey, string $keyStatus): string
    {
        try {
            $verified = $this->verifier->verify($payloadHash, $signature, $publicKey);
            if ($verified === null) {
                return self::NOT_VERIFIABLE;
            }

            if (!$verified) {
                return self::INVALID;
            }

            return $keyStatus === 'REVOCADA' ? self::REVOKED_KEY : self::VALID;
        } catch (\Throwable) {
            return self::ERROR;
        }
    }
}


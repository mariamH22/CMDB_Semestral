<?php
declare(strict_types=1);

namespace App\Core\Contracts;

interface SignatureVerifierInterface
{
    public function verify(string $payload, string $signature, string $publicKeyPem): ?bool;
}


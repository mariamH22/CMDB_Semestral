<?php
declare(strict_types=1);

namespace App\Core\Contracts;

interface DigitalSignatureInterface
{
    public function sign(string $payload, string $privateKeyPem): ?string;
}


<?php
declare(strict_types=1);

namespace App\Core\Contracts;

interface EncryptionServiceInterface
{
    public function isConfigured(): bool;

    public function encrypt(string $plainText): ?string;

    public function decrypt(string $cipherText): ?string;
}


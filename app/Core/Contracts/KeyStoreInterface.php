<?php
declare(strict_types=1);

namespace App\Core\Contracts;

interface KeyStoreInterface
{
    public function isConfigured(): bool;

    public function storeEncryptedPrivateKey(int $userId, string $fingerprint, string $encryptedPrivateKey): ?string;

    public function readEncryptedPrivateKey(string $reference): ?string;
}


<?php
declare(strict_types=1);

namespace App\Core\Contracts;

interface KeyManagementInterface
{
    public function isConfigured(): bool;

    public function generateForUser(int $userId, string $name = 'Llave RSA de usuario', int $bits = 3072): ?array;

    public function loadPrivateKey(string $reference): ?string;
}


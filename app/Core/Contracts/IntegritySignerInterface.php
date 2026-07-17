<?php
declare(strict_types=1);

namespace App\Core\Contracts;

interface IntegritySignerInterface
{
    public function isConfigured(): bool;

    public function sign(array $payload): string;

    public function verify(array $payload, ?string $signature): bool;
}

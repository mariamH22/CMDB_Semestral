<?php
declare(strict_types=1);

namespace App\Core\Contracts;

interface CanonicalPayloadInterface
{
    public function serialize(array $payload): string;
}

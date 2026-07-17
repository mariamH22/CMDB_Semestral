<?php
declare(strict_types=1);

namespace App\Core\Contracts;

interface ErrorRendererInterface
{
    public function render(\Throwable $exception): never;
}

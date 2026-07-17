<?php
declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Contracts\PasswordHasherInterface;

final class PasswordHasher implements PasswordHasherInterface
{
    public function needsRehash(string $hash): bool
    {
        $algorithm = $this->preferredAlgorithm();

        return password_needs_rehash($hash, $algorithm, $this->options($algorithm));
    }

    public function hash(string $plain): string
    {
        $algo = $this->preferredAlgorithm();
        $options = $this->options($algo);
        $hash = password_hash($plain, $algo, $options);
        if ($hash === false) {
            return password_hash($plain, PASSWORD_BCRYPT);
        }

        return $hash;
    }

    public function verify(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    private function preferredAlgorithm(): string|int
    {
        $algos = password_algos();
        if (
            defined('PASSWORD_ARGON2ID')
            && in_array(PASSWORD_ARGON2ID, $algos, true)
        ) {
            return PASSWORD_ARGON2ID;
        }

        return PASSWORD_BCRYPT;
    }

    private function options(string|int $algorithm): array
    {
        return match ($algorithm) {
            PASSWORD_ARGON2ID => [
                'memory_cost' => 1 << 17,
                'time_cost' => 4,
                'threads' => 2,
            ],
            default => ['cost' => 12],
        };
    }
}

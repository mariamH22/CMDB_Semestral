<?php
declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Contracts\KeyStoreInterface;

final class FileKeyStore implements KeyStoreInterface
{
    private ?string $basePath;
    private string $projectRoot;

    public function __construct(?string $basePath)
    {
        $this->projectRoot = dirname(__DIR__, 3);
        $this->basePath = $this->normalizeBasePath($basePath);
    }

    public function isConfigured(): bool
    {
        return $this->basePath !== null && !$this->isProjectPath($this->basePath);
    }

    public function storeEncryptedPrivateKey(int $userId, string $fingerprint, string $encryptedPrivateKey): ?string
    {
        if (!$this->isConfigured() || !preg_match('/\A[a-f0-9]{64}\z/i', $fingerprint)) {
            return null;
        }

        $directory = (string) $this->basePath;
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            return null;
        }

        if ($this->isProjectPath($directory) || !is_writable($directory)) {
            return null;
        }

        $reference = 'user_' . $userId . '_' . strtolower($fingerprint) . '.key';
        $path = $directory . DIRECTORY_SEPARATOR . $reference;

        if (file_put_contents($path, $encryptedPrivateKey, LOCK_EX) === false) {
            return null;
        }

        @chmod($path, 0600);

        return $reference;
    }

    public function readEncryptedPrivateKey(string $reference): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $reference = basename($reference);
        if ($reference === '' || str_contains($reference, '..')) {
            return null;
        }

        $path = (string) $this->basePath . DIRECTORY_SEPARATOR . $reference;
        if (!is_file($path) || $this->isProjectPath($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        return $contents === false ? null : $contents;
    }

    private function normalizeBasePath(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = trim($path);
        if ($path === '') {
            return null;
        }

        if (!$this->isAbsolutePath($path)) {
            $path = $this->projectRoot . DIRECTORY_SEPARATOR . $path;
        }

        return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR) || (bool) preg_match('/\A[A-Za-z]:[\\\\\/]/', $path);
    }

    private function isProjectPath(string $path): bool
    {
        $projectPath = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->projectRoot), DIRECTORY_SEPARATOR);
        $candidate = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);

        return $candidate === $projectPath || str_starts_with($candidate . DIRECTORY_SEPARATOR, $projectPath . DIRECTORY_SEPARATOR);
    }
}

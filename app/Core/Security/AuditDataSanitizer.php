<?php
declare(strict_types=1);

namespace App\Core\Security;

final class AuditDataSanitizer
{
    private const REDACTED = '[REDACTED]';
    private const MASKED = '[MASKED]';

    private const SECRET_PATTERNS = [
        'password',
        'password_hash',
        'contrasena',
        'contraseña',
        'token',
        'csrf',
        'private_key',
        'llave_privada',
        'key_encryption',
        'integrity_key',
        'secret',
        'secreto',
        'firma_frase',
        'frase_firma',
        'password_actual',
        'password_nueva',
        'password_confirmacion',
        'password_confirmation',
    ];

    private const MASK_PATTERNS = [
        'clave_licencia',
        'license_key',
        'serial_licencia',
    ];

    public function sanitize(array $data): array
    {
        return $this->sanitizeArray($data);
    }

    private function sanitizeValue(string|int $key, mixed $value): mixed
    {
        $normalizedKey = strtolower((string) $key);

        if ($this->matches($normalizedKey, self::SECRET_PATTERNS)) {
            return self::REDACTED;
        }

        if ($this->matches($normalizedKey, self::MASK_PATTERNS)) {
            return $this->mask((string) $value);
        }

        if (is_array($value)) {
            return $this->sanitizeArray($value);
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
            return $value;
        }

        return (string) $value;
    }

    private function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $sanitized[$key] = $this->sanitizeValue($key, $value);
        }

        return $sanitized;
    }

    private function matches(string $key, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (str_contains($key, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function mask(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $last = substr($value, -4);

        return self::MASKED . ($last !== '' ? '...' . $last : '');
    }
}

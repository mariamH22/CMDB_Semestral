<?php
declare(strict_types=1);

namespace App\Core;

final class Auth
{
    private const KEY = 'cmdb_user';

    public static function login(array $user): void
    {
        // Regenerar el ID de sesion reduce el riesgo de session fixation.
        session_regenerate_id(true);

        $_SESSION[self::KEY] = [
            'id' => (int) $user['id'],
            'nombre_usuario' => $user['nombre_usuario'],
            'email' => $user['email'],
            'rol' => $user['rol'],
            'colaborador_id' => $user['colaborador_id'] ? (int) $user['colaborador_id'] : null,
        ];
    }

    public static function logout(): void
    {
        unset($_SESSION[self::KEY]);
        // Tambien se regenera al cerrar para invalidar referencias antiguas.
        session_regenerate_id(true);
    }

    public static function check(): bool
    {
        return isset($_SESSION[self::KEY]);
    }

    public static function user(): ?array
    {
        return $_SESSION[self::KEY] ?? null;
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function role(): ?string
    {
        return self::user()['rol'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'ADMIN';
    }

    public static function isOperator(): bool
    {
        return self::role() === 'OPERADOR';
    }

    public static function isInternal(): bool
    {
        return in_array(self::role(), ['ADMIN', 'OPERADOR'], true);
    }

    public static function isCollaborator(): bool
    {
        return self::role() === 'COLABORADOR';
    }

    public static function can(string $permission): bool
    {
        return Authorization::can(self::role(), $permission);
    }
}

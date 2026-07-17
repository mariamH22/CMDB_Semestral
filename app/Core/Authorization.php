<?php
declare(strict_types=1);

namespace App\Core;

final class Authorization
{
    // Mapa unico de permisos por rol. Evita repetir reglas en cada controlador.
    private const PERMISSIONS = [
        'system.admin' => ['ADMIN'],
        'dashboard.view' => ['ADMIN', 'OPERADOR'],
        'inventory.view' => ['ADMIN', 'OPERADOR'],
        'inventory.manage' => ['ADMIN', 'OPERADOR'],
        'inventory.reveal_license' => ['ADMIN'],
        'collaborators.view' => ['ADMIN', 'OPERADOR'],
        'collaborators.manage' => ['ADMIN'],
        'categories.view' => ['ADMIN', 'OPERADOR'],
        'categories.manage' => ['ADMIN'],
        'assignments.view' => ['ADMIN', 'OPERADOR'],
        'assignments.manage' => ['ADMIN'],
        'needs.view' => ['ADMIN', 'OPERADOR'],
        'needs.manage' => ['ADMIN'],
        'budgets.view' => ['ADMIN', 'OPERADOR'],
        'budgets.manage' => ['ADMIN'],
        'reports.view' => ['ADMIN', 'OPERADOR'],
        'reports.export' => ['ADMIN', 'OPERADOR'],
        'users.manage' => ['ADMIN'],
        'audit.view' => ['ADMIN'],
        'news.manage' => ['ADMIN'],
        'portal.view' => ['COLABORADOR'],
    ];

    public static function can(?string $role, string $permission): bool
    {
        if ($role === null) {
            return false;
        }

        // Un permiso no registrado se trata como denegado por seguridad.
        return in_array($role, self::PERMISSIONS[$permission] ?? [], true);
    }
}

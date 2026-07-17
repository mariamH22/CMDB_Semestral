<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\InventoryStatus;
use App\Core\NeedStatus;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim((string) Config::get('app.base_path', ''), '/');
    $path = ltrim($path, '/');

    return $path === '' ? ($base ?: '/') : ($base . '/' . $path);
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(Csrf::token()) . '">';
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return $value;
}

function old(string $key, string $default = ''): string
{
    return e(old_value($key, $default));
}

function old_value(string $key, mixed $default = ''): mixed
{
    if (array_key_exists($key, $_POST)) {
        return $_POST[$key];
    }

    if (isset($_SESSION['_old_input']) && is_array($_SESSION['_old_input']) && array_key_exists($key, $_SESSION['_old_input'])) {
        return $_SESSION['_old_input'][$key];
    }

    return $default;
}

function has_old_input(): bool
{
    return isset($_SESSION['_old_input']) && is_array($_SESSION['_old_input']);
}

function old_checked(string $key, bool|int $default = false): string
{
    if (has_old_input()) {
        return checked(array_key_exists($key, $_SESSION['_old_input']));
    }

    return checked($default);
}

function selected(mixed $current, mixed $expected): string
{
    return (string) $current === (string) $expected ? 'selected' : '';
}

function checked(bool|int $value): string
{
    return (bool) $value ? 'checked' : '';
}

function status_badge(string $status): string
{
    $normalized = strtoupper($status);
    $class = match ($normalized) {
        'ACTIVO', 'ACTIVA', 'DISPONIBLE', 'ATENDIDA', 'PUBLICADA', 'APROBADA' => 'success',
        'ASIGNADO', 'EN_REVISION', 'EN_TRAMITE', 'MANTENIMIENTO', 'DEVOLUCION_REGISTRADA', 'REVISION_TECNICA', 'EN_REPARACION' => 'info',
        'DANADO', 'DESCARTE', 'BLOQUEADO', 'CANCELADA', 'DONADO', 'VENCIDA', 'RECHAZADA' => 'danger',
        'PENDIENTE', 'EN_ESPERA', 'INACTIVA' => 'warning',
        default => 'neutral'
    };

    $label = in_array($normalized, NeedStatus::values(), true)
        ? NeedStatus::label($status)
        : InventoryStatus::label($status);

    return '<span class="badge badge-' . $class . '">' . e($label) . '</span>';
}

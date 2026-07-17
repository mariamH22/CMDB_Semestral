<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Core\Config;
use App\Core\IntegritySigner;
use App\Core\Validator;

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$payload = ['serie' => 'TEST-001', 'tipo' => 'HARDWARE', 'estado' => 'DISPONIBLE', 'fecha' => '2026-07-13'];

Config::load([
    'security' => [
        'integrity_key' => bin2hex(random_bytes(32)),
        'integrity_legacy_key' => null,
    ],
]);

$signature = IntegritySigner::sign($payload);
$assert($signature !== '', 'HMAC con clave valida no debe estar vacio.');
$assert((bool) preg_match('/\A[a-f0-9]{64}\z/', $signature), 'HMAC debe ser SHA-256 hexadecimal.');
$assert(IntegritySigner::verify($payload, $signature), 'HMAC valida debe verificar.');

$tampered = $payload;
$tampered['estado'] = 'DANADO';
$assert(!IntegritySigner::verify($tampered, $signature), 'HMAC debe detectar alteracion del payload.');

Config::load([
    'security' => [
        'integrity_key' => null,
        'integrity_legacy_key' => null,
    ],
]);

$missingSignature = IntegritySigner::sign($payload);
$assert($missingSignature === '', 'Sin clave HMAC no se debe firmar con fallback.');
$assert(!IntegritySigner::verify($payload, $signature), 'Sin clave HMAC no se debe verificar con fallback.');

Config::load([
    'security' => [
        'integrity_key' => '',
        'integrity_legacy_key' => 'cambiar_esta_clave',
    ],
]);

$unsafeFallbackSignature = IntegritySigner::sign($payload);
$assert($unsafeFallbackSignature === '', 'Claves vacias o placeholder deben rechazarse.');

Config::load([
    'security' => [
        'password_min_length' => 8,
        'password_max_length' => 64,
    ],
]);

$longValidPassword = 'PasswordSeguro123!';
$assert(Validator::password($longValidPassword) === $longValidPassword, 'Debe aceptar contrasenas validas de mas de 12 caracteres.');

$passwordWithoutSymbolFailed = false;
try {
    Validator::password('PasswordSeguro123');
} catch (\Throwable) {
    $passwordWithoutSymbolFailed = true;
}
$assert($passwordWithoutSymbolFailed, 'Debe rechazar contrasenas sin simbolo.');

$validatorSource = (string) file_get_contents(dirname(__DIR__) . '/app/Core/Validator.php');
$sanitizerSource = (string) file_get_contents(dirname(__DIR__) . '/app/Core/Sanitizer.php');
$reportSource = (string) file_get_contents(dirname(__DIR__) . '/app/Core/ReportService.php');
$assert(str_contains($validatorSource, "function_exists('mb_strlen')"), 'Validator debe proteger mb_strlen con fallback.');
$assert(str_contains($sanitizerSource, "function_exists('mb_substr')"), 'Sanitizer debe proteger mb_substr con fallback.');
$assert(str_contains($reportSource, "function_exists('mb_strtolower')"), 'ReportService debe proteger mb_strtolower con fallback.');

echo "OK Phase1SecurityTest\n";

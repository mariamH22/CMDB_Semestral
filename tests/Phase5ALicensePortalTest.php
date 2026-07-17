<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Core\LicensePolicy;
use App\Core\PortalAccessPolicy;
use App\Core\Security\AuthenticatedEncryptionService;

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$throws = static function (callable $callback): bool {
    try {
        $callback();
        return false;
    } catch (\RuntimeException) {
        return true;
    }
};

$encryption = new AuthenticatedEncryptionService('clave-maestra-de-prueba-no-real-2026');
$assert($encryption->isConfigured(), 'El cifrado debe estar configurado con una clave externa de prueba.');
$cipherText = $encryption->encrypt('LIC-PRUEBA-12345');
$assert(is_string($cipherText) && $cipherText !== 'LIC-PRUEBA-12345', 'La clave no debe quedar en texto plano.');
$assert($encryption->decrypt($cipherText) === 'LIC-PRUEBA-12345', 'Debe descifrar la clave cifrada.');

$missing = new AuthenticatedEncryptionService(null);
$assert(!$missing->isConfigured(), 'Sin clave maestra no debe configurarse el cifrado.');
$assert($missing->encrypt('LIC-SIN-CLAVE') === null, 'Sin clave maestra no debe cifrar.');

$assert(LicensePolicy::available(10, 4) === 6, 'Cantidad disponible incorrecta.');
LicensePolicy::assertQuantity(1, 10, 9);
$assert($throws(fn () => LicensePolicy::assertQuantity(0, 10, 0)), 'Debe bloquear cantidad cero.');
$assert($throws(fn () => LicensePolicy::assertQuantity(-1, 10, 0)), 'Debe bloquear cantidad negativa.');
$assert($throws(fn () => LicensePolicy::assertQuantity(2, 10, 9)), 'Debe bloquear sobreasignación.');

LicensePolicy::assertAssignable('2027-01-01', LicensePolicy::ACTIVA, false, '2026-07-13');
$assert($throws(fn () => LicensePolicy::assertAssignable('2026-01-01', LicensePolicy::ACTIVA, false, '2026-07-13')), 'Debe bloquear licencia vencida sin autorización.');
$assert($throws(fn () => LicensePolicy::assertAssignable(null, LicensePolicy::INACTIVA, false, '2026-07-13')), 'Debe bloquear licencia inactiva sin autorización.');
LicensePolicy::assertAssignable('2026-01-01', LicensePolicy::ACTIVA, true, '2026-07-13');

PortalAccessPolicy::assertAssignmentBelongsToCollaborator(7, 7);
$assert($throws(fn () => PortalAccessPolicy::assertAssignmentBelongsToCollaborator(7, 8)), 'Debe bloquear IDOR del portal.');
PortalAccessPolicy::assertReturnCanBeRequested('ACTIVA', 'ASIGNADO');
$assert($throws(fn () => PortalAccessPolicy::assertReturnCanBeRequested('DEVUELTA', 'DEVOLUCION_REGISTRADA')), 'Debe bloquear devolución duplicada.');

echo "OK Phase5ALicensePortalTest\n";

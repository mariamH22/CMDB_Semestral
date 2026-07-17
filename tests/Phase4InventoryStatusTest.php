<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Core\InventoryStatus;

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

InventoryStatus::assertCanCreate(InventoryStatus::DISPONIBLE);
InventoryStatus::assertCanCreate(InventoryStatus::DANADO);
InventoryStatus::assertCanCreate(InventoryStatus::EN_REPARACION);
$assert($throws(fn () => InventoryStatus::assertCanCreate(InventoryStatus::DESCARTE)), 'No debe crear activos como DESCARTE.');
$assert($throws(fn () => InventoryStatus::assertCanCreate(InventoryStatus::DONADO)), 'No debe crear activos como DONADO.');

InventoryStatus::assertTransition(InventoryStatus::DISPONIBLE, InventoryStatus::ASIGNADO, 'asignacion');
InventoryStatus::assertTransition(InventoryStatus::ASIGNADO, InventoryStatus::DEVOLUCION_REGISTRADA, 'devolucion');
InventoryStatus::assertTransition(InventoryStatus::DEVOLUCION_REGISTRADA, InventoryStatus::REVISION_TECNICA, 'revision');
InventoryStatus::assertTransition(InventoryStatus::REVISION_TECNICA, InventoryStatus::DISPONIBLE, 'revision');
InventoryStatus::assertTransition(InventoryStatus::REVISION_TECNICA, InventoryStatus::EN_REPARACION, 'revision');
InventoryStatus::assertTransition(InventoryStatus::REVISION_TECNICA, InventoryStatus::DESCARTE, 'revision');
InventoryStatus::assertTransition(InventoryStatus::REVISION_TECNICA, InventoryStatus::DONADO, 'revision');

$assert($throws(fn () => InventoryStatus::assertTransition(InventoryStatus::ASIGNADO, InventoryStatus::DISPONIBLE, 'manual')), 'No debe permitir ASIGNADO -> DISPONIBLE directo.');
$assert($throws(fn () => InventoryStatus::assertTransition(InventoryStatus::DISPONIBLE, InventoryStatus::DESCARTE, 'manual')), 'No debe permitir DISPONIBLE -> DESCARTE directo.');
$assert($throws(fn () => InventoryStatus::assertTransition(InventoryStatus::DISPONIBLE, InventoryStatus::DONADO, 'manual')), 'No debe permitir DISPONIBLE -> DONADO directo.');
$assert($throws(fn () => InventoryStatus::assertTransition(InventoryStatus::DANADO, InventoryStatus::DONADO, 'manual')), 'Donación debe pasar por revisión técnica.');
$assert($throws(fn () => InventoryStatus::assertTransition(InventoryStatus::DANADO, InventoryStatus::DESCARTE, 'manual')), 'Descarte debe pasar por revisión técnica.');

$manualFromAvailable = InventoryStatus::manualTransitions(InventoryStatus::DISPONIBLE);
$assert(!in_array(InventoryStatus::DESCARTE, $manualFromAvailable, true), 'Manual desde disponible no debe ofrecer DESCARTE.');
$assert(!in_array(InventoryStatus::DONADO, $manualFromAvailable, true), 'Manual desde disponible no debe ofrecer DONADO.');
$assert(in_array(InventoryStatus::DISPONIBLE, InventoryStatus::reviewResults(), true), 'Revisión puede devolver a DISPONIBLE.');
$assert(in_array(InventoryStatus::DONADO, InventoryStatus::reviewResults(), true), 'Revisión puede resolver DONADO.');

echo "OK Phase4InventoryStatusTest\n";

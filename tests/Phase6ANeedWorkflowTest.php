<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

use App\Core\NeedAccessPolicy;
use App\Core\NeedHistoryEntry;
use App\Core\NeedStatus;

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

NeedStatus::assertTransition(NeedStatus::EN_ESPERA, NeedStatus::EN_TRAMITE);
NeedStatus::assertTransition(NeedStatus::EN_TRAMITE, NeedStatus::APROBADA);
NeedStatus::assertTransition(NeedStatus::EN_TRAMITE, NeedStatus::RECHAZADA);
NeedStatus::assertTransition('PENDIENTE', NeedStatus::EN_TRAMITE);

$assert($throws(fn () => NeedStatus::assertTransition(NeedStatus::APROBADA, NeedStatus::EN_TRAMITE)), 'No debe reabrir solicitud aprobada.');
$assert($throws(fn () => NeedStatus::assertTransition(NeedStatus::RECHAZADA, NeedStatus::APROBADA)), 'No debe aprobar solicitud rechazada.');
$assert($throws(fn () => NeedStatus::assertTransition(NeedStatus::EN_ESPERA, 'CANCELADA_FINAL')), 'Debe bloquear estado inválido.');

$assert(NeedStatus::normalize('PENDIENTE') === NeedStatus::EN_ESPERA, 'Debe normalizar PENDIENTE.');
$assert(NeedStatus::storageStatus(NeedStatus::APROBADA, false) === 'ATENDIDA', 'Debe guardar legacy si no hay migración formal.');
$assert(NeedStatus::storageStatus(NeedStatus::APROBADA, true) === NeedStatus::APROBADA, 'Debe guardar formal si hay migración.');
$assert(NeedStatus::requiresProcessor(NeedStatus::APROBADA), 'Aprobación requiere procesador.');
$assert(NeedStatus::requiresSignature(NeedStatus::RECHAZADA), 'Rechazo requiere firma.');
$assert(!NeedStatus::requiresSignature(NeedStatus::EN_TRAMITE), 'Trámite no requiere firma final.');

NeedAccessPolicy::assertCollaboratorOwnsNeed(15, 15);
$assert($throws(fn () => NeedAccessPolicy::assertCollaboratorOwnsNeed(15, 16)), 'Debe bloquear IDOR de solicitud.');
$assert(NeedAccessPolicy::canViewPrivateDetail('ADMIN', 15, null), 'Admin debe ver solicitudes.');
$assert(NeedAccessPolicy::canViewPrivateDetail('COLABORADOR', 15, 15), 'Colaborador debe ver sus solicitudes.');
$assert(!NeedAccessPolicy::canViewPrivateDetail('COLABORADOR', 15, 16), 'Colaborador no debe ver solicitudes ajenas.');

$history = NeedHistoryEntry::build(20, 3, 'EN_TRAMITE', 'APROBADA', 'Aprobada por disponibilidad presupuestaria.', 55, 99);
$assert($history['necesidad_id'] === 20, 'Historial debe incluir solicitud.');
$assert($history['usuario_id'] === 3, 'Historial debe incluir usuario.');
$assert($history['estado_anterior'] === NeedStatus::EN_TRAMITE, 'Historial debe incluir estado anterior formal.');
$assert($history['estado_nuevo'] === NeedStatus::APROBADA, 'Historial debe incluir estado nuevo formal.');
$assert($history['firma_id'] === 55, 'Historial debe incluir firma.');
$assert($history['audit_id'] === 99, 'Historial debe incluir auditoría.');
$assert($history['respuesta_administrativa'] === $history['observacion'], 'Historial debe incluir respuesta administrativa.');

echo "OK Phase6ANeedWorkflowTest\n";

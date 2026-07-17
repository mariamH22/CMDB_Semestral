<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\ServiceContainer;

final class AuditLog
{
    public function __construct(private Database $db)
    {
    }

    public function create(?int $userId, string $module, string $action, string $description, string $level = 'INFO', array $context = []): int
    {
        if ($this->supportsTrail()) {
            return $this->createTrailRecord($userId, $module, $action, $description, $level, $context);
        }

        return $this->db->insert(
            "INSERT INTO bitacora (usuario_id, modulo, accion, descripcion, ip, nivel)
             VALUES (:usuario_id, :modulo, :accion, :descripcion, :ip, :nivel)",
            [
                'usuario_id' => $userId,
                'modulo' => $module,
                'accion' => $action,
                'descripcion' => $description,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
                'nivel' => $level,
            ]
        );
    }

    public function attachSignature(int $auditId, int $signatureId): void
    {
        if (!$this->supportsTrail() || !$this->db->tableExists('firmas_digitales') || $auditId < 1 || $signatureId < 1) {
            return;
        }

        $fingerprintSelect = $this->db->columnExists('firmas_digitales', 'fingerprint')
            ? 'fingerprint'
            : 'NULL AS fingerprint';
        $signature = $this->db->fetch(
            "SELECT id, {$fingerprintSelect} FROM firmas_digitales WHERE id = :id",
            ['id' => $signatureId]
        );
        $row = $this->db->fetch(
            "SELECT * FROM bitacora WHERE id = :id",
            ['id' => $auditId]
        );

        if (!$signature || !$row) {
            return;
        }

        $row['firma_id'] = (int) $signature['id'];
        $row['fingerprint'] = $signature['fingerprint'] ?? null;
        $payload = ServiceContainer::auditTrail()->payload(ServiceContainer::auditTrail()->eventFromRow($row));
        $recordHash = ServiceContainer::auditTrail()->hash($row['previous_hash'] ?: null, $payload);

        $this->db->execute(
            "UPDATE bitacora
             SET firma_id = :firma_id,
                 fingerprint = :fingerprint,
                 record_hash = :record_hash
             WHERE id = :id",
            [
                'id' => $auditId,
                'firma_id' => (int) $signature['id'],
                'fingerprint' => $signature['fingerprint'] ?? null,
                'record_hash' => $recordHash,
            ]
        );
    }

    public function loginAttempt(?int $userId, string $identifier, bool $success, string $reason = ''): void
    {
        $this->db->insert(
            "INSERT INTO intentos_login (usuario_id, identificador, ip, exitoso, motivo)
             VALUES (:usuario_id, :identificador, :ip, :exitoso, :motivo)",
            [
                'usuario_id' => $userId,
                'identificador' => $identifier,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
                'exitoso' => $success ? 1 : 0,
                'motivo' => $reason,
            ]
        );
    }

    public function recentFailedLoginCount(string $identifier, int $minutes, ?string $ip = null): int
    {
        $minutes = max(1, $minutes);
        $ip ??= $_SERVER['REMOTE_ADDR'] ?? 'CLI';

        // Se usa la tabla existente de intentos para no agregar dependencias ni migraciones.
        $row = $this->db->fetch(
            "SELECT COUNT(*) AS total
             FROM intentos_login
             WHERE exitoso = 0
               AND identificador = :identificador
               AND ip = :ip
               AND created_at >= DATE_SUB(NOW(), INTERVAL {$minutes} MINUTE)",
            [
                'identificador' => $identifier,
                'ip' => $ip,
            ]
        );

        return (int) ($row['total'] ?? 0);
    }

    public function portalAccess(int $userId): void
    {
        $this->db->insert(
            "INSERT INTO accesos_portal_colaborador (usuario_id, ip) VALUES (:usuario_id, :ip)",
            ['usuario_id' => $userId, 'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI']
        );
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            "SELECT b.*, u.nombre_usuario
             FROM bitacora b
             LEFT JOIN usuarios u ON u.id = b.usuario_id
             ORDER BY b.created_at DESC
             LIMIT 300"
        );
    }

    public function portalHistory(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM accesos_portal_colaborador WHERE usuario_id = :id ORDER BY accessed_at DESC LIMIT 30",
            ['id' => $userId]
        );
    }

    public function supportsTrail(): bool
    {
        if (!$this->db->tableExists('bitacora')) {
            return false;
        }

        foreach ($this->trailColumns() as $column) {
            if (!$this->db->columnExists('bitacora', $column)) {
                return false;
            }
        }

        return true;
    }

    private function createTrailRecord(?int $userId, string $module, string $action, string $description, string $level, array $context): int
    {
        return $this->db->transaction(function (Database $db) use ($userId, $module, $action, $description, $level, $context): int {
            $last = $db->fetch(
                "SELECT record_hash
                 FROM bitacora
                 WHERE record_hash IS NOT NULL
                 ORDER BY id DESC
                 LIMIT 1
                 FOR UPDATE"
            );
            $previousHash = $last['record_hash'] ?? null;
            $createdAt = date('Y-m-d H:i:s');
            $before = ServiceContainer::auditTrail()->sanitize((array) ($context['before'] ?? []));
            $after = ServiceContainer::auditTrail()->sanitize((array) ($context['after'] ?? []));
            $event = [
                'payload_version' => 1,
                'usuario_id' => $userId,
                'modulo' => $module,
                'accion' => $action,
                'entidad' => $context['entity'] ?? null,
                'entidad_id' => $context['entity_id'] ?? null,
                'created_at' => $createdAt,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI',
                'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'CLI'), 0, 255),
                'resultado' => $context['result'] ?? 'OK',
                'motivo' => $context['reason'] ?? null,
                'descripcion' => $description,
                'datos_anteriores' => $before,
                'datos_posteriores' => $after,
                'firma_id' => $context['signature_id'] ?? null,
                'fingerprint' => $context['fingerprint'] ?? null,
                'correlation_id' => $context['correlation_id'] ?? bin2hex(random_bytes(16)),
            ];
            $payload = ServiceContainer::auditTrail()->payload($event);
            $recordHash = ServiceContainer::auditTrail()->hash($previousHash ? (string) $previousHash : null, $payload);

            return $db->insert(
                "INSERT INTO bitacora
                 (usuario_id, modulo, accion, descripcion, ip, user_agent, nivel, entidad, entidad_id,
                  resultado, motivo, datos_anteriores_json, datos_posteriores_json, correlation_id,
                  previous_hash, record_hash, firma_id, fingerprint, payload_version, created_at)
                 VALUES
                 (:usuario_id, :modulo, :accion, :descripcion, :ip, :user_agent, :nivel, :entidad, :entidad_id,
                  :resultado, :motivo, :datos_anteriores_json, :datos_posteriores_json, :correlation_id,
                  :previous_hash, :record_hash, :firma_id, :fingerprint, :payload_version, :created_at)",
                [
                    'usuario_id' => $userId,
                    'modulo' => $module,
                    'accion' => $action,
                    'descripcion' => $description,
                    'ip' => $event['ip'],
                    'user_agent' => $event['user_agent'],
                    'nivel' => $level,
                    'entidad' => $event['entidad'],
                    'entidad_id' => $event['entidad_id'],
                    'resultado' => $event['resultado'],
                    'motivo' => $event['motivo'],
                    'datos_anteriores_json' => $this->encodeJson($before),
                    'datos_posteriores_json' => $this->encodeJson($after),
                    'correlation_id' => $event['correlation_id'],
                    'previous_hash' => $previousHash,
                    'record_hash' => $recordHash,
                    'firma_id' => $event['firma_id'],
                    'fingerprint' => $event['fingerprint'],
                    'payload_version' => 1,
                    'created_at' => $createdAt,
                ]
            );
        });
    }

    private function trailColumns(): array
    {
        return [
            'user_agent',
            'entidad',
            'entidad_id',
            'resultado',
            'motivo',
            'datos_anteriores_json',
            'datos_posteriores_json',
            'correlation_id',
            'previous_hash',
            'record_hash',
            'firma_id',
            'fingerprint',
            'payload_version',
        ];
    }

    private function encodeJson(array $data): string
    {
        $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? $encoded : '{}';
    }
}

<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;
use App\Core\InventoryStatus;
use App\Models\Assignment;
use App\Models\ReturnReview;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
require_once dirname(__DIR__, 2) . '/app/Core/helpers.php';

Config::load(require dirname(__DIR__, 2) . '/app/Config/config.php');

$db = Database::instance();
$assignments = new Assignment($db);
$returns = new ReturnReview($db);
$marker = 'QA_R21_' . date('YmdHis') . '_' . bin2hex(random_bytes(3));
$created = [
    'category_id' => null,
    'inventory_id' => null,
    'assignment_id' => null,
    'return_id' => null,
    'requester_collaborator_id' => null,
    'receiver_collaborator_id' => null,
    'requester_user_id' => null,
    'receiver_user_id' => null,
];

$ok = static function (string $message): void {
    echo "[OK] {$message}\n";
};

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$fetchOne = static function (Database $db, string $sql, array $params = []): array {
    $row = $db->fetch($sql, $params);
    if (!$row) {
        throw new RuntimeException('No se encontro el registro esperado.');
    }

    return $row;
};

$cleanup = static function () use ($db, &$created, $marker): void {
    $run = static function (string $sql, array $params = []) use ($db): void {
        try {
            $db->execute($sql, $params);
        } catch (Throwable) {
            // La verificacion usa datos temporales; una sentencia puede no aplicar
            // si una etapa fallo antes de crear todas las filas.
        }
    };

    if ($created['return_id']) {
        $run('DELETE FROM revisiones_tecnicas WHERE devolucion_id = :id', ['id' => $created['return_id']]);
        $run('DELETE FROM devoluciones WHERE id = :id', ['id' => $created['return_id']]);
    }
    if ($created['inventory_id']) {
        $run('DELETE FROM inventario_estado_historial WHERE inventario_id = :id', ['id' => $created['inventory_id']]);
    }
    if ($created['assignment_id']) {
        $run('DELETE FROM asignaciones WHERE id = :id', ['id' => $created['assignment_id']]);
    }
    if ($created['inventory_id']) {
        $run('DELETE FROM inventario_imagenes WHERE inventario_id = :id', ['id' => $created['inventory_id']]);
        $run('DELETE FROM inventario WHERE id = :id', ['id' => $created['inventory_id']]);
    }
    foreach (['requester_user_id', 'receiver_user_id'] as $key) {
        if ($created[$key]) {
            $run('DELETE FROM usuarios WHERE id = :id', ['id' => $created[$key]]);
        }
    }
    foreach (['requester_collaborator_id', 'receiver_collaborator_id'] as $key) {
        if ($created[$key]) {
            $run('DELETE FROM colaboradores WHERE id = :id', ['id' => $created[$key]]);
        }
    }
    if ($created['category_id']) {
        $run('DELETE FROM categorias WHERE id = :id', ['id' => $created['category_id']]);
    }
    $run(
        "DELETE rt
         FROM revisiones_tecnicas rt
         INNER JOIN devoluciones d ON d.id = rt.devolucion_id
         INNER JOIN inventario i ON i.id = d.inventario_id
         WHERE i.codigo_activo LIKE :marker_review",
        ['marker_review' => 'QA_R21_%']
    );
    $run(
        "DELETE d
         FROM devoluciones d
         INNER JOIN inventario i ON i.id = d.inventario_id
         WHERE i.codigo_activo LIKE :marker_return",
        ['marker_return' => 'QA_R21_%']
    );
    $run(
        "DELETE h
         FROM inventario_estado_historial h
         INNER JOIN inventario i ON i.id = h.inventario_id
         WHERE i.codigo_activo LIKE :marker_history",
        ['marker_history' => 'QA_R21_%']
    );
    $run(
        "DELETE a
         FROM asignaciones a
         INNER JOIN inventario i ON i.id = a.inventario_id
         WHERE i.codigo_activo LIKE :marker_assignment",
        ['marker_assignment' => 'QA_R21_%']
    );
    $run(
        "DELETE ii
         FROM inventario_imagenes ii
         INNER JOIN inventario i ON i.id = ii.inventario_id
         WHERE i.codigo_activo LIKE :marker_image",
        ['marker_image' => 'QA_R21_%']
    );
    $run('DELETE FROM inventario WHERE codigo_activo LIKE :marker_inventory_delete', ['marker_inventory_delete' => 'QA_R21_%']);
    $run('DELETE FROM usuarios WHERE nombre_usuario LIKE :marker_user_delete', ['marker_user_delete' => 'qa_r21_%']);
    $run('DELETE FROM colaboradores WHERE identificacion LIKE :marker_collaborator_delete', ['marker_collaborator_delete' => 'QA_R21_%']);
    $run('DELETE FROM categorias WHERE nombre LIKE :marker_category_delete', ['marker_category_delete' => 'QA_R21_%']);
    $run(
        "DELETE FROM bitacora WHERE descripcion LIKE :marker_description OR correlation_id LIKE :marker_correlation",
        [
            'marker_description' => '%' . $marker . '%',
            'marker_correlation' => '%' . $marker . '%',
        ]
    );

    $leftovers = $db->fetch(
        "SELECT
            (SELECT COUNT(*) FROM inventario WHERE codigo_activo LIKE :marker_inventory) +
            (SELECT COUNT(*) FROM colaboradores WHERE identificacion LIKE :marker_collaborator) +
            (SELECT COUNT(*) FROM usuarios WHERE nombre_usuario LIKE :lower_marker_user) +
            (SELECT COUNT(*) FROM categorias WHERE nombre LIKE :marker_category) AS total",
        [
            'marker_inventory' => 'QA_R21_%',
            'marker_collaborator' => 'QA_R21_%',
            'lower_marker_user' => 'qa_r21_%',
            'marker_category' => 'QA_R21_%',
        ]
    );
    if ((int) ($leftovers['total'] ?? 0) > 0) {
        fwrite(STDERR, "[WARN] Quedaron registros temporales QA_R21_*; revise la limpieza manual.\n");
    }
};

try {
    $cleanup();

    $assert($assignments->supportsFormalReturns(), 'La base no soporta devoluciones formales.');
    $assert($returns->schemaReady(), 'La base no tiene columnas de recepcion fisica.');

    $created['category_id'] = $db->insert(
        "INSERT INTO categorias (nombre, tipo, descripcion, activo)
         VALUES (:nombre, 'HARDWARE', :descripcion, 1)",
        [
            'nombre' => $marker . '_Categoria',
            'descripcion' => 'Categoria temporal para verificar requisito 21.',
        ]
    );

    $created['requester_collaborator_id'] = $db->insert(
        "INSERT INTO colaboradores (nombres, apellidos, identificacion, departamento, ubicacion, direccion, telefono, email, activo)
         VALUES ('QA', 'Solicitante', :identificacion, 'QA', 'Lab QA', 'Temporal', '6000-2101', :email, 1)",
        [
            'identificacion' => $marker . '_SOL',
            'email' => strtolower($marker) . '.solicitante@cmdb.local',
        ]
    );
    $created['receiver_collaborator_id'] = $db->insert(
        "INSERT INTO colaboradores (nombres, apellidos, identificacion, departamento, ubicacion, direccion, telefono, email, activo)
         VALUES ('QA', 'Receptor', :identificacion, 'Soporte', 'Mesa QA', 'Temporal', '6000-2102', :email, 1)",
        [
            'identificacion' => $marker . '_REC',
            'email' => strtolower($marker) . '.receptor@cmdb.local',
        ]
    );

    $password = password_hash($marker . '_Password123*', PASSWORD_BCRYPT);
    $created['requester_user_id'] = $db->insert(
        "INSERT INTO usuarios (colaborador_id, nombre_usuario, email, password_hash, rol, activo, estado_cuenta, intentos_fallidos)
         VALUES (:colaborador_id, :usuario, :email, :password, 'COLABORADOR', 1, 'ACTIVO', 0)",
        [
            'colaborador_id' => $created['requester_collaborator_id'],
            'usuario' => strtolower($marker) . '_sol',
            'email' => strtolower($marker) . '.solicitante@cmdb.local',
            'password' => $password,
        ]
    );
    $created['receiver_user_id'] = $db->insert(
        "INSERT INTO usuarios (colaborador_id, nombre_usuario, email, password_hash, rol, activo, estado_cuenta, intentos_fallidos)
         VALUES (:colaborador_id, :usuario, :email, :password, 'OPERADOR', 1, 'ACTIVO', 0)",
        [
            'colaborador_id' => $created['receiver_collaborator_id'],
            'usuario' => strtolower($marker) . '_rec',
            'email' => strtolower($marker) . '.receptor@cmdb.local',
            'password' => $password,
        ]
    );

    $created['inventory_id'] = $db->insert(
        "INSERT INTO inventario
            (categoria_id, codigo_activo, nombre, tipo_activo, subcategoria, marca, modelo, serie, costo, fecha_ingreso, vida_util_meses, estado, notas, firma_integridad, activo)
         VALUES
            (:categoria_id, :codigo, 'Equipo QA Requisito 21', 'HARDWARE', 'Prueba', 'QA', 'R21', :serie, 10.00, CURDATE(), 36, 'DISPONIBLE', :notas, :firma, 1)",
        [
            'categoria_id' => $created['category_id'],
            'codigo' => $marker,
            'serie' => $marker . '_SERIE',
            'notas' => 'Registro temporal para verificar flujo de devolucion independiente.',
            'firma' => hash('sha256', $marker),
        ]
    );

    $created['assignment_id'] = $assignments->create([
        'inventario_id' => $created['inventory_id'],
        'colaborador_id' => $created['requester_collaborator_id'],
        'fecha_asignacion' => date('Y-m-d'),
        'ip_asignada' => '10.21.0.21',
        'observaciones' => 'Asignacion temporal ' . $marker,
    ], $created['receiver_user_id']);
    $ok('Asignacion temporal creada y activo queda ASIGNADO.');

    $assignments->close(
        $created['assignment_id'],
        'Solicitud temporal ' . $marker,
        [
            'motivo' => 'Verificacion requisito 21',
            'observaciones' => 'Solicitud sin recepcion fisica aun.',
            'evidencia' => 'qa-r21.txt',
        ],
        $created['requester_user_id'],
        null,
        $created['requester_collaborator_id']
    );

    $assignment = $fetchOne($db, 'SELECT * FROM asignaciones WHERE id = :id', ['id' => $created['assignment_id']]);
    $inventory = $fetchOne($db, 'SELECT * FROM inventario WHERE id = :id', ['id' => $created['inventory_id']]);
    $return = $fetchOne($db, 'SELECT * FROM devoluciones WHERE asignacion_id = :id', ['id' => $created['assignment_id']]);
    $created['return_id'] = (int) $return['id'];

    $assert($assignment['estado'] === 'ACTIVA', 'La solicitud no debe cerrar inmediatamente la asignacion.');
    $assert($assignment['fecha_devolucion'] === null, 'La solicitud no debe registrar fecha_devolucion.');
    $assert($inventory['estado'] === InventoryStatus::DEVOLUCION_REGISTRADA, 'El activo debe quedar en DEVOLUCION_REGISTRADA tras la solicitud.');
    $assert((int) $return['solicitado_por'] === $created['requester_user_id'], 'El solicitante debe ser el colaborador.');
    $assert($return['recibido_por'] === null, 'La solicitud debe dejar recibido_por en NULL.');
    $assert($return['fecha_recepcion'] === null, 'La solicitud debe dejar fecha_recepcion en NULL.');
    $assert($return['estado_fisico'] === null, 'La solicitud no debe validar condicion fisica antes de recepcion.');
    $assert($return['estado'] === 'PENDIENTE_REVISION', 'La solicitud debe quedar pendiente de recepcion.');
    $ok('Solicitud registrada sin cerrar asignacion y sin receptor fisico.');

    $sameUserRejected = false;
    try {
        $returns->receiveReturn($created['return_id'], $created['requester_user_id'], null, [
            'estado_fisico' => 'BUENO',
            'accesorios_recibidos' => 'Cargador',
            'observacion_recepcion' => 'Intento invalido.',
        ]);
    } catch (Throwable) {
        $sameUserRejected = true;
    }
    $assert($sameUserRejected, 'El solicitante no debe poder registrarse como receptor fisico.');
    $ok('Recepcion por el mismo solicitante rechazada.');

    $returns->receiveReturn($created['return_id'], $created['receiver_user_id'], null, [
        'estado_fisico' => 'REGULAR',
        'evidencia' => 'recepcion-r21.txt',
        'accesorios_recibidos' => 'Cargador y maletin',
        'observacion_recepcion' => 'Recepcion independiente validada.',
    ]);

    $assignment = $fetchOne($db, 'SELECT * FROM asignaciones WHERE id = :id', ['id' => $created['assignment_id']]);
    $inventory = $fetchOne($db, 'SELECT * FROM inventario WHERE id = :id', ['id' => $created['inventory_id']]);
    $return = $fetchOne($db, 'SELECT * FROM devoluciones WHERE id = :id', ['id' => $created['return_id']]);

    $assert($assignment['estado'] === 'ACTIVA', 'La recepcion fisica aun no debe cerrar la asignacion.');
    $assert($assignment['fecha_devolucion'] === null, 'La recepcion fisica aun no debe registrar fecha_devolucion.');
    $assert($inventory['estado'] === InventoryStatus::REVISION_TECNICA, 'La recepcion debe mover el activo a REVISION_TECNICA.');
    $assert((int) $return['recibido_por'] === $created['receiver_user_id'], 'El receptor fisico debe ser operador/admin distinto.');
    $assert((int) $return['recibido_por'] !== (int) $return['solicitado_por'], 'Solicitante y receptor deben ser distintos.');
    $assert($return['fecha_recepcion'] !== null, 'La recepcion debe registrar fecha_recepcion.');
    $assert($return['accesorios_recibidos'] === 'Cargador y maletin', 'La recepcion debe conservar accesorios.');
    $assert($return['observacion_recepcion'] === 'Recepcion independiente validada.', 'La recepcion debe conservar observacion.');
    $assert($return['estado'] === 'EN_REVISION', 'La recepcion debe dejar la devolucion EN_REVISION.');
    $ok('Recepcion fisica independiente registrada con condicion y accesorios.');

    $returns->completeReview(
        $created['return_id'],
        $created['receiver_user_id'],
        InventoryStatus::DISPONIBLE,
        'Revision tecnica aprobada para disponibilidad.',
        $created['receiver_user_id'],
        null,
        [
            'diagnostico' => 'Equipo funcional.',
            'opinion_tecnica' => 'Disponible para reasignacion.',
            'recomendacion' => 'Reasignar.',
            'evidencia' => 'revision-r21.txt',
            'aprobador_id' => $created['receiver_user_id'],
        ]
    );

    $assignment = $fetchOne($db, 'SELECT * FROM asignaciones WHERE id = :id', ['id' => $created['assignment_id']]);
    $inventory = $fetchOne($db, 'SELECT * FROM inventario WHERE id = :id', ['id' => $created['inventory_id']]);
    $return = $fetchOne($db, 'SELECT * FROM devoluciones WHERE id = :id', ['id' => $created['return_id']]);

    $assert($assignment['estado'] === 'DEVUELTA', 'Solo la revision tecnica debe cerrar la asignacion.');
    $assert($assignment['fecha_devolucion'] !== null, 'Solo el cierre tecnico debe registrar fecha_devolucion.');
    $assert($inventory['estado'] === InventoryStatus::DISPONIBLE, 'La revision debe dejar el activo en el resultado elegido.');
    $assert($return['estado'] === 'APROBADA', 'La devolucion debe quedar aprobada tras revision.');
    $ok('Revision tecnica cierra asignacion y libera el activo segun resultado.');

    echo "\nVerificacion completa: flujo de devolucion independiente cumple requisito 21.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "[ERROR] {$exception->getMessage()}\n");
    exit(1);
} finally {
    $cleanup();
}

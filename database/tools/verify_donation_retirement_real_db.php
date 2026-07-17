<?php
declare(strict_types=1);

use App\Core\Config;
use App\Core\Database;
use App\Core\InventoryStatus;
use App\Core\ModelFactory;
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
$models = new ModelFactory($db);
$assignments = new Assignment($db);
$returns = new ReturnReview($db);
$inventoryModel = $models->inventory();
$reports = $models->reports();
$marker = 'QA_DON_' . date('YmdHis') . '_' . bin2hex(random_bytes(3));
$created = [
    'category_id' => null,
    'inventory_id' => null,
    'assignment_id' => null,
    'return_id' => null,
    'collaborator_id' => null,
    'operator_collaborator_id' => null,
    'collaborator_user_id' => null,
    'operator_user_id' => null,
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

$cleanup = static function () use ($db, &$created): void {
    $run = static function (string $sql, array $params = []) use ($db): void {
        try {
            $db->execute($sql, $params);
        } catch (Throwable) {
            // Si la prueba falla a media ejecucion, algunas filas temporales no existen.
        }
    };

    if ($created['return_id']) {
        $run('DELETE FROM revisiones_tecnicas WHERE devolucion_id = :id', ['id' => $created['return_id']]);
        $run('DELETE FROM devoluciones WHERE id = :id', ['id' => $created['return_id']]);
    }
    if ($created['inventory_id']) {
        $run('DELETE FROM inventario_estado_historial WHERE inventario_id = :id', ['id' => $created['inventory_id']]);
        $run('DELETE FROM inventario_imagenes WHERE inventario_id = :id', ['id' => $created['inventory_id']]);
    }
    if ($created['assignment_id']) {
        $run('DELETE FROM asignaciones WHERE id = :id', ['id' => $created['assignment_id']]);
    }
    if ($created['inventory_id']) {
        $run('DELETE FROM inventario WHERE id = :id', ['id' => $created['inventory_id']]);
    }
    foreach (['collaborator_user_id', 'operator_user_id'] as $key) {
        if ($created[$key]) {
            $run('DELETE FROM usuarios WHERE id = :id', ['id' => $created[$key]]);
        }
    }
    foreach (['collaborator_id', 'operator_collaborator_id'] as $key) {
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
         WHERE i.codigo_activo LIKE 'QA_DON_%'"
    );
    $run(
        "DELETE d
         FROM devoluciones d
         INNER JOIN inventario i ON i.id = d.inventario_id
         WHERE i.codigo_activo LIKE 'QA_DON_%'"
    );
    $run(
        "DELETE h
         FROM inventario_estado_historial h
         INNER JOIN inventario i ON i.id = h.inventario_id
         WHERE i.codigo_activo LIKE 'QA_DON_%'"
    );
    $run(
        "DELETE a
         FROM asignaciones a
         INNER JOIN inventario i ON i.id = a.inventario_id
         WHERE i.codigo_activo LIKE 'QA_DON_%'"
    );
    $run('DELETE FROM inventario WHERE codigo_activo LIKE :marker', ['marker' => 'QA_DON_%']);
    $run('DELETE FROM usuarios WHERE nombre_usuario LIKE :marker', ['marker' => 'qa_don_%']);
    $run('DELETE FROM colaboradores WHERE identificacion LIKE :marker', ['marker' => 'QA_DON_%']);
    $run('DELETE FROM categorias WHERE nombre LIKE :marker', ['marker' => 'QA_DON_%']);
};

try {
    $cleanup();

    $activeDonations = $db->fetch("SELECT COUNT(*) AS total FROM inventario WHERE estado = 'DONADO' AND activo = 1");
    $assert((int) ($activeDonations['total'] ?? 0) === 0, 'Existen activos DONADO con activo = 1 antes de probar el flujo.');
    $ok('Base real no contiene donaciones activas en inventario operativo.');

    $created['category_id'] = $db->insert(
        "INSERT INTO categorias (nombre, tipo, descripcion, activo)
         VALUES (:nombre, 'HARDWARE', :descripcion, 1)",
        [
            'nombre' => $marker . '_Categoria',
            'descripcion' => 'Categoria temporal para verificar donacion fuera de inventario operativo.',
        ]
    );

    $created['collaborator_id'] = $db->insert(
        "INSERT INTO colaboradores (nombres, apellidos, identificacion, departamento, ubicacion, direccion, telefono, email, activo)
         VALUES ('QA', 'Donante', :identificacion, 'QA', 'Lab QA', 'Temporal', '6000-2501', :email, 1)",
        [
            'identificacion' => $marker . '_COL',
            'email' => strtolower($marker) . '.colaborador@cmdb.local',
        ]
    );
    $created['operator_collaborator_id'] = $db->insert(
        "INSERT INTO colaboradores (nombres, apellidos, identificacion, departamento, ubicacion, direccion, telefono, email, activo)
         VALUES ('QA', 'Operador Donacion', :identificacion, 'Soporte', 'Mesa QA', 'Temporal', '6000-2502', :email, 1)",
        [
            'identificacion' => $marker . '_OPE',
            'email' => strtolower($marker) . '.operador@cmdb.local',
        ]
    );

    $password = password_hash($marker . '_Password123*', PASSWORD_BCRYPT);
    $created['collaborator_user_id'] = $db->insert(
        "INSERT INTO usuarios (colaborador_id, nombre_usuario, email, password_hash, rol, activo, estado_cuenta, intentos_fallidos)
         VALUES (:colaborador_id, :usuario, :email, :password, 'COLABORADOR', 1, 'ACTIVO', 0)",
        [
            'colaborador_id' => $created['collaborator_id'],
            'usuario' => strtolower($marker) . '_col',
            'email' => strtolower($marker) . '.colaborador@cmdb.local',
            'password' => $password,
        ]
    );
    $created['operator_user_id'] = $db->insert(
        "INSERT INTO usuarios (colaborador_id, nombre_usuario, email, password_hash, rol, activo, estado_cuenta, intentos_fallidos)
         VALUES (:colaborador_id, :usuario, :email, :password, 'OPERADOR', 1, 'ACTIVO', 0)",
        [
            'colaborador_id' => $created['operator_collaborator_id'],
            'usuario' => strtolower($marker) . '_ope',
            'email' => strtolower($marker) . '.operador@cmdb.local',
            'password' => $password,
        ]
    );

    $created['inventory_id'] = $db->insert(
        "INSERT INTO inventario
            (categoria_id, codigo_activo, nombre, tipo_activo, subcategoria, marca, modelo, serie, costo, fecha_ingreso, vida_util_meses, estado, notas, firma_integridad, activo)
         VALUES
            (:categoria_id, :codigo, 'Equipo QA Donacion Retiro', 'HARDWARE', 'Prueba', 'QA', 'DON', :serie, 75.00, CURDATE(), 36, 'DISPONIBLE', :notas, :firma, 1)",
        [
            'categoria_id' => $created['category_id'],
            'codigo' => $marker,
            'serie' => $marker . '_SERIE',
            'notas' => 'Registro temporal para verificar donacion fuera de inventario operativo.',
            'firma' => hash('sha256', $marker),
        ]
    );

    $created['assignment_id'] = $assignments->create([
        'inventario_id' => $created['inventory_id'],
        'colaborador_id' => $created['collaborator_id'],
        'fecha_asignacion' => date('Y-m-d'),
        'ip_asignada' => '10.25.0.25',
        'observaciones' => 'Asignacion temporal para donacion ' . $marker,
    ], $created['operator_user_id']);

    $assignments->close(
        $created['assignment_id'],
        'Solicitud temporal para donacion ' . $marker,
        [
            'motivo' => 'Verificacion donacion fuera de inventario operativo',
            'observaciones' => 'Solicitud de devolucion para donar.',
            'evidencia' => 'qa-don-solicitud.txt',
        ],
        $created['collaborator_user_id'],
        null,
        $created['collaborator_id']
    );
    $return = $fetchOne($db, 'SELECT * FROM devoluciones WHERE asignacion_id = :id', ['id' => $created['assignment_id']]);
    $created['return_id'] = (int) $return['id'];

    $returns->receiveReturn($created['return_id'], $created['operator_user_id'], null, [
        'estado_fisico' => 'BUENO',
        'evidencia' => 'qa-don-recepcion.txt',
        'accesorios_recibidos' => 'Cargador',
        'observacion_recepcion' => 'Recepcion fisica previa a donacion.',
    ]);

    $returns->completeReview(
        $created['return_id'],
        $created['operator_user_id'],
        InventoryStatus::DONADO,
        'Equipo funcional aprobado para donacion por renovacion tecnologica.',
        $created['operator_user_id'],
        null,
        [
            'diagnostico' => 'Equipo funcional con desgaste normal.',
            'opinion_tecnica' => 'Apto para donacion.',
            'recomendacion' => 'Donar a programa academico externo.',
            'evidencia' => 'acta-qa-don.pdf',
            'aprobador_id' => $created['operator_user_id'],
            'responsable_donacion' => 'Comite QA',
            'beneficiario_donacion' => 'Programa QA Comunitario',
            'valor_donacion' => 25.50,
            'fecha_donacion' => date('Y-m-d'),
            'evidencia_donacion' => 'acta-qa-don.pdf',
            'observacion_donacion' => 'Donacion temporal verificada en base real.',
            'autorizador_donacion_id' => $created['operator_user_id'],
        ]
    );

    $inventory = $fetchOne($db, 'SELECT * FROM inventario WHERE id = :id', ['id' => $created['inventory_id']]);
    $assignment = $fetchOne($db, 'SELECT * FROM asignaciones WHERE id = :id', ['id' => $created['assignment_id']]);
    $assert($inventory['estado'] === InventoryStatus::DONADO, 'La revision debe dejar el activo DONADO.');
    $assert((int) $inventory['activo'] === 0, 'La donacion debe retirar el activo del inventario operativo.');
    $assert($assignment['estado'] === 'DEVUELTA', 'La asignacion debe quedar cerrada al completar la revision.');
    $ok('Flujo formal de donacion deja estado DONADO y activo = 0.');

    $operationalRows = $inventoryModel->all(['buscar' => $marker]);
    $assert(count($operationalRows) === 0, 'El inventario operativo no debe listar el activo donado.');
    $ok('Inventario general excluye el activo donado por activo = 0.');

    $donations = $reports->donations(['buscar' => $marker]);
    $assert(count($donations) === 1, 'El reporte historico de donaciones debe encontrar el activo inactivo.');
    $assert((int) ($donations[0]['activo'] ?? 1) === 0, 'El reporte debe conservar la fila historica con activo = 0.');
    $assert(($donations[0]['beneficiario_donacion'] ?? '') === 'Programa QA Comunitario', 'El reporte debe conservar beneficiario de donacion.');

    $report = $reports->donationsReport(['buscar' => $marker]);
    $assert(count($report['rows']) === 1, 'El Excel de donaciones debe incluir el activo donado inactivo.');
    $assert($report['rows'][0][0] === $marker, 'La fila exportable debe conservar el codigo del activo donado.');
    $ok('Reporte de donaciones usa historico independiente e incluye donados inactivos.');

    echo "\nVerificacion completa: donacion retira del inventario operativo y conserva reporte historico en base real.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "[ERROR] {$exception->getMessage()}\n");
    exit(1);
} finally {
    $cleanup();
}

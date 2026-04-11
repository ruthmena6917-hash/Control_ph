<?php
require '../../auth/session.php';
require '../../config/database.php';

verificarRol(['gerente', 'seguridad']);

$sql = "SELECT 
            t.id,
            t.fecha,
            t.hora_inicio,
            t.hora_fin,
            t.activo,
            u.nombre AS nombre_empleado,
            e.cargo
        FROM turnos t
        JOIN empleados e ON e.id = t.empleado_id
        JOIN usuarios u ON u.id = e.usuario_id
        WHERE t.activo = 1
        AND t.fecha = CURDATE()
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$turno = $stmt->fetch(PDO::FETCH_ASSOC);

header('Content-Type: application/json');

if (!$turno) {
    echo json_encode([
        'activo' => false,
        'mensaje' => 'No hay turno activo en este momento'
    ]);
    exit;
}

echo json_encode($turno);
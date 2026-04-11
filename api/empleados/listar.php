<?php
require '../../auth/session.php';
require '../../config/database.php';

verificarRol(['gerente']);

$sql = "SELECT 
            e.id,
            u.nombre,
            u.cedula,
            u.telefono,
            u.email,
            e.cargo,
            e.fecha_ingreso,
            t.fecha AS turno_fecha,
            t.hora_inicio,
            t.hora_fin,
            t.activo AS turno_activo
        FROM empleados e
        JOIN usuarios u ON u.id = e.usuario_id
        LEFT JOIN turnos t ON t.empleado_id = e.id 
            AND t.fecha = CURDATE()
        WHERE e.activo = 1
        ORDER BY e.cargo ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($empleados);
<?php
require_once '../../auth/session.php';
require_once '../../config/database.php';

// Verificar que sea gerente
verificarRol(['gerente']);

header('Content-Type: application/json');

try {
    $sql = "SELECT 
                u.id, 
                u.nombre, 
                u.cedula, 
                u.email, 
                u.telefono,
                u.foto_url,
                u.activo,
                u.fecha_creacion,
                r.nombre AS rol_nombre,
                auth.username,
                apt.numero as apt_numero,
                apt.torre as apt_torre,
                t.hora_inicio as turno_inicio,
                t.hora_fin as turno_fin
            FROM usuarios u
            JOIN roles r ON u.rol_id = r.id
            LEFT JOIN autenticacion auth ON u.id = auth.usuario_id
            LEFT JOIN residentes res ON u.id = res.usuario_id
            LEFT JOIN apartamentos apt ON res.apartamento_id = apt.id
            LEFT JOIN empleados emp ON u.id = emp.usuario_id
            LEFT JOIN turnos t ON emp.id = t.empleado_id AND t.fecha = CURDATE()
            ORDER BY u.id DESC";

    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($usuarios);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>

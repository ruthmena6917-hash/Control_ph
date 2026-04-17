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
                u.activo,
                u.fecha_creacion,
                r.nombre AS rol_nombre,
                auth.username
            FROM usuarios u
            JOIN roles r ON u.rol_id = r.id
            LEFT JOIN autenticacion auth ON u.id = auth.usuario_id
            ORDER BY u.id DESC";

    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($usuarios);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>

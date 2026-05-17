<?php
require_once '../../auth/session.php';
require_once '../../config/database.php';

verificarRol(['gerente']);

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
    exit;
}

try {
    $sql = "SELECT 
                u.id, 
                u.nombre, 
                u.cedula, 
                u.email, 
                u.telefono,
                u.tipo_sangre,
                u.contacto_emergencia,
                u.placa_principal,
                u.foto_url,
                u.activo,
                u.fecha_creacion,
                r.nombre AS rol_nombre,
                r.descripcion AS rol_descripcion,
                auth.username,
                auth.ultimo_login
            FROM usuarios u
            JOIN roles r ON u.rol_id = r.id
            LEFT JOIN autenticacion auth ON u.id = auth.usuario_id
            WHERE u.id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
        exit;
    }

    echo json_encode(['success' => true, 'data' => $usuario]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

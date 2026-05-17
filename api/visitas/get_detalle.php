<?php
require '../../auth/session.php';
verificarRol(['seguridad', 'gerente']);
require '../../config/database.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
    exit;
}

try {
    $sql = "SELECT v.*, r.usuario_id, u.nombre as residente_nombre, a.numero as apto, a.torre
            FROM visitas v
            LEFT JOIN residentes r ON v.residente_id = r.id
            LEFT JOIN usuarios u ON r.usuario_id = u.id
            LEFT JOIN apartamentos a ON v.apartamento_id = a.id
            WHERE v.id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $visita = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$visita) {
        echo json_encode(['success' => false, 'error' => 'Visita no encontrada']);
        exit;
    }

    echo json_encode(['success' => true, 'data' => $visita]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

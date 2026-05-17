<?php
require '../../auth/session.php';
require '../../config/database.php';

header('Content-Type: application/json');

$usuario_id = $_SESSION['usuario_id'];

try {
    // Obtener notificaciones no leídas
    $stmt = $pdo->prepare("SELECT id, mensaje, fecha_creacion FROM notificaciones 
                           WHERE usuario_id = :uid AND leida = 0 
                           ORDER BY fecha_creacion DESC");
    $stmt->execute([':uid' => $usuario_id]);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Marcar como leídas después de obtenerlas (demo simple)
    if (!empty($notificaciones)) {
        $stmt_mark = $pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE usuario_id = :uid");
        $stmt_mark->execute([':uid' => $usuario_id]);
    }

    echo json_encode(['success' => true, 'data' => $notificaciones]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

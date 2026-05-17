<?php

require '../../config/database.php';

/**
 * Endpoint para Salida Automática vía IoT
 * Espera un POST con el 'tag_id' del sensor.
 * No requiere sesión de usuario ya que es llamado por un dispositivo externo (simulado).
 */

header('Content-Type: application/json');

$tag_id = $_POST['tag_id'] ?? null;

if (!$tag_id) {
    // Si no es POST, intentar leer del body (JSON)
    $input = json_decode(file_get_contents('php://input'), true);
    $tag_id = $input['tag_id'] ?? null;
}

if (!$tag_id) {
    echo json_encode(['success' => false, 'error' => 'No se recibió el ID del Tag']);
    exit;
}

try {
    // 1. Buscar la visita activa que tiene este Tag asignado
    $sql = "SELECT id, visitante_nombre FROM visitas 
            WHERE token_iot = :tag_id 
            AND estado = 'en_edificio' 
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':tag_id' => $tag_id]);
    $visita = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$visita) {
        echo json_encode(['success' => false, 'error' => 'No se encontró una visita activa con este Tag']);
        exit;
    }

    // 2. Marcar la salida automáticamente
    $sql_update = "UPDATE visitas 
                   SET estado = 'finalizada', 
                       fecha_salida = NOW(),
                       notas = CONCAT(IFNULL(notas, ''), '\n[SISTEMA IOT] Salida detectada automáticamente.')
                   WHERE id = :id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([':id' => $visita['id']]);

    echo json_encode([
        'success' => true, 
        'message' => 'Salida registrada para: ' . $visita['visitante_nombre'],
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>

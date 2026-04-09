<?php
require_once '../../config/database.php';
require_once '../../auth/session.php';

// Solo el gerente puede eliminar
verificarRol(['gerente']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        header('Content-Type: application/json', true, 400);
        echo json_encode(['error' => 'ID de visita no proporcionado']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM visitas WHERE id = :id");
        $stmt->execute([':id' => $id]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['error' => 'Error al eliminar la visita: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json', true, 405);
    echo json_encode(['error' => 'Método no permitido']);
}

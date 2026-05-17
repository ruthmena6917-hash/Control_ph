<?php
require_once '../../config/database.php';
require_once '../../auth/session.php';

// Solo el gerente puede desactivar usuarios
verificarRol(['gerente']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        header('Content-Type: application/json', true, 400);
        echo json_encode(['error' => 'ID de usuario no proporcionado']);
        exit;
    }

    // Evitar que el gerente se desactive a sí mismo (opcional pero recomendado)
    if ($id == $_SESSION['usuario_id']) {
        header('Content-Type: application/json', true, 400);
        echo json_encode(['error' => 'No puedes desactivar tu propia cuenta administrativa']);
        exit;
    }

    try {
        // Realizamos un borrado lógico (soft delete)
        $stmt = $pdo->prepare("UPDATE usuarios SET activo = 0 WHERE id = :id");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json', true, 404);
            echo json_encode(['error' => 'Usuario no encontrado o ya estaba inactivo']);
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['error' => 'Error al desactivar el usuario: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json', true, 405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>

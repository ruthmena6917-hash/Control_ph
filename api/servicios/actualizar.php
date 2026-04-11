<?php
require_once '../../config/database.php';
require_once '../../auth/session.php';

// Solo el gerente puede editar
verificarRol(['gerente']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nombre = trim($_POST['nombre'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $fecha = $_POST['fecha_programada'] ?? null;

    if (!$id || !$nombre || !$fecha) {
        header('Content-Type: application/json', true, 400);
        echo json_encode(['error' => 'Datos incompletos para actualizar']);
        exit;
    }

    try {
        $sql = "UPDATE visitas SET 
                    visitante_nombre = :nombre, 
                    visitante_cedula = :cedula, 
                    fecha_programada = :fecha 
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':cedula' => $cedula,
            ':fecha' => $fecha,
            ':id' => $id
        ]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['error' => 'Error al actualizar el servicio: ' . $e->getMessage()]);
    }
}

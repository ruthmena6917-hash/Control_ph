<?php
require '../../auth/session.php';
verificarRol(['residente']);
require '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? null;
    $cedula = $_POST['cedula'] ?? null;
    $fecha  = $_POST['fecha']  ?? null;
    $usuario_id = $_SESSION['usuario_id'];

    if (!$nombre || !$cedula || !$fecha) {
        echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios']);
        exit;
    }

    // 1. Obtener residente_id y apartamento_id automáticos
    $stmt = $pdo->prepare("SELECT id, apartamento_id FROM residentes WHERE usuario_id = :usuario_id LIMIT 1");
    $stmt->execute([':usuario_id' => $usuario_id]);
    $residente = $stmt->fetch();

    if (!$residente) {
        echo json_encode(['success' => false, 'error' => 'Perfil de residente no encontrado']);
        exit;
    }

    try {
        // 2. Insertar con campos oficiales del SQL
        $sql = "INSERT INTO visitas 
                (visitante_nombre, visitante_cedula, fecha_programada, estado, residente_id, apartamento_id, registrado_por)
                VALUES (:nombre, :cedula, :fecha, 'pendiente', :residente_id, :apartamento_id, :registrado_por)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre'         => $nombre,
            ':cedula'         => $cedula,
            ':fecha'          => $fecha,
            ':residente_id'   => $residente['id'],
            ':apartamento_id' => $residente['apartamento_id'],
            ':registrado_por' => $usuario_id
        ]);

        echo json_encode(['success' => true, 'message' => 'Visita registrada exitosamente']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
    }
    exit;
}
?>

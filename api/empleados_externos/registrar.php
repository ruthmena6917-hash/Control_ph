<?php
require_once '../../auth/session.php';
require_once '../../config/database.php';

verificarRol(['gerente']);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $empresa = trim($_POST['empresa'] ?? '');
    $servicio_tipo = trim($_POST['servicio_tipo'] ?? '');

    if (empty($nombre) || empty($cedula) || empty($servicio_tipo)) {
        echo json_encode(['success' => false, 'error' => 'Nombre, Cédula y Tipo de Servicio son obligatorios']);
        exit;
    }

    try {
        // Generar un código QR único (simulado con un string aleatorio)
        $codigo_qr = 'EXT-' . strtoupper(substr(md5(uniqid()), 0, 8));

        $sql = "INSERT INTO empleados_externos (nombre, cedula, empresa, servicio_tipo, codigo_qr, activo) 
                VALUES (:nombre, :cedula, :empresa, :servicio_tipo, :codigo_qr, 1)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':cedula' => $cedula,
            ':empresa' => $empresa,
            ':servicio_tipo' => $servicio_tipo,
            ':codigo_qr' => $codigo_qr
        ]);

        echo json_encode(['success' => true, 'message' => 'Empleado externo registrado con éxito', 'codigo_qr' => $codigo_qr]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error al registrar: ' . $e->getMessage()]);
    }
    exit;
}
?>

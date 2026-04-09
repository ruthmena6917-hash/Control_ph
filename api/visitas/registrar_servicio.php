<?php
require_once '../../config/database.php';
require_once '../../auth/session.php';

// Solo el gerente puede registrar servicios manuales
verificarRol(['gerente']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '--');
    $tipo = $_POST['tipo'] ?? 'Otros servicios';
    $fecha = $_POST['fecha_programada'] ?? date('Y-m-d H:i:s');
    
    $nombre_completo = $nombre . ' ' . $apellido;
    $usuario_id = $_SESSION['usuario_id'];

    try {
        // Usamos el residente_id 1 y apartamento_id 1 como "Gerencia / Sistema"
        $sql = "INSERT INTO visitas (
                    visitante_nombre, 
                    visitante_cedula, 
                    residente_id, 
                    apartamento_id, 
                    fecha_programada, 
                    fecha_entrada_real, 
                    estado, 
                    registrado_por,
                    notas
                ) VALUES (
                    :nombre, 
                    :cedula, 
                    1, 
                    1, 
                    :fecha, 
                    :fecha, 
                    'en_edificio', 
                    :registrado_por,
                    :notas
                )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre_completo,
            ':cedula' => $cedula,
            ':fecha' => $fecha,
            ':registrado_por' => $usuario_id,
            ':notas' => '[SERVICIO] ' . $tipo
        ]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['error' => 'Error al registrar servicio: ' . $e->getMessage()]);
    }
}

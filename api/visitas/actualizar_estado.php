<?php

require '../../auth/session.php';
require '../../config/database.php';

verificarRol(['seguridad']);

header('Content-Type: application/json');

$id     = $_POST['id']     ?? null;
$estado = $_POST['estado'] ?? null;

// Validar que llegaron los datos
if (!$id || !$estado) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

// Validar que el estado sea válido
$estados_permitidos = ['en_edificio', 'finalizada'];
if (!in_array($estado, $estados_permitidos)) {
    echo json_encode(['success' => false, 'error' => 'Estado no válido']);
    exit;
}

try {
    if ($estado === 'en_edificio') {
        $sql = "UPDATE visitas 
                SET estado = 'en_edificio',
                    fecha_entrada_real = NOW(),
                    validado_por = :usuario_id
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $_SESSION['usuario_id'],
            ':id'         => $id
        ]);
    }

    if ($estado === 'finalizada') {
        $sql = "UPDATE visitas 
                SET estado = 'finalizada',
                    fecha_salida_real = NOW()
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
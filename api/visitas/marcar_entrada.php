<?php
require '../../auth/session.php';
verificarRol(['seguridad']);
require '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['visita_id'];
    $placa = !empty($_POST['placa']) ? $_POST['placa'] : null;

    // Sincronizado con control_visitas (1).sql: fecha_entrada_real
    $sql = "UPDATE visitas 
            SET estado = 'en_edificio', 
                fecha_entrada_real = NOW(), 
                placa_vehiculo = :placa 
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':placa' => $placa,
        ':id' => $id
    ]);

    header('Location: ../../views/dashboard_seguridad.php?success=entrada');
    exit;
}
?>

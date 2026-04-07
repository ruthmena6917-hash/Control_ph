<?php
require '../../auth/session.php';
verificarRol(['seguridad']);
require '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['visita_id'];

    $sql = "UPDATE visitas 
            SET estado = 'finalizada', 
                fecha_salida = NOW() 
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    header('Location: ../../views/dashboard_seguridad.php?success=salida');
    exit;
}
?>

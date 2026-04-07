<?php
require '../../auth/session.php';
verificarRol(['seguridad']);
require '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['visita_id'];

    $sql = "UPDATE visitas 
            SET estado = 'en_edificio', fecha_entrada = NOW() 
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    header('Location: ../../views/visitas/lista.php');
    exit;
}

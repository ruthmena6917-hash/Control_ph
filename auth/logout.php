<?php
session_start();

// Registrar en bitacora antes de cerrar sesión
require '../config/database.php';

if (isset($_SESSION['usuario_id'])) {
    $sql = "INSERT INTO bitacora_accesos (usuario_id, accion) VALUES (:id, 'logout')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $_SESSION['usuario_id']]);
}

// Destruir la sesión
$_SESSION = [];
session_destroy();

header('Location: ../views/login.php');
exit;
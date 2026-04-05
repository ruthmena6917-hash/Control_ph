<?php
require '../auth/session.php';
verificarRol(['seguridad']);
require '../config/database.php';

$pendientes = $pdo->query("SELECT COUNT(*) FROM visitas WHERE estado = 'pendiente'")->fetchColumn();
$en_edificio = $pdo->query("SELECT COUNT(*) FROM visitas WHERE estado = 'en_edificio'")->fetchColumn();
$finalizadas = $pdo->query("SELECT COUNT(*) FROM visitas WHERE estado = 'finalizada' AND DATE(fecha_salida) = CURDATE()")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seguridad</title>
</head>
<body>
    <h1>Panel de Seguridad</h1>

    <p>Visitas pendientes: <?= $pendientes ?></p>
    <p>En edificio: <?= $en_edificio ?></p>
    <p>Finalizadas hoy: <?= $finalizadas ?></p>

    <a href="lista.php">Ver visitas</a>
</body>
</html>
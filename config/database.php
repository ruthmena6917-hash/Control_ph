<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

$host     = 'localhost';
$dbname   = 'control_visitas';
$user     = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die(json_encode([
        'error' => 'Error de conexión: ' . $e->getMessage()
    ]));
}

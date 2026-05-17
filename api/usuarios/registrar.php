<?php
require '../../auth/session.php';
verificarRol(['gerente']);
require '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $rol_id = $_POST['rol_id'] ?? null;
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $tipo_sangre = trim($_POST['tipo_sangre'] ?? '');
    $contacto_emergencia = trim($_POST['contacto_emergencia'] ?? '');
    $placa_principal = trim($_POST['placa_principal'] ?? '');

    if (empty($nombre) || empty($cedula) || empty($username) || empty($password) || !$rol_id) {
        echo json_encode(['success' => false, 'error' => 'Todos los campos marcados son obligatorios']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Insertar en tabla usuarios
        $sqlUser = "INSERT INTO usuarios (rol_id, nombre, cedula, email, telefono, tipo_sangre, contacto_emergencia, placa_principal) 
                    VALUES (:rol_id, :nombre, :cedula, :email, :telefono, :tipo_sangre, :contacto_emergencia, :placa_principal)";
        $stmtUser = $pdo->prepare($sqlUser);
        $stmtUser->execute([
            ':rol_id'   => $rol_id,
            ':nombre'   => $nombre,
            ':cedula'   => $cedula,
            ':email'    => $email,
            ':telefono' => $telefono,
            ':tipo_sangre' => $tipo_sangre,
            ':contacto_emergencia' => $contacto_emergencia,
            ':placa_principal' => $placa_principal
        ]);
        $usuario_id = $pdo->lastInsertId();

        // 2. Insertar en tabla autenticacion
        $sqlAuth = "INSERT INTO autenticacion (usuario_id, username, password_hash) VALUES (:usuario_id, :username, :password_hash)";
        $stmtAuth = $pdo->prepare($sqlAuth);
        $stmtAuth->execute([
            ':usuario_id'    => $usuario_id,
            ':username'      => $username,
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Error al crear usuario: ' . $e->getMessage()]);
    }
    exit;
}
?>

<?php
require '../../auth/session.php';
verificarRol(['gerente']);
require '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $apartamento_id = $_POST['apartamento_id'] ?? null;
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Para crear un residente, primero necesitamos crearle un usuario con rol 'residente'
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '123456'; // Generic or provided

    if (empty($nombre) || empty($cedula) || !$apartamento_id || empty($username)) {
        echo json_encode(['success' => false, 'error' => 'Nombre, Cédula, Apartamento y Usuario son obligatorios']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Obtener ID del rol 'residente'
        $stmt_rol = $pdo->prepare("SELECT id FROM roles WHERE nombre = 'residente'");
        $stmt_rol->execute();
        $rol = $stmt_rol->fetch();
        
        if (!$rol) throw new Exception("Error: El rol 'residente' no existe en el sistema.");

        // 2. Crear el Usuario
        $sqlUser = "INSERT INTO usuarios (rol_id, nombre, cedula, email, telefono) VALUES (:rid, :nom, :ced, :em, :tel)";
        $stUser = $pdo->prepare($sqlUser);
        $stUser->execute([
            ':rid' => $rol['id'],
            ':nom' => $nombre,
            ':ced' => $cedula,
            ':em'  => $email,
            ':tel' => $telefono
        ]);
        $usuario_id = $pdo->lastInsertId();

        // 3. Crear Autenticación
        $sqlAuth = "INSERT INTO autenticacion (usuario_id, username, password_hash) VALUES (:uid, :usr, :pw)";
        $stAuth = $pdo->prepare($sqlAuth);
        $stAuth->execute([
            ':uid' => $usuario_id,
            ':usr' => $username,
            ':pw'  => password_hash($password, PASSWORD_DEFAULT)
        ]);

        // 4. Crear el Registro de Residente
        $sqlRes = "INSERT INTO residentes (usuario_id, apartamento_id) VALUES (:uid, :aid)";
        $stRes = $pdo->prepare($sqlRes);
        $stRes->execute([':uid' => $usuario_id, ':aid' => $apartamento_id]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Residente registrado exitosamente']);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>

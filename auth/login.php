<?php
session_start();

require '../config/database.php';

$username = $_POST['username'];
$password = $_POST['password'];
// Buscar usuario en la BD
$sql = "SELECT u.id, u.nombre, u.activo, r.nombre AS rol, a.password_hash
        FROM usuarios u
        JOIN roles r ON r.id = u.rol_id
        JOIN autenticacion a ON a.usuario_id = u.id
        WHERE a.username = :username";

$stmt = $pdo->prepare($sql);
$stmt->execute([':username' => $username]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);


//verificar que el ususario existe y está activo
if (!$usuario || $usuario['activo'] != "1"){
        header("Location: ../views/login.php?error=1");
        exit();
}

//verificar la contraseña
if (!password_verify($password, $usuario['password_hash'])){
        header("Location: ../views/login.php?error=1");
        exit();
}

//iniciamos seccion

$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['nombre'] = $usuario['nombre'];
$_SESSION['rol'] = $usuario['rol'];


//registramos en la bitacora 
$sql = "INSERT INTO bitacora_accesos(usuario_id, accion) VALUES (:usuario_id, 'login')";
$stmt = $pdo->prepare($sql);
$stmt->execute([':usuario_id' => $usuario['id']]);

//registrar segun el rol
switch ($usuario['rol']){
        case 'gerente':
                header("location: ../views/dashboard_gerente.php");
                break;
        case 'seguridad':
                header("location: ../views/dashboard_seguridad.php");
                break;
        case 'residente':
                header("location: ../views/dashboard_residente.php");
                break;
        default:
                header("Location: ../views/login.php?error=1");
}
exit;

?>

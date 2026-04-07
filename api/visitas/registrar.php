<?php
require '../../auth/session.php';
verificarRol(['residente']);
require '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $cedula = $_POST['cedula'];
    $fecha  = $_POST['fecha'];
    $usuario_id = $_SESSION['usuario_id'];

    // 1. Obtener residente_id y apartamento_id automáticos
    $stmt = $pdo->prepare("SELECT id, apartamento_id FROM residentes WHERE usuario_id = :usuario_id LIMIT 1");
    $stmt->execute([':usuario_id' => $usuario_id]);
    $residente = $stmt->fetch();

    if (!$residente) {
        die("Error: Perfil de residente no encontrado.");
    }

    // 2. Insertar con campos oficiales del SQL
    $sql = "INSERT INTO visitas 
            (visitante_nombre, visitante_cedula, fecha_programada, estado, residente_id, apartamento_id, registrado_por)
            VALUES (:nombre, :cedula, :fecha, 'pendiente', :residente_id, :apartamento_id, :registrado_por)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre'         => $nombre,
        ':cedula'         => $cedula,
        ':fecha'          => $fecha,
        ':residente_id'   => $residente['id'],
        ':apartamento_id' => $residente['apartamento_id'],
        ':registrado_por' => $usuario_id
    ]);

    header('Location: ../../views/visitas/mis_visitas.php?success=registro');
    exit;
}
?>

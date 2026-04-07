<?php
require '../../auth/session.php';
verificarRol(['residente']);
require '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $visit_id = $_POST['id'];
    $usuario_id = $_SESSION['usuario_id'];

    // 1. Obtener residente_id real para asegurar pertenencia
    $stmt = $pdo->prepare("SELECT id FROM residentes WHERE usuario_id = :usuario_id");
    $stmt->execute([':usuario_id' => $usuario_id]);
    $residente = $stmt->fetch();

    if ($residente) {
        // 2. Solo permitir cancelar si está pendiente Y pertenece al residente
        $sql = "UPDATE visitas 
                SET estado = 'cancelada' 
                WHERE id = :id AND residente_id = :res_id AND estado = 'pendiente'";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id'     => $visit_id,
            ':res_id' => $residente['id']
        ]);
    }

    header('Location: ../../views/visitas/mis_visitas.php?success=cancelado');
    exit;
}
?>

<?php
require '../../auth/session.php';
verificarRol(['seguridad', 'residente', 'admin']);
require '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
   $cedula = isset($_POST['cedula']) ? $_POST['cedula'] : '';
   $fecha  = isset($_POST['fecha']) ? $_POST['fecha'] : '';

    // Insertar la nueva visita
    // Nota: Si la tabla tiene campos adicionales como 'apto' o 'destino', 
    // se pueden agregar aquí. Por ahora usamos los básicos detectados.
    $sql = "INSERT INTO visitas (visitante_nombre, visitante_cedula, fecha_programada, estado) 
            VALUES (:nombre, :cedula, :fecha, :estado)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':cedula' => $cedula,
        ':fecha'  => $fecha,
        ':estado' => $estado
    ]);

    // Redirigir de vuelta a la lista
    header('Location: ../../views/visitas/lista.php?success=1');
    exit;
}
?>

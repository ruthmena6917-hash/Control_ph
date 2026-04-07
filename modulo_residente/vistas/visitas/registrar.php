<?php
require '../../../auth/session.php';
verificarRol(['residente']);
require '../../../config/database.php';

// Necesitamos obtener el residente_id y apartamento_id del usuario actual
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT id, apartamento_id FROM residentes WHERE usuario_id = :usuario_id LIMIT 1");
$stmt->execute([':usuario_id' => $usuario_id]);
$residente = $stmt->fetch();

if (!$residente) {
    die("Error: No se encontró un perfil de residente relacionado con tu usuario.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $cedula = $_POST['cedula'];
    $fecha = $_POST['fecha'];

    // Sincronización con el esquema: visitante_nombre, visitante_cedula, fecha_programada
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

    header('Location: mis_visitas.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Visita - Módulo Residente</title>
    <!-- Mantenemos el estilo simple por ahora como solicitaste -->
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body style="background: var(--gris-fondo); font-family: 'Inter', sans-serif; padding: 2rem;">

<div style="max-width: 500px; margin: auto; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid var(--gris-borde);">
    <h2 style="color: var(--azul-primario); margin-top: 0;">Registrar Nueva Visita</h2>
    
    <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
        <div>
            <label style="font-size: 0.8rem; color: var(--texto-suave);">Nombre del Visitante</label>
            <input type="text" name="nombre" class="search-input" style="width: 100%;" required>
        </div>
        <div>
            <label style="font-size: 0.8rem; color: var(--texto-suave);">Cédula de Identidad</label>
            <input type="text" name="cedula" class="search-input" style="width: 100%;" required>
        </div>
        <div>
            <label style="font-size: 0.8rem; color: var(--texto-suave);">Fecha y Hora Programada</label>
            <input type="datetime-local" name="fecha" class="search-input" style="width: 100%;" required>
        </div>

        <button type="submit" class="btn-action" style="background: var(--azul-primario); color: white; border: none; margin-top: 1rem;">Guardar Visita</button>
    </form>

    <div style="margin-top: 1.5rem; border-top: 1px solid #eee; pt: 1rem;">
        <a href="../../dashboard_residente.php" style="color: var(--texto-suave); font-size: 0.9rem; text-decoration: none;">← Volver al Panel</a>
    </div>
</div>

</body>
</html>
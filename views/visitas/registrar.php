<?php
require '../../auth/session.php';
verificarRol(['residente']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Visita - Control de Visitas</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body style="background: var(--gris-fondo); font-family: 'Inter', sans-serif; padding: 2rem;">

<div style="max-width: 500px; margin: auto; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid var(--gris-borde);">
    <h2 style="color: var(--azul-primario); margin-top: 0;">📝 Registrar Nueva Visita</h2>
    
    <form action="../../api/visitas/registrar.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
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

    <div style="margin-top: 1.5rem; border-top: 1px solid #eee; padding-top: 1rem;">
        <a href="../dashboard_residente.php" style="color: var(--texto-suave); font-size: 0.9rem; text-decoration: none;">← Volver al Panel</a>
    </div>
</div>

</body>
</html>

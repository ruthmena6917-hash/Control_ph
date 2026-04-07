<?php
require '../auth/session.php';
verificarRol(['residente']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residente - Control de Visitas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --azul-primario: #1E3A5F;
            --azul-claro: #2D6A9F;
            --gris-fondo: #F4F7F9;
        }
        body {
            background-color: var(--gris-fondo);
            font-family: 'Inter', sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .dashboard-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(30, 58, 95, 0.1);
            text-align: center;
            max-width: 450px;
            width: 90%;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .welcome-icon { font-size: 3rem; margin-bottom: 1rem; display: block; }
        h1 { color: var(--azul-primario); font-weight: 700; margin-bottom: 0.5rem; font-size: 1.8rem; }
        p { color: #7F8C8D; margin-bottom: 2.5rem; }
        .actions-grid { display: grid; gap: 1rem; margin-bottom: 2rem; }
        .btn-menu {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 1.2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        .btn-register { background-color: var(--azul-primario); color: white; }
        .btn-register:hover { background-color: var(--azul-claro); transform: translateY(-2px); }
        .btn-list { background-color: white; color: var(--azul-primario); border: 2px solid var(--azul-primario); }
        .btn-list:hover { background-color: #f0f4f8; transform: translateY(-2px); }
        .logout-link { color: #E74C3C; text-decoration: none; font-size: 0.9rem; font-weight: 500; }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <span class="welcome-icon">🏠</span>
        <h1>Hola, <?= htmlspecialchars($_SESSION['nombre']) ?></h1>
        <p>¿Qué gestión deseas realizar hoy?</p>

        <div class="actions-grid">
            <a href="visitas/registrar.php" class="btn-menu btn-register">
                📝 Registrar Visita
            </a>
            <a href="visitas/mis_visitas.php" class="btn-menu btn-list">
                📋 Mis Visitas Programadas
            </a>
        </div>

        <a href="../auth/logout.php" class="logout-link">Cerrar Sesión Segura</a>
    </div>

</body>
</html>

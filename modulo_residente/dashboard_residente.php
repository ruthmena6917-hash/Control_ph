<?php
require '../auth/session.php';
verificarRol(['residente']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Residente - Control de Visitas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --azul-primario: #1E3A5F;
            --azul-claro: #2D6A9F;
            --gris-fondo: #F4F7F9;
            --texto-principal: #2C3E50;
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
        .welcome-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
        h1 {
            color: var(--azul-primario);
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        p {
            color: #7F8C8D;
            margin-bottom: 2.5rem;
        }
        .actions-grid {
            display: grid;
            gap: 1rem;
            margin-bottom: 2rem;
        }
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
        .btn-register {
            background-color: var(--azul-primario);
            color: white;
        }
        .btn-register:hover {
            background-color: var(--azul-claro);
            transform: translateY(-2px);
        }
        .btn-list {
            background-color: white;
            color: var(--azul-primario);
            border: 2px solid var(--azul-primario);
        }
        .btn-list:hover {
            background-color: #f0f4f8;
            transform: translateY(-2px);
        }
        .logout-link {
            color: #E74C3C;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: opacity 0.3s;
        }
        .logout-link:hover {
            opacity: 0.7;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <span class="welcome-icon">🏠</span>
        <!-- Corregido: $_SESSION['nombre'] en lugar de 'usuario' -->
        <h1>Hola, <?= htmlspecialchars($_SESSION['nombre']) ?></h1>
        <p>¿Qué deseas gestionar hoy?</p>

        <div class="actions-grid">
            <a href="vistas/visitas/registrar.php" class="btn-menu btn-register">
                📝 Registrar Visita
            </a>
            <a href="vistas/visitas/mis_visitas.php" class="btn-menu btn-list">
                📋 Ver Mis Visitas
            </a>
        </div>

        <a href="../auth/logout.php" class="logout-link">Cerrar Sesión Segura</a>
    </div>

</body>
</html>
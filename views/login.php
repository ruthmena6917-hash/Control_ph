<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body> 
    <div class="login-container">
        <h2>Control de Visitas</h2>

        <?php if (isset($_GET['error'])): ?>
            <p class="error">Usuario o contraseña incorrectos</p>
        <?php endif; ?>

        <form action="../auth/login.php" method="POST">
            <input type="text"     name="username" placeholder="Usuario"     required>
            <input type="password" name="password" placeholder="Contraseña"  required>
            <button type="submit">Ingresar</button>
        </form>
    </div>
    
</body>
</html>
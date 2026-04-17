<?php
require '../../auth/session.php';
require_once '../../config/database.php';
verificarRol(['gerente']);

$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $cedula = trim($_POST['cedula']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $rol_id = $_POST['rol_id'];
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);

    if (empty($nombre) || empty($cedula) || empty($username) || empty($password)) {
        $mensaje = "Todos los campos son obligatorios";
        $tipo_mensaje = "error";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Insertar en tabla usuarios
            $sqlUser = "INSERT INTO usuarios (rol_id, nombre, cedula, email, telefono) VALUES (:rol_id, :nombre, :cedula, :email, :telefono)";
            $stmtUser = $pdo->prepare($sqlUser);
            $stmtUser->execute([
                ':rol_id' => $rol_id,
                ':nombre' => $nombre,
                ':cedula' => $cedula,
                ':email' => $email,
                ':telefono' => $telefono
            ]);
            $usuario_id = $pdo->lastInsertId();

            // 2. Insertar en tabla autenticacion
            $sqlAuth = "INSERT INTO autenticacion (usuario_id, username, password_hash) VALUES (:usuario_id, :username, :password_hash)";
            $stmtAuth = $pdo->prepare($sqlAuth);
            $stmtAuth->execute([
                ':usuario_id' => $usuario_id,
                ':username' => $username,
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT)
            ]);

            $pdo->commit();
            $mensaje = "Usuario creado exitosamente";
            $tipo_mensaje = "success";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensaje = "Error al crear usuario: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}

// Obtener roles para el select
$roles = $pdo->query("SELECT id, nombre FROM roles WHERE nombre != 'visitante'")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - Control Ph</title>
    <link rel="stylesheet" href="../../assets/css/style.css?v=2">
    <style>
        .form-container {
            max-width: 500px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--azul-primario);
        }
        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--gris-borde);
            border-radius: 8px;
            box-sizing: border-box;
        }
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: var(--azul-primario);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-submit:hover {
            background: var(--azul-claro);
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<header class="main-header">
    <h1 class="header-title">Crear Nuevo Usuario</h1>
    <div class="user-info">
        <a href="../dashboard_gerente.php" class="btn-back">← Volver al Panel</a>
        <span><?= htmlspecialchars($_SESSION['nombre']) ?></span>
        <a href="../../auth/logout.php" class="btn-logout">Salir</a>
    </div>
</header>

<div class="dashboard-container">
    <div class="form-container">
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required placeholder="Ej: Juan Pérez">
            </div>

            <div class="form-group">
                <label for="cedula">Cédula / ID</label>
                <input type="text" name="cedula" id="cedula" class="form-control" required placeholder="8-000-000">
            </div>

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="usuario@email.com">
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="text" name="telefono" id="telefono" class="form-control" placeholder="6000-0000">
            </div>

            <div class="form-group">
                <label for="rol_id">Rol del Usuario</label>
                <select name="rol_id" id="rol_id" class="form-control">
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?php echo $rol['id']; ?>"><?php echo ucfirst($rol['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <hr>

            <div class="form-group">
                <label for="username">Nombre de Usuario (Login)</label>
                <input type="text" name="username" id="username" class="form-control" required placeholder="juan.perez">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" required minlength="6">
            </div>

            <button type="submit" class="btn-submit">Registrar Usuario</button>
        </form>
    </div>
</div>

</body>
</html>

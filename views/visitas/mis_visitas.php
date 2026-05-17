<?php
require '../../auth/session.php';
verificarRol(['residente']);
require '../../config/database.php';

// Detectar si es una petición AJAX para el modal
$isAjax = isset($_GET['ajax']);

// 1. Obtener residente_id
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT id FROM residentes WHERE usuario_id = :usuario_id");
$stmt->execute([':usuario_id' => $usuario_id]);
$residente = $stmt->fetch();

if (!$residente) {
    if ($isAjax) die('<p style="color:red">Error: Perfil no encontrado</p>');
    die("Error: Perfil de residente no encontrado.");
}

// 2. Traer sus visitas
$stmt = $pdo->prepare("SELECT * FROM visitas WHERE residente_id = :id ORDER BY fecha_programada DESC");
$stmt->execute([':id' => $residente['id']]);
$visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si es AJAX, solo devolvemos la tabla (o un fragmento)
if ($isAjax): ?>
    <table class="custom-table">
        <thead>
            <tr>
                <th>Visitante</th>
                <th>Cédula</th>
                <th>Fecha</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($visitas)): ?>
                <tr><td colspan="4" style="text-align: center; padding: 2rem;">No hay registros.</td></tr>
            <?php endif; ?>
            <?php foreach ($visitas as $v): ?>
            <tr>
                <td><?= htmlspecialchars($v['visitante_nombre']) ?></td>
                <td><?= htmlspecialchars($v['visitante_cedula']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($v['fecha_programada'])) ?></td>
                <td>
                    <?php 
                        $badgeStyle = match($v['estado']) {
                            'pendiente' => 'background: #fef3c7; color: #92400e;',
                            'en_edificio' => 'background: #d1fae5; color: #065f46;',
                            'finalizada' => 'background: #f3f4f6; color: #374151;',
                            'cancelada' => 'background: #fee2e2; color: #991b1b;',
                            default => 'background: #eee; color: #666;'
                        };
                    ?>
                    <span style="padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; <?= $badgeStyle ?>">
                        <?= ucfirst($v['estado']) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php exit; endif; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Visitas - Control de Visitas</title>
    <link rel="stylesheet" href="../../assets/css/style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="padding: 2rem; background: var(--gris-fondo);">
    <div class="dashboard-container">
        <h2 class="section-title">Historial Completo de Visitas</h2>
        <a href="../dashboard_residente.php" class="btn-back" style="margin-bottom: 1rem;">← Volver al Panel</a>
        <div class="content-box">
            <!-- Reutilizamos la lógica AJAX aquí si quisiéramos, pero por ahora mostramos lo mismo -->
             <p>Este archivo ahora se carga principalmente via modal en el dashboard principal.</p>
        </div>
    </div>
</body>
</html>

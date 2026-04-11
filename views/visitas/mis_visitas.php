<?php
require '../../auth/session.php';
verificarRol(['residente']);
require '../../config/database.php';

// 1. Obtener residente_id
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT id FROM residentes WHERE usuario_id = :usuario_id");
$stmt->execute([':usuario_id' => $usuario_id]);
$residente = $stmt->fetch();

if (!$residente) {
    die("Error: Perfil de residente no encontrado.");
}

// 2. Traer sus visitas
$stmt = $pdo->prepare("SELECT * FROM visitas WHERE residente_id = :id ORDER BY fecha_programada DESC");
$stmt->execute([':id' => $residente['id']]);
$visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Visitas - Control de Visitas</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/dashboard_residente.css">
</head>
<body>

<header class="main-header">
    <h1 class="header-title">Mis Visitas - Historial Completo</h1>
    <div class="user-info">
        <span><?= htmlspecialchars($_SESSION['nombre']) ?></span>
        <a href="../dashboard_residente.php" class="btn-logout">Volver al Panel</a>
    </div>
</header>

<div class="dashboard-container">

<div class="nav-tabs-custom">
    <div>
        <a href="#" class="tab-item active">Historial Completo</a>
        <a href="../dashboard_residente.php" class="tab-item">Volver al Panel</a>
    </div>
</div>

<div class="content-box">
    <div class="section-title">Todas Mis Visitas</div>
    <table class="custom-table">
        <thead>
            <tr>
                <th>Visitante</th>
                <th>Cédula</th>
                <th>Fecha Programada</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($visitas)): ?>
                <tr><td colspan="5" style="text-align: center; padding: 3rem; color: var(--texto-suave);">Aún no has registrado visitas.</td></tr>
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
                    <span style="padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; <?= $badgeStyle ?>">
                        <?= ucfirst($v['estado']) ?>
                    </span>
                </td>
                <td>
                    <?php if ($v['estado'] === 'pendiente'): ?>
                        <form action="../../api/visitas/cancelar.php" method="POST">
                            <input type="hidden" name="id" value="<?= $v['id'] ?>">
                            <button type="submit" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 0.4rem 0.8rem; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 500;">
                                Cancelar
                            </button>
                        </form>
                    <?php else: ?>
                        <span style="color: #ccc; font-size: 0.8rem;">Sin acciones</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</div>

</body>
</html>

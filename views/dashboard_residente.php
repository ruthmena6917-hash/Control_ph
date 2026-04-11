<?php
require '../auth/session.php';
verificarRol(['residente']);
require '../config/database.php';

// Obtener datos del residente
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT id FROM residentes WHERE usuario_id = :usuario_id");
$stmt->execute([':usuario_id' => $usuario_id]);
$residente = $stmt->fetch();

$visitas_hoy = [];
if ($residente) {
    // Obtener visitas del día del residente
    $hoy = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM visitas 
                           WHERE residente_id = :residente_id 
                           AND DATE(fecha_programada) = :hoy 
                           ORDER BY fecha_programada ASC");
    $stmt->execute([':residente_id' => $residente['id'], ':hoy' => $hoy]);
    $visitas_hoy = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar estadísticas
    $stats = [
        'pendientes' => count(array_filter($visitas_hoy, fn($v) => $v['estado'] == 'pendiente')),
        'en_edificio' => count(array_filter($visitas_hoy, fn($v) => $v['estado'] == 'en_edificio')),
        'finalizadas' => count(array_filter($visitas_hoy, fn($v) => $v['estado'] == 'finalizada')),
        'total' => count($visitas_hoy)
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Residente - Control Ph</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard_residente.css">
</head>
<body>

<header class="main-header">
    <h1 class="header-title">Panel Personal - Residente</h1>
    <div class="user-info">
        <span>Bienvenido, <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></span>
        <a href="../auth/logout.php" class="btn-logout">Cerrar sesión</a>
    </div>
</header>

<div class="dashboard-container">

    <div class="nav-tabs-custom">
        <div>
            <a href="#" class="tab-item active">Mis Visitas de Hoy</a>
            <a href="visitas/registrar.php" class="tab-item">Registrar Visita</a>
            <a href="visitas/mis_visitas.php" class="tab-item">Historial Completo</a>
        </div>
    </div>

    <!-- Sección de estadísticas -->
    <?php if (isset($stats)): ?>
    <div class="stats-section">
        <div class="stat-card">
            <div class="stat-number"><?= $stats['pendientes'] ?></div>
            <div class="stat-label">Pendientes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats['en_edificio'] ?></div>
            <div class="stat-label">En Edificio</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats['finalizadas'] ?></div>
            <div class="stat-label">Finalizadas</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats['total'] ?></div>
            <div class="stat-label">Total Hoy</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de visitas del día -->
    <div class="content-box">
        <div class="section-title">Visitas Programadas para Hoy</div>
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Visitante</th>
                    <th>Cédula</th>
                    <th>Hora Programada</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($visitas_hoy)): ?>
                    <tr>
                        <td colspan="5" class="no-visitas">No tienes visitas programadas para hoy.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($visitas_hoy as $visita): ?>
                    <tr>
                        <td class="visitante-nombre"><?= htmlspecialchars($visita['visitante_nombre']) ?></td>
                        <td><?= htmlspecialchars($visita['visitante_cedula']) ?></td>
                        <td><?= date('H:i', strtotime($visita['fecha_programada'])) ?></td>
                        <td>
                            <?php 
                            $badgeClass = '';
                            $estadoLabel = '';
                            switch($visita['estado']) {
                                case 'pendiente': $badgeClass = 'badge-pendiente'; $estadoLabel = 'Pendiente'; break;
                                case 'en_edificio': $badgeClass = 'badge-edificio'; $estadoLabel = 'En edificio'; break;
                                case 'finalizada': $badgeClass = 'badge-finalizada'; $estadoLabel = 'Finalizada'; break;
                                default: $badgeClass = 'badge-finalizada'; $estadoLabel = $visita['estado'];
                            }
                            ?>
                            <span class="badge-status <?= $badgeClass ?>"><?= $estadoLabel ?></span>
                        </td>
                        <td>
                            <?php if ($visita['estado'] == 'pendiente'): ?>
                                <a href="visitas/cancelar.php?id=<?= $visita['id'] ?>" class="btn-action" style="background: #dc3545;" onclick="return confirm('¿Cancelar esta visita?')">Cancelar</a>
                            <?php else: ?>
                                <span style="color: var(--texto-suave); font-size: 0.9rem;">Sin acciones</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>

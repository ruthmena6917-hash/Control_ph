<?php
require '../auth/session.php';
verificarRol(['seguridad']);
require '../config/database.php';

// Obtener todas las visitas de hoy
$hoy = date('Y-m-d');
$sql = "SELECT * FROM visitas WHERE DATE(fecha_programada) = :hoy ORDER BY fecha_programada ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':hoy' => $hoy]);
$visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contadores para las tarjetas
$pendientes = count(array_filter($visitas, fn($v) => $v['estado'] == 'pendiente'));
$en_edificio = count(array_filter($visitas, fn($v) => $v['estado'] == 'en_edificio'));
$finalizadas = count(array_filter($visitas, fn($v) => $v['estado'] == 'finalizada'));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Seguridad - Control de Visitas</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard_seguridad.css">
</head>
<body>
    <header class="main-header">
        <h1 class="header-title">Control de Visitas de Hoy</h1>
        <div class="user-info">
            <span><?= $_SESSION['nombre'] ?> — <?= ucfirst($_SESSION['rol']) ?></span>
            <a href="../auth/logout.php" class="btn-logout">Salir</a>
        </div>
    </header>

    <main class="dashboard-container">
        <!-- Navegación Simplificada -->
        <div class="nav-tabs-custom">
            <a href="#" class="tab-item active">Seguridad (Panel General)</a>
        </div>

        <!-- Tarjetas de Resumen -->
        <div class="summary-cards">
            <div class="card-stat">
                <span class="label">Pendientes</span>
                <span class="value pendiente"><?= $pendientes ?></span>
            </div>
            <div class="card-stat">
                <span class="label">En edificio</span>
                <span class="value edificio"><?= $en_edificio ?></span>
            </div>
            <div class="card-stat">
                <span class="label">Finalizadas</span>
                <span class="value finalizada"><?= $finalizadas ?></span>
            </div>
        </div>

        <!-- Tabla de Visitas del Día -->
        <div class="search-wrapper">
            <input type="text" id="buscador" class="search-input" placeholder="Buscar por nombre o cédula...">
            <input type="date" id="filtro-fecha" class="search-input" value="<?= $hoy ?>">
        </div>
        <div class="content-box">
            <div class="box-header">Visitas Programadas para Hoy</div>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Visitante</th>
                        <th>Cédula</th>
                        <th>Hora Prog.</th>
                        <th>Estado</th>
                        <th>Coche (Placa)</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($visitas)): ?>
                        <tr><td colspan="6" class="no-visitas">No hay visitas programadas para hoy.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($visitas as $v): ?>
                    <tr>
                        <td class="visitante-nombre"><?= $v['visitante_nombre'] ?></td>
                        <td><?= $v['visitante_cedula'] ?></td>
                        <td><?= date('H:i', strtotime($v['fecha_programada'])) ?></td>
                        <td>
                            <?php 
                            $badgeClass = '';
                            $estadoLabel = '';
                            switch($v['estado']) {
                                case 'pendiente': $badgeClass = 'badge-pendiente'; $estadoLabel = 'Pendiente'; break;
                                case 'en_edificio': $badgeClass = 'badge-edificio'; $estadoLabel = 'En edificio'; break;
                                case 'finalizada': $badgeClass = 'badge-finalizada'; $estadoLabel = 'Finalizada'; break;
                                default: $badgeClass = 'badge-finalizada'; $estadoLabel = $v['estado'];
                            }
                            ?>
                            <span class="badge-status <?= $badgeClass ?>"><?= $estadoLabel ?></span>
                        </td>
                        <td><?= $v['placa_vehiculo'] ?? '<span class="placa-na">N/A</span>' ?></td>
                        <td>
                            <?php if ($v['estado'] == 'pendiente'): ?>
                                <button class="btn-action" onclick="marcarEntrada(<?= $v['id'] ?>)">Marcar Entrada</button>
                                <a href="visitas/lista.php?id=<?= $v['id'] ?>" class="btn-action">Ver Detalles</a>
                            <?php elseif ($v['estado'] == 'en_edificio'): ?>
                                <button class="btn-action" onclick="marcarSalida(<?= $v['id'] ?>)">Marcar Salida</button>
                                <a href="visitas/lista.php?id=<?= $v['id'] ?>" class="btn-action">Ver Detalles</a>
                            <?php else: ?>
                                <a href="visitas/lista.php?id=<?= $v['id'] ?>" class="btn-action">Ver Detalles</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
<script src="../assets/js/dashboard_seguridad.js"></script>
</body>
</html>
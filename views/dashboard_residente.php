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
            <a href="#" class="tab-item active" onclick="location.reload()">Mis Visitas de Hoy</a>
            <button class="tab-item" onclick="abrirModal('modalRegistrar')">➕ Registrar Visita</button>
            <button class="tab-item" onclick="abrirModal('modalHistorial')">📜 Historial Completo</button>
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
                                <button class="btn-action" style="background: #dc3545; color: white;" onclick="cancelarVisita(<?= $visita['id'] ?>)">Cancelar</button>
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

<!-- Modal Registrar Visita -->
<div id="modalRegistrar" class="modal">
    <div class="modal-content scrollable-modal">
        <div class="modal-header">📝 Registrar Nueva Visita</div>
        <form id="formRegistrar">
            <div class="form-group">
                <label>Nombre del Visitante</label>
                <input type="text" name="nombre" class="search-input" placeholder="Ej: Juan Pérez" required>
            </div>
            <div class="form-group">
                <label>Cédula de Identidad</label>
                <input type="text" name="cedula" class="search-input" placeholder="8-000-000" required>
            </div>
            <div class="form-group">
                <label>Fecha y Hora Programada</label>
                <input type="datetime-local" name="fecha" class="search-input" value="<?= date('Y-m-d\TH:i') ?>" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModal('modalRegistrar')">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Visita</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Historial (Placeholder) -->
<div id="modalHistorial" class="modal">
    <div class="modal-content scrollable-modal" style="max-width: 800px;">
        <div class="modal-header">📜 Historial de Visitas</div>
        <div id="historial-loading" style="padding: 2rem; text-align: center;">Cargando historial...</div>
        <div id="historial-content"></div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="cerrarModal('modalHistorial')">Cerrar</button>
        </div>
    </div>
</div>

<!-- Área de Notificaciones (Alertas) -->
<div id="notification-toast" class="toast-alert" style="display: none;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div id="notif-content"></div>
        <button onclick="document.getElementById('notification-toast').style.display='none'">&times;</button>
    </div>
</div>

<style>
    /* Estilos globales para modales scrollables */
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); }
    .modal-content { background: white; margin: 2% auto; padding: 2rem; border-radius: 16px; width: 95%; max-width: 500px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    .scrollable-modal { max-height: 90vh; overflow-y: auto; }
    .modal-header { font-size: 1.4rem; font-weight: 700; color: var(--azul-primario); margin-bottom: 1.5rem; border-bottom: 2px solid #f0f0f0; padding-bottom: 0.5rem; }
    .modal-footer { margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px; }
    .btn-cancel { background: #eee; border: none; padding: 0.8rem 1.5rem; border-radius: 8px; cursor: pointer; font-weight: 600; }
    .btn-save { background: var(--azul-primario); color: white; border: none; padding: 0.8rem 1.5rem; border-radius: 8px; cursor: pointer; font-weight: 600; }
    
    .toast-alert { position: fixed; top: 20px; right: 20px; background: white; border-left: 5px solid #28a745; box-shadow: 0 5px 15px rgba(0,0,0,0.2); padding: 1rem; border-radius: 8px; z-index: 2000; min-width: 300px; animation: slideIn 0.3s ease; }
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>

<script>
    function abrirModal(id) {
        document.getElementById(id).style.display = 'block';
        if (id === 'modalHistorial') cargarHistorial();
    }

    function cerrarModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    // AJAX Registro
    document.getElementById('formRegistrar').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('../api/visitas/registrar.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('✅ Visita registrada correctamente');
                location.reload();
            } else {
                alert('❌ Error: ' + data.error);
            }
        });
    });

    function cancelarVisita(id) {
        if (!confirm('¿Seguro que deseas cancelar esta visita?')) return;
        fetch('../api/visitas/actualizar_estado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}&estado=cancelada`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert('Error al cancelar');
        });
    }

    function cargarHistorial() {
        // En un escenario real esto cargaría via AJAX. Por ahora redirección simplificada o carga dinámica.
        fetch('visitas/mis_visitas.php?ajax=1')
            .then(r => r.text())
            .then(html => {
                document.getElementById('historial-loading').style.display = 'none';
                document.getElementById('historial-content').innerHTML = html;
            });
    }

    function checkNotifications() {
        fetch('../api/usuarios/get_notificaciones.php')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    const toast = document.getElementById('notification-toast');
                    const content = document.getElementById('notif-content');
                    data.data.forEach(notif => {
                        content.innerText = notif.mensaje;
                        toast.style.display = 'block';
                        setTimeout(() => location.reload(), 3000);
                    });
                }
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        checkNotifications();
        setInterval(checkNotifications, 10000);
    });
</script>
</body>
</html>

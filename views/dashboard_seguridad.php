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
                            <?php elseif ($v['estado'] == 'en_edificio'): ?>
                                <button class="btn-action" onclick="marcarSalida(<?= $v['id'] ?>)">Marcar Salida</button>
                            <?php endif; ?>
                            <button class="btn-action" style="background:#6c757d; color:white;" onclick="verDetalles(<?= $v['id'] ?>)">Ver Detalles</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- MODAL: MARCAR ENTRADA (CÁMARA) -->
    <div id="modal-entrada" class="modal">
        <div class="modal-content scrollable-modal">
            <div class="modal-header">Gestión de Entrada de Visitante</div>
            <div class="modal-body">
                <div class="photo-section">
                    <video id="webcam" autoplay playsinline width="100%" height="auto" style="border-radius: 12px; background: #000; box-shadow: 0 4px 10px rgba(0,0,0,0.3);"></video>
                    <canvas id="photo-canvas" style="display: none;"></canvas>
                    <div id="photo-preview-container" style="display: none;">
                        <img id="photo-preview" style="width: 100%; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                    </div>
                    <button type="button" class="btn-action" id="btn-foto" onclick="capturarFoto()" style="margin-top: 15px; width: 100%; background: #28a745; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600;">📸 Capturar Foto</button>
                    <button type="button" class="btn-action" id="btn-reintentar" onclick="reintentarFoto()" style="display: none; margin-top: 15px; width: 100%; background: #6c757d; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600;">🔄 Reintentar</button>
                </div>
                <div class="form-group" style="margin-top: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--azul-primario);">Asignar Tag IoT (Opcional)</label>
                    <input type="text" id="tag_iot" class="search-input" placeholder="Escanee o ingrese ID de Tag" style="width: 100%; border-radius: 8px;">
                </div>
                <div class="legal-section" style="margin-top: 20px; background: #f8f9fa; padding: 12px; border-radius: 10px; border: 1px solid #e9ecef;">
                    <p style="font-size: 0.7rem; color: #666; margin: 0; line-height: 1.4;">El visitante acepta la captura de su fotografía y datos biométricos para fines exclusivamente de seguridad interna.</p>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="visit-id-modal">
                <button type="button" class="btn-cancel" onclick="cerrarModal('modal-entrada')">Cancelar</button>
                <button type="button" class="btn-save" id="btn-confirmar-entrada" onclick="confirmarEntrada()">✅ Confirmar Entrada</button>
            </div>
        </div>
    </div>

    <!-- MODAL: VER DETALLES -->
    <div id="modal-detalles" class="modal">
        <div class="modal-content scrollable-modal" style="max-width: 550px;">
            <div class="modal-header">📋 Ficha de Visitante</div>
            <div id="detalles-loading" style="padding: 2rem; text-align: center; color: var(--texto-suave);">Obteniendo información...</div>
            <div id="detalles-body" style="display: none;">
                <div style="display: grid; grid-template-columns: 140px 1fr; gap: 20px; align-items: start;">
                    <div id="detalles-foto-container">
                        <img id="detalles-foto" src="" style="width: 140px; height: 140px; border-radius: 12px; object-fit: cover; border: 3px solid #f0f0f0;">
                    </div>
                    <div>
                        <h2 id="detalles-nombre" style="margin: 0; font-size: 1.3rem; color: var(--azul-primario);"></h2>
                        <p id="detalles-cedula" style="margin: 5px 0; color: var(--texto-suave); font-weight: 500;"></p>
                        <span id="detalles-estado" class="badge-status"></span>
                    </div>
                </div>
                
                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #f0f0f0; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="font-size: 0.75rem; color: #999; text-transform: uppercase;">Residente Host</label>
                        <p id="detalles-residente" style="margin: 0; font-weight: 600;"></p>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: #999; text-transform: uppercase;">Ubicación</label>
                        <p id="detalles-ubicacion" style="margin: 0; font-weight: 600;"></p>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: #999; text-transform: uppercase;">Entrada Real</label>
                        <p id="detalles-entrada" style="margin: 0; font-weight: 600;"></p>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: #999; text-transform: uppercase;">Salida Real</label>
                        <p id="detalles-salida" style="margin: 0; font-weight: 600;"></p>
                    </div>
                </div>
                
                <div style="margin-top: 15px; padding: 12px; background: #fff8e1; border-radius: 8px; border: 1px solid #ffe082;">
                    <label style="font-size: 0.75rem; color: #795548; font-weight: 700;">NOTAS DEL SISTEMA / IOT</label>
                    <p id="detalles-notas" style="margin: 5px 0 0; font-size: 0.85rem; color: #5d4037; white-space: pre-wrap;"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModal('modal-detalles')">Cerrar</button>
            </div>
        </div>
    </div>

    <style>
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(5px); }
        .modal-content { background: white; margin: 3% auto; padding: 2rem; border-radius: 20px; width: 95%; max-width: 500px; box-shadow: 0 15px 40px rgba(0,0,0,0.4); }
        .scrollable-modal { max-height: 92vh; overflow-y: auto; }
        .modal-header { font-size: 1.5rem; font-weight: 700; color: var(--azul-primario); margin-bottom: 1.5rem; border-bottom: 2px solid #f8f9fa; padding-bottom: 0.7rem; }
        .modal-footer { margin-top: 25px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #f8f9fa; padding-top: 15px; }
        .btn-cancel { background: #f1f3f5; border: none; padding: 0.8rem 1.6rem; border-radius: 10px; cursor: pointer; font-weight: 700; color: #495057; }
        .btn-save { background: var(--azul-primario); color: white; border: none; padding: 0.8rem 1.6rem; border-radius: 10px; cursor: pointer; font-weight: 700; }
    </style>

<script src="../assets/js/dashboard_seguridad.js?v=<?php echo time(); ?>"></script>
</body>
</html>
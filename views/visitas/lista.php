<?php
require '../../auth/session.php';
verificarRol(['seguridad']);
require '../../config/database.php';

// Si se recibe un ID, mostramos el detalle de una visita específica para VALIDACIÓN
$visita_id = $_GET['id'] ?? null;
$visita = null;

if ($visita_id) {
    $stmt = $pdo->prepare("SELECT * FROM visitas WHERE id = :id");
    $stmt->execute([':id' => $visita_id]);
    $visita = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Lógica de búsqueda (solo si no hay un ID específico seleccionado)
$busqueda = $_GET['busqueda'] ?? '';
$fecha = $_GET['fecha'] ?? date('Y-m-d');

if (!$visita) {
    $sql = "SELECT * FROM visitas 
            WHERE DATE(fecha_programada) = :fecha
            AND (visitante_nombre LIKE :busqueda OR visitante_cedula LIKE :busqueda)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':fecha' => $fecha, ':busqueda' => "%$busqueda%"]);
    $visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Visita - Control de Visitas</title>
    <!-- Bootstrap 5 CSS para estructura base -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css?v=2">
</head>
<body>
    <header class="main-header">
        <h1 class="header-title">Detalle y Validación</h1>
        <div class="user-info">
            <a href="../dashboard_seguridad.php" class="btn-back">← Volver al Panel</a>
            <span><?= $_SESSION['nombre'] ?></span>
            <a href="../../auth/logout.php" class="btn-logout">Salir</a>
        </div>
    </header>

    <main class="dashboard-container">
        <div class="nav-tabs-custom">
            <div>
                <a href="#" class="tab-item active">Ficha de Validación</a>
            </div>
        </div>

        <?php if ($visita): ?>
            <!-- VISTA DE DETALLE (FICHA) -->
            <div class="content-box">
                <div class="box-header">Información del Visitante: <?= $visita['visitante_nombre'] ?></div>
                <div class="p-4">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="text-secondary small d-block">Documento de Identidad (Cédula)</label>
                            <p class="fs-5 fw-medium"><?= $visita['visitante_cedula'] ?></p>
                            
                            <label class="text-secondary small d-block">Estado de la Visita</label>
                            <?php 
                                $badgeClass = match($visita['estado']) {
                                    'pendiente' => 'badge-pendiente',
                                    'en_edificio' => 'badge-edificio',
                                    'finalizada' => 'badge-finalizada',
                                    default => 'badge-finalizada'
                                };
                            ?>
                            <span class="badge-status <?= $badgeClass ?>"><?= ucfirst($visita['estado']) ?></span>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="text-secondary small d-block">Vehículo Registrado</label>
                            <p class="fs-5 fw-medium">
                                <?= $visita['placa_vehiculo'] ?: '<span class="text-muted italic">Sin vehículo registrado</span>' ?>
                            </p>

                            <label class="text-secondary small d-block">Registro de Tiempos</label>
                            <ul class="list-unstyled small mt-2">
                                <li>📅 Prog: <?= date('d/m H:i', strtotime($visita['fecha_programada'])) ?></li>
                                <li>🚪 Ent: <?= $visita['fecha_entrada_real'] ? date('H:i', strtotime($visita['fecha_entrada_real'])) : '--:--' ?></li>
                                <li>🏁 Sal: <?= $visita['fecha_salida'] ? date('H:i', strtotime($visita['fecha_salida'])) : '--:--' ?></li>
                            </ul>
                            
                            <?php if ($visita['estado'] == 'en_edificio'): ?>
                                <p class="text-success small fw-bold mt-2">📍 Actualmente en el edificio</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="border-top pt-4 mt-2">
                        <?php if ($visita['estado'] == 'pendiente'): ?>
                            <form action="../../api/visitas/actualizar_estado.php" method="POST">
                                <input type="hidden" name="estado" value="en_edificio">
                                <input type="hidden" name="id" value="<?= $visita['id'] ?>">
                                <div class="mb-3">
                                    <label class="form-label small">Confirmar Placa de Vehículo (opcional)</label>
                                    <input type="text" name="placa" class="search-input" placeholder="Ej: ABC-123" value="<?= $visita['placa_vehiculo'] ?>">
                                </div>
                                <button type="submit" class="btn-action w-100" style="background: var(--en-edificio); color: white; border: none; padding: 1rem; font-weight: 600;">
                                    ✅ VALIDAR Y MARCAR ENTRADA REAL
                                </button>
                            </form>
                        <?php elseif ($visita['estado'] == 'en_edificio'): ?>
                            <form action="../../api/visitas/actualizar_estado.php" method="POST">
                                <input type="hidden" name="estado" value="finalizada">
                                <input type="hidden" name="id" value="<?= $visita['id'] ?>">
                                <button type="submit" class="btn-action w-100" style="background: var(--azul-primario); color: white; border: none; padding: 1rem; font-weight: 600;">
                                    🏁 MARCAR SALIDA DEL EDIFICIO
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-light w-100 border text-center">
                                Esta visita ya ha sido finalizada y no requiere más acciones.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- VISTA DE LISTA COMPLETA (POR SI SE ACCEDE DIRECTAMENTE) -->
            <div class="content-box">
                <div class="box-header">Visitas del Día (Búsqueda General)</div>
                <div class="search-wrapper">
                    <form method="GET" class="d-flex gap-2">
                        <input type="text" name="busqueda" class="search-input" placeholder="Filtrar por nombre..." value="<?= $busqueda ?>">
                        <button type="submit" class="btn-action">Filtrar</button>
                    </form>
                </div>
                <table class="custom-table">
                    <thead><tr><th>Visitante</th><th>Hora Prog.</th><th>Estado</th><th>Acción</th></tr></thead>
                    <tbody>
                        <?php foreach (($visitas ?? []) as $v): ?>
                        <tr>
                            <td><?= $v['visitante_nombre'] ?></td>
                            <td><?= date('H:i', strtotime($v['fecha_programada'])) ?></td>
                            <td><span class="badge-status"><?= $v['estado'] ?></span></td>
                            <td><a href="?id=<?= $v['id'] ?>" class="btn-action">Ver Detalles / Validar</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

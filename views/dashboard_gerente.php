<?php
require_once '../auth/session.php';
verificarRol(['gerente']);
require_once '../config/database.php';

$servicios_opciones = $pdo->query("SELECT nombre FROM servicios")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Gerente - Control Ph</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .btn-service { background: var(--azul-primario); color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 8px; cursor: pointer; font-weight: 500; transition: background 0.3s; }
        .btn-service:hover { background: var(--azul-claro); }
        .btn-delete { color: #dc3545; cursor: pointer; border: none; background: none; font-size: 1.1rem; padding: 5px; }
        .btn-edit { color: var(--azul-primario); cursor: pointer; border: none; background: none; font-size: 1.1rem; padding: 5px; }
        .section-title { margin-top: 2.5rem; margin-bottom: 1rem; color: var(--azul-primario); font-weight: 700; font-size: 1.25rem; display: flex; align-items: center; gap: 10px; }
        .section-title::before { content: ""; display: inline-block; width: 4px; height: 20px; background: var(--azul-primario); border-radius: 4px; }

        /* Modales */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .modal-content { background: white; margin: 5% auto; padding: 2rem; border-radius: 12px; width: 90%; max-width: 500px; }
        .modal-header { margin-bottom: 1.5rem; color: var(--azul-primario); font-weight: 700; font-size: 1.2rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-size: 0.85rem; color: var(--texto-suave); }
        .modal-footer { margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 1rem; }
        .btn-cancel { background: #eee; border: none; padding: 0.8rem 1.2rem; border-radius: 8px; cursor: pointer; }
        .btn-save { background: var(--azul-primario); color: white; border: none; padding: 0.8rem 1.2rem; border-radius: 8px; cursor: pointer; }

        /* Secciones */
        .dashboard-section { display: none; }
        .dashboard-section.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<header class="main-header">
    <h1 class="header-title">Panel Administrativo - Gerencia</h1>
    <div class="user-info">
        <span>Bienvenido, <strong><?php echo $_SESSION['nombre']; ?></strong></span>
        <a href="../auth/logout.php" class="btn-logout">Cerrar sesión</a>
    </div>
</header>

<div class="dashboard-container">

    <div class="nav-tabs-custom">
        <div>
            <a href="#" class="tab-item active" onclick="mostrarSeccion('visitas', this)">Visitas</a>
            <a href="#" class="tab-item" onclick="mostrarSeccion('servicios', this)">Servicios</a>
            <a href="#" class="tab-item" onclick="mostrarSeccion('residentes', this)">Residentes</a>
            <a href="#" class="tab-item" onclick="mostrarSeccion('empleados', this)">Empleados</a>
            <a href="usuarios/crear.php" class="tab-item">Crear Usuario</a>
        </div>
        <button class="btn-service" id="btn-accion" onclick="abrirModalRegistro()">➕ Crear Servicio Adicional</button>
    </div>

    <div class="search-wrapper">
        <input type="text" id="buscador" class="search-input" placeholder="Buscar registros..." onkeyup="filtrar()">
    </div>

    <!-- SECCIÓN 1: VISITAS -->
    <div id="seccion-visitas" class="dashboard-section active">
        <div class="section-title">Visitas del Edificio</div>
        <div class="content-box">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Residente Responsable</th>
                        <th>Visitante / Cédula</th>
                        <th>Apartamento</th>
                        <th>Entrada / Salida</th>
                        <th style="width: 80px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla_visitas"></tbody>
            </table>
        </div>
    </div>

    <!-- SECCIÓN 2: SERVICIOS -->
    <div id="seccion-servicios" class="dashboard-section">
        <div class="section-title">Otros Servicios Registrados</div>
        <div class="content-box">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Personal de Servicio</th>
                        <th>Cédula</th>
                        <th>Tipo de Servicio</th>
                        <th>Fecha / Hora</th>
                        <th style="width: 100px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla_servicios"></tbody>
            </table>
        </div>
    </div>

    <!-- SECCIÓN 3: RESIDENTES -->
    <div id="seccion-residentes" class="dashboard-section">
        <div class="section-title">Listado de Residentes</div>
        <div class="content-box">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Residente</th>
                        <th>Cédula</th>
                        <th>Ubicación</th>
                        <th>Teléfono</th>
                        <th>Fecha Ingreso</th>
                    </tr>
                </thead>
                <tbody id="tabla-residentes"></tbody>
            </table>
        </div>
    </div>

    <!-- SECCIÓN 4: EMPLEADOS -->
    <div id="seccion-empleados" class="dashboard-section">
        <div class="section-title">Personal y Turnos</div>
        <div class="content-box">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Cargo</th>
                        <th>Contacto</th>
                        <th>Estado Hoy</th>
                        <th>Turno Actual</th>
                    </tr>
                </thead>
                <tbody id="tabla-empleados"></tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Registro Servicio -->
<div id="modalRegistro" class="modal">
    <div class="modal-content">
        <div class="modal-header">Registrar Nuevo Servicio Adicional</div>
        <form id="formRegistro">
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" name="nombre" class="search-input" required>
                </div>
                <div class="form-group">
                    <label>Apellido:</label>
                    <input type="text" name="apellido" class="search-input" required>
                </div>
            </div>
            <div class="form-group">
                <label>Cédula / ID:</label>
                <input type="text" name="cedula" class="search-input" required>
            </div>
            <div class="form-group">
                <label>Tipo de Servicio:</label>
                <input type="text" name="tipo" class="search-input" placeholder="Ej: Jardinería, Ascensores..." required>
            </div>
            <div class="form-group">
                <label>Fecha y Hora:</label>
                <input type="datetime-local" name="fecha_programada" class="search-input" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModals()">Cancelar</button>
                <button type="submit" class="btn-save">Registrar Permanente</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edición Servicio -->
<div id="modalEdicion" class="modal">
    <div class="modal-content">
        <div class="modal-header">Editar Datos de Servicio</div>
        <form id="formEdicion">
            <input type="hidden" id="edit_id" name="id">
            <div class="form-group">
                <label>Nombre Completo:</label>
                <input type="text" id="edit_nombre" name="nombre" class="search-input" required>
            </div>
            <div class="form-group">
                <label>Cédula / ID:</label>
                <input type="text" id="edit_cedula" name="cedula" class="search-input" required>
            </div>
            <div class="form-group">
                <label>Nueva Fecha y Hora:</label>
                <input type="datetime-local" id="edit_fecha" name="fecha_programada" class="search-input" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModals()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/dashboard_gerente.js"></script>
</body>
</html>
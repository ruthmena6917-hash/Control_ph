<?php
require_once '../auth/session.php';
verificarRol(['gerente']);
require_once '../config/database.php';

// Obtener tipos de servicios de la base de datos
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
    <div class="nav-tabs-custom" style="justify-content: space-between; align-items: center;">
        <div>
            <a href="dashboard_gerente.php" class="tab-item active">Visitas Recientes</a>
            <a href="usuarios/crear.php" class="tab-item">Crear Usuario</a>
        </div>
        <button class="btn-service" onclick="abrirModalRegistro()">➕ Crear Servicio Adicional</button>
    </div>

    <div class="search-wrapper">
        <input type="text" id="buscador" class="search-input" placeholder="Buscar registros..." onkeyup="filtrar()">
    </div>

    <!-- SECCIÓN 1: VISITAS -->
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

    <!-- SECCIÓN 2: OTROS SERVICIOS -->
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

<script>
let visitasData = [];

function cargarVisitas() {
    fetch('../api/visitas/listado_gerente.php')
    .then(res => res.json())
    .then(data => {
        visitasData = data;
        renderizarTablas(data);
    })
    .catch(err => console.error("Error:", err));
}

function renderizarTablas(data) {
    let htmlV = "";
    let htmlS = "";
    
    data.forEach(v => {
        const notasRaw = v.notas || '';
        const esServicio = notasRaw.startsWith('[SERVICIO]');
        
        if (!esServicio) {
            htmlV += `
            <tr>
                <td><strong>${v.residente_nombre}</strong></td>
                <td><strong>${v.visitante_nombre}</strong><br><small>${v.visitante_cedula}</small></td>
                <td>Apt. ${v.apartamento}</td>
                <td>E: ${v.fecha_entrada_real || '--'}<br>S: ${v.fecha_salida || '--'}</td>
                <td><button class="btn-delete" onclick="eliminarRegistro(${v.id})">🗑️</button></td>
            </tr>`;
        } else {
            const nombreServicio = notasRaw.replace('[SERVICIO] ', '');
            htmlS += `
            <tr>
                <td><strong>${v.visitante_nombre}</strong></td>
                <td>${v.visitante_cedula}</td>
                <td><span class="badge-status badge-edificio" style="background: var(--azul-primario); color: white;">${nombreServicio}</span></td>
                <td>${v.fecha_programada}</td>
                <td>
                    <button class="btn-edit" onclick="abrirModalEdicion(${v.id}, '${v.visitante_nombre}', '${v.visitante_cedula}', '${v.fecha_programada}')">✏️</button>
                    <button class="btn-delete" onclick="eliminarRegistro(${v.id})">🗑️</button>
                </td>
            </tr>`;
        }
    });

    document.getElementById("tabla_visitas").innerHTML = htmlV || "<tr><td colspan='5' style='text-align:center;'>No hay visitas en este momento</td></tr>";
    document.getElementById("tabla_servicios").innerHTML = htmlS || "<tr><td colspan='5' style='text-align:center;'>No hay servicios registrados en este momento</td></tr>";
}

function filtrar() {
    const q = document.getElementById("buscador").value.toLowerCase();
    const filtrados = visitasData.filter(v => 
        v.visitante_nombre.toLowerCase().includes(q) || v.residente_nombre.toLowerCase().includes(q) || v.visitante_cedula.includes(q)
    );
    renderizarTablas(filtrados);
}

function eliminarRegistro(id) {
    if (confirm("¿Eliminar permanentemente?")) {
        const fd = new FormData(); fd.append('id', id);
        fetch('../api/visitas/eliminar.php', { method: 'POST', body: fd })
        .then(res => res.json()).then(res => { if(res.success) cargarVisitas(); else alert(res.error); });
    }
}

function abrirModalRegistro() { document.getElementById('modalRegistro').style.display = 'block'; }
function cerrarModals() { 
    document.getElementById('modalRegistro').style.display = 'none'; 
    document.getElementById('modalEdicion').style.display = 'none'; 
}

function abrirModalEdicion(id, nombre, cedula, fecha) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_cedula').value = cedula;
    
    let f = new Date(fecha);
    let offset = f.getTimezoneOffset() * 60000;
    let localISO = (new Date(f.getTime() - offset)).toISOString().slice(0, 16);
    document.getElementById('edit_fecha').value = localISO;
    
    document.getElementById('modalEdicion').style.display = 'block';
}

document.getElementById('formRegistro').onsubmit = function(e) {
    e.preventDefault();
    fetch('../api/visitas/registrar_servicio.php', { method: 'POST', body: new FormData(this) })
    .then(res => res.json()).then(res => { if(res.success) { cerrarModals(); cargarVisitas(); } else alert(res.error); });
};

document.getElementById('formEdicion').onsubmit = function(e) {
    e.preventDefault();
    fetch('../api/visitas/actualizar_servicio.php', { method: 'POST', body: new FormData(this) })
    .then(res => res.json()).then(res => { if(res.success) { cerrarModals(); cargarVisitas(); } else alert(res.error); });
};

window.onclick = function(e) { if (e.target.className === 'modal') cerrarModals(); }
setInterval(cargarVisitas, 15000);
window.onload = cargarVisitas;
</script>
</body>
</html>

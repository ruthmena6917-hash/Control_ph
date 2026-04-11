// =====================
// CARGAR RESIDENTES
// =====================
function cargarResidentes() {
    fetch('../api/residentes/listar.php')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.length === 0) {
                html = '<tr><td colspan="5" style="text-align:center;">No hay residentes registrados</td></tr>';
            } else {
                data.forEach(r => {
                    html += `
                        <tr>
                            <td><strong>${r.nombre}</strong></td>
                            <td>${r.cedula}</td>
                            <td>${r.apartamento} — Piso ${r.piso}</td>
                            <td>${r.telefono ?? '—'}</td>
                            <td>${r.fecha_ingreso}</td>
                        </tr>
                    `;
                });
            }
            document.getElementById('tabla-residentes').innerHTML = html;
        })
        .catch(error => console.log('Error cargando residentes:', error));
}

// =====================
// CARGAR EMPLEADOS
// =====================
function cargarEmpleados() {
    fetch('../api/empleados/listar.php')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.length === 0) {
                html = '<tr><td colspan="5" style="text-align:center;">No hay empleados registrados</td></tr>';
            } else {
                data.forEach(e => {
                    const status = e.turno_activo ? '<span class="badge-status badge-edificio" style="background:#28a745;color:white;">En Turno</span>' : '<span class="badge-status" style="background:#6c757d;color:white;">Fuera</span>';
                    html += `
                        <tr>
                            <td><strong>${e.nombre}</strong></td>
                            <td>${e.cargo}</td>
                            <td>${e.telefono ?? '—'}<br><small>${e.email ?? ''}</small></td>
                            <td>${status}</td>
                            <td>${e.turno_fecha ? e.hora_inicio + ' - ' + e.hora_fin : 'No programado'}</td>
                        </tr>
                    `;
                });
            }
            document.getElementById('tabla-empleados').innerHTML = html;
        })
        .catch(error => console.log('Error cargando empleados:', error));
}

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
    fetch('../api/servicios/crear.php', { method: 'POST', body: new FormData(this) })
    .then(res => res.json()).then(res => { if(res.success) { cerrarModals(); cargarVisitas(); } else alert(res.error); });
};

document.getElementById('formEdicion').onsubmit = function(e) {
    e.preventDefault();
    fetch('../api/servicios/actualizar.php', { method: 'POST', body: new FormData(this) })
    .then(res => res.json()).then(res => { if(res.success) { cerrarModals(); cargarVisitas(); } else alert(res.error); });
};

window.onclick = function(e) { if (e.target.className === 'modal') cerrarModals(); }
setInterval(cargarVisitas, 15000);
function mostrarSeccion(seccion, el) {
    // Ocultar todas las secciones
    document.getElementById('seccion-visitas').style.display    = 'none';
    document.getElementById('seccion-servicios').style.display  = 'none';
    document.getElementById('seccion-residentes').style.display = 'none';
    document.getElementById('seccion-empleados').style.display  = 'none';

    // Mostrar la sección seleccionada
    document.getElementById('seccion-' + seccion).style.display = 'block';

    // Actualizar pestaña activa
    document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
    el.classList.add('active');

    // Cambiar botón de acción según sección
    const btn = document.getElementById('btn-accion');
    if (seccion === 'visitas')    { btn.textContent = '➕ Crear Servicio Adicional'; btn.onclick = abrirModalRegistro; btn.style.display = 'block'; }
    if (seccion === 'servicios')  { btn.textContent = '➕ Crear Servicio Adicional'; btn.onclick = abrirModalRegistro; btn.style.display = 'block'; }
    if (seccion === 'residentes') { btn.style.display = 'none'; }
    if (seccion === 'empleados')  { btn.style.display = 'none'; }

    // Cargar datos según sección
    if (seccion === 'visitas' || seccion === 'servicios') cargarVisitas();
    if (seccion === 'residentes') cargarResidentes();
    if (seccion === 'empleados')  cargarEmpleados();
}

function cargarResidentes() {
    fetch('../api/residentes/listar.php')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.length === 0) {
                html = '<tr><td colspan="5" style="text-align:center">No hay residentes registrados</td></tr>';
            } else {
                data.forEach(r => {
                    html += `
                        <tr>
                            <td><strong>${r.nombre}</strong></td>
                            <td>${r.cedula}</td>
                            <td>Apt. ${r.apartamento} — Piso ${r.piso}</td>
                            <td>${r.telefono ?? '—'}</td>
                            <td>${r.fecha_ingreso}</td>
                        </tr>
                    `;
                });
            }
            document.getElementById('tabla_residentes').innerHTML = html;
        })
        .catch(err => console.error('Error cargando residentes:', err));
}

function cargarEmpleados() {
    fetch('../api/empleados/listar.php')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.length === 0) {
                html = '<tr><td colspan="5" style="text-align:center">No hay empleados registrados</td></tr>';
            } else {
                data.forEach(e => {
                    const turno = e.hora_inicio
                        ? `${e.hora_inicio.slice(0,5)} - ${e.hora_fin.slice(0,5)}`
                        : 'Sin turno hoy';
                    html += `
                        <tr>
                            <td><strong>${e.nombre}</strong></td>
                            <td>${e.cedula}</td>
                            <td>${e.cargo}</td>
                            <td>${e.telefono ?? '—'}</td>
                            <td>${turno}</td>
                        </tr>
                    `;
                });
            }
            document.getElementById('tabla_empleados').innerHTML = html;
        })
        .catch(err => console.error('Error cargando empleados:', err));
}

window.onload = cargarVisitas;

function cargarResidentes() {
    fetch('../api/residentes/listar.php')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.length === 0) {
                html = '<tr><td colspan="5" style="text-align:center;padding:1rem;">No hay residentes registrados</td></tr>';
            } else {
                data.forEach(r => {
                    html += `
                        <tr>
                            <td><strong>${r.nombre}</strong></td>
                            <td>${r.cedula}</td>
                            <td>Apt. ${r.apartamento} — Piso ${r.piso}</td>
                            <td>${r.telefono ?? '—'}</td>
                            <td>${r.fecha_ingreso}</td>
                        </tr>
                    `;
                });
            }
            document.getElementById('tabla-residentes').innerHTML = html;
        })
        .catch(err => console.error('Error cargando residentes:', err));
}

function cargarEmpleados() {
    fetch('../api/empleados/listar.php')
        .then(res => res.json())
        .then(data => {
            let html = '';
            if (data.length === 0) {
                html = '<tr><td colspan="5" style="text-align:center;padding:1rem;">No hay empleados registrados</td></tr>';
            } else {
                data.forEach(e => {
                    const turno = e.hora_inicio
                        ? `${e.hora_inicio.slice(0,5)} — ${e.hora_fin.slice(0,5)}`
                        : 'Sin turno hoy';
                    const estado = e.turno_activo == 1
                        ? '<span class="badge-status badge-edificio">En turno</span>'
                        : '<span class="badge-status badge-finalizada">Fuera de turno</span>';
                    html += `
                        <tr>
                            <td><strong>${e.nombre}</strong></td>
                            <td>${e.cargo}</td>
                            <td>${e.telefono ?? '—'}</td>
                            <td>${estado}</td>
                            <td>${turno}</td>
                        </tr>
                    `;
                });
            }
            document.getElementById('tabla-empleados').innerHTML = html;
        })
        .catch(err => console.error('Error cargando empleados:', err));
}
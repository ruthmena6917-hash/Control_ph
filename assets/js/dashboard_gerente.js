let mainChart = null;
let currentPeriod = 'dia';
let currentMetric = 'visitas';
let cachedUsers = [];

document.addEventListener('DOMContentLoaded', () => {
    swTab('home', document.querySelector('.nav-item-p.active'));
    initForms();
});

function swTab(id, el) {
    document.querySelectorAll('.dashboard-section').forEach(s => s.classList.remove('active'));
    const section = document.getElementById('sec-' + id);
    if (section) section.classList.add('active');
    
    if (el) {
        document.querySelectorAll('.nav-item-p').forEach(n => n.classList.remove('active'));
        el.classList.add('active');
    }

    if (id === 'home') { loadCh(currentPeriod); loadSt(); loadVisDetailed('tbody-home-visitas'); }
    if (id === 'visitas') loadVisDetailed('tbody-visitas-full');
    if (id === 'usuarios') loadUCards();
    if (id === 'externos') loadExCards();
}

// --- ANALYTICS ---
function swMetric(metric) {
    currentMetric = metric;
    document.querySelectorAll('.btn-metric-p').forEach(b => {
        b.classList.toggle('active', b.dataset.metric === metric);
    });
    loadCh(currentPeriod);
}

function loadCh(periodo, el) {
    currentPeriod = periodo;
    if (el) {
        document.querySelectorAll('.btn-filter-p').forEach(b => b.classList.remove('active'));
        el.classList.add('active');
    }

    fetch(`../api/visitas/get_analytics_data.php?periodo=${periodo}`)
        .then(r => r.json())
        .then(res => {
            if (res.success) renderPremiumChart(res, currentMetric);
        });
}

function renderPremiumChart(res, metric) {
    const ctx = document.getElementById('mainChart').getContext('2d');
    if (mainChart) mainChart.destroy();

    const data = res.datasets[metric];
    const labels = res.labels;

    const colors = {
        visitas: { border: '#1e3a8a', bg: 'rgba(59, 130, 246, 0.4)' },
        ocupacion: { border: '#10b981', bg: 'rgba(16, 185, 129, 0.4)' },
        servicios: { border: '#f59e0b', bg: 'rgba(245, 158, 11, 0.4)' }
    };

    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, colors[metric].bg);
    gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');

    mainChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: metric.toUpperCase(),
                data: data,
                borderColor: colors[metric].border,
                borderWidth: 4,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderColor: colors[metric].border,
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: { 
                    padding: 12, 
                    backgroundColor: '#0f172a',
                    callbacks: {
                        label: (ctx) => ` ${metric.toUpperCase()}: ${ctx.raw}`
                    }
                } 
            },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#64748b', font: { weight: 700 } } },
                x: { grid: { display: false }, ticks: { color: '#64748b', font: { weight: 700 } } }
            }
        }
    });
}

function loadSt() {
    fetch('../api/visitas/listado_gerente.php')
        .then(r => r.json())
        .then(data => {
            document.getElementById('st-total').innerText = data.length;
            document.getElementById('st-in').innerText = data.filter(v => v.estado === 'en_edificio').length;
            document.getElementById('st-serv').innerText = data.filter(v => (v.notas || '').includes('[SERVICIO]')).length;
        });
}

function loadVisDetailed(targetId) {
    fetch('../api/visitas/listado_gerente.php')
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById(targetId);
            if (!tbody) return;
            
            const list = (targetId === 'tbody-home-visitas') ? data.slice(0, 10) : data;
            
            tbody.innerHTML = list.map(v => {
                const badge = v.estado === 'pendiente' ? 'badge-pendiente' : (v.estado === 'en_edificio' ? 'badge-edificio' : 'badge-finalizada');
                return `
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <img src="${v.foto_url ? '../'+v.foto_url : '../assets/img/default-user.png'}" style="width:35px; height:35px; border-radius:8px; object-fit:cover;">
                                <div><strong>${v.visitante_nombre}</strong><br><small style="color:#64748b">${v.visitante_cedula}</small></div>
                            </div>
                        </td>
                        <td>Torre ${v.torre} - Apt ${v.apartamento}</td>
                        <td>${v.residente_nombre}</td>
                        <td>
                            <div style="font-size:0.75rem;">
                                Por: <b>${v.registrado_por_nombre}</b><br>
                                <span style="color: ${v.validado_por_nombre ? '#10b981' : '#f59e0b'}">
                                    Val: <b>${v.validado_por_nombre || 'Pendiente'}</b>
                                </span>
                            </div>
                        </td>
                        <td><span class="badge-status ${badge}">${v.estado.toUpperCase()}</span></td>
                        <td>
                            <button class="btn-action-p" onclick="vVD(${v.id})" title="Ver Detalles">📂</button>
                        </td>
                    </tr>
                `;
            }).join('');
        });
}

// Detalle de Visita
function vVD(id) {
    opMod('modalUD');
    const container = document.getElementById('ud-c');
    container.innerHTML = '<p style="text-align:center; padding:2rem;">Cargando registro de seguridad...</p>';

    fetch(`../api/visitas/get_detalle.php?id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) return container.innerHTML = 'Error al cargar datos.';
            const v = res.data;
            container.innerHTML = `
                <div style="display:flex; align-items:center; gap:2rem; margin-bottom:2rem; border-bottom:1px solid #f1f5f9; padding-bottom:1.5rem;">
                    <img src="${v.foto_url ? '../'+v.foto_url : '../assets/img/default-user.png'}" style="width:100px; height:100px; border-radius:24px; object-fit:cover;">
                    <div>
                        <h2 style="margin:0; font-weight:800;">${v.visitante_nombre}</h2>
                        <span class="badge-status badge-${v.estado}">${v.estado.toUpperCase()}</span>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; font-size:0.9rem;">
                    <div><label style="font-weight:800; color:#64748b; font-size:0.7rem;">CÉDULA</label><div style="font-weight:600;">${v.visitante_cedula}</div></div>
                    <div><label style="font-weight:800; color:#64748b; font-size:0.7rem;">UBICACIÓN</label><div style="font-weight:600;">Torre ${v.torre} - Apt ${v.apartamento}</div></div>
                    <div><label style="font-weight:800; color:#64748b; font-size:0.7rem;">VEHÍCULO (PLACA)</label><div style="font-weight:600;">${v.placa_vehiculo || 'PEA TÓN'}</div></div>
                    <div><label style="font-weight:800; color:#64748b; font-size:0.7rem;">REGISTRADO POR</label><div style="font-weight:600;">${v.registrado_por_nombre}</div></div>
                    <div><label style="font-weight:800; color:#64748b; font-size:0.7rem;">FECHA PROGRAMADA</label><div style="font-weight:600;">${v.fecha_programada}</div></div>
                    <div><label style="font-weight:800; color:#64748b; font-size:0.7rem;">ENTRADA REAL</label><div style="font-weight:600;">${v.fecha_entrada_real || '--'}</div></div>
                </div>
                <div style="margin-top:1.5rem; padding:1rem; background:#f8fafc; border-radius:12px;">
                    <label style="font-weight:800; color:#64748b; font-size:0.7rem;">NOTAS DE SEGURIDAD</label>
                    <p style="margin:5px 0 0; font-size:0.85rem;">${v.notas || 'Sin observaciones.'}</p>
                </div>
            `;
        });
}

function loadUCards() {
    fetch('../api/usuarios/listar.php')
        .then(r => r.json())
        .then(data => {
            cachedUsers = data;
            fU();
        });
}

function fU() {
    const q = document.getElementById('src-u').value.toLowerCase();
    const r = document.getElementById('flt-rol').value;
    const s = document.getElementById('flt-st').value;

    const filtered = cachedUsers.filter(u => {
        const mq = u.nombre.toLowerCase().includes(q) || u.cedula.includes(q);
        const mr = r === "" || u.rol_nombre.toLowerCase() === r.toLowerCase();
        const ms = s === "" || u.activo == s;
        return mq && mr && ms;
    });

    const container = document.getElementById('cards-u');
    container.innerHTML = filtered.map(u => {
        const roleStyles = {
            'gerente': { bg: '#dbeafe', color: '#1e40af' },
            'seguridad': { bg: '#fef3c7', color: '#92400e' },
            'residente': { bg: '#dcfce7', color: '#166534' }
        };
        const st = roleStyles[u.rol_nombre.toLowerCase()] || { bg: '#f1f5f9', color: '#475569' };

        // Información específica por rol
        let extraInfo = '';
        if (u.rol_nombre.toLowerCase() === 'residente') {
            extraInfo = `<div style="color: #1e3a8a; font-weight: 700; margin-top: 5px;">Ubicación: Torre ${u.apt_torre || '?'} - Apt ${u.apt_numero || '?'}</div>`;
        } else if (u.rol_nombre.toLowerCase() === 'seguridad') {
            const turno = u.turno_inicio ? `Horario: ${u.turno_inicio.slice(0,5)} - ${u.turno_fin.slice(0,5)}` : 'Sin asignación hoy';
            extraInfo = `<div style="color: #92400e; font-weight: 700; margin-top: 5px;">${turno}</div>`;
        }

        return `
            <div class="card-p">
                <div class="card-header-p">
                    <img src="${u.foto_url || '../assets/img/default-user.png'}" class="card-img-p">
                    <div style="flex:1">
                        <h4 style="margin:0; font-weight:800;">${u.nombre}</h4>
                        <span class="role-badge-p" style="background:${st.bg}; color:${st.color};">${u.rol_nombre}</span>
                    </div>
                    <div style="width:10px; height:10px; border-radius:50%; background:${u.activo == 1 ? '#10b981' : '#ef4444'}"></div>
                </div>
                <div style="font-size:0.85rem; color:#64748b; display:grid; gap:5px;">
                    <div>ID: ${u.cedula}</div>
                    ${extraInfo}
                </div>
                <button class="btn-main-p" style="width:100%; margin-top:1.2rem; background:#f8fafc; color:#1e293b; border:1px solid #e2e8f0; box-shadow:none;" onclick="vDU(${u.id})">Detalles de Perfil</button>
            </div>
        `;
    }).join('');
}

function vDU(id) {
    opMod('modalUD');
    const container = document.getElementById('ud-c');
    container.innerHTML = '<p style="text-align:center; padding:2rem;">Cargando perfil...</p>';

    fetch(`../api/usuarios/get_detalle.php?id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) return container.innerHTML = 'Error al cargar';
            const u = res.data;
            container.innerHTML = `
                <div style="display:flex; align-items:center; gap:2rem; margin-bottom:2rem; border-bottom:1px solid #f1f5f9; padding-bottom:1.5rem;">
                    <img src="${u.foto_url || '../assets/img/default-user.png'}" style="width:100px; height:100px; border-radius:24px; object-fit:cover;">
                    <div>
                        <h2 style="margin:0; font-weight:800;">${u.nombre}</h2>
                        <b style="color:#3b82f6;">${u.rol_nombre.toUpperCase()}</b>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; font-size:0.9rem;">
                    <div><label style="font-weight:800; color:#64748b; font-size:0.7rem;">CÉDULA / ID</label><div style="font-weight:600;">${u.cedula}</div></div>
                    <div><label style="font-weight:800; color:#64748b; font-size:0.7rem;">USUARIO</label><div style="font-weight:600;">@${u.username || 'N/A'}</div></div>
                    <div><label style="font-weight:800; color:#64748b; font-size:0.7rem;">EMAIL</label><div style="font-weight:600;">${u.email || 'N/A'}</div></div>
                    <div><label style="font-weight:800; color:#64748b; font-size:0.7rem;">TELÉFONO</label><div style="font-weight:600;">${u.telefono || 'N/A'}</div></div>
                    
                    <div><label style="font-weight:800; color:#64748b; font-size:0.7rem;">TIPO DE SANGRE</label><div style="font-weight:600;">${u.tipo_sangre || '--'}</div></div>
                    <div><label style="font-weight:800; color:#64748b; font-size:0.7rem;">PLACA PRINCIPAL</label><div style="font-weight:600;">${u.placa_principal || 'No registrado'}</div></div>
                    
                    <div><label style="font-weight:800; color:#64748b; font-size:0.7rem;">ÚLTIMO LOGIN</label><div style="font-weight:600;">${u.ultimo_login || 'Nunca'}</div></div>
                    <div><label style="font-weight:800; color:#64748b; font-size:0.7rem;">MIEMBRO DESDE</label><div style="font-weight:600;">${u.fecha_creacion}</div></div>
                </div>
                <div style="margin-top:1.5rem; padding:1rem; background:#f8fafc; border-radius:12px;">
                    <label style="font-weight:800; color:#64748b; font-size:0.7rem;">CONTACTO DE EMERGENCIA</label>
                    <p style="margin:5px 0 0; font-size:0.85rem;">${u.contacto_emergencia || 'No especificado.'}</p>
                </div>
            `;
        });
}

function loadExCards() {
    fetch('../api/empleados_externos/listar.php')
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('cards-ex');
            if (data.length === 0) {
                container.innerHTML = `
                    <div style="grid-column:1/-1; text-align:center; padding:4rem; background:white; border-radius:24px;">
                        <h3 style="margin:10px 0;">No hay servicios registrados</h3>
                        <p style="color:#64748b;">Administre proveedores y personal externo del edificio.</p>
                        <button class="btn-main-p" onclick="opMod('modalAddEx')">Registrar Servicio</button>
                    </div>
                `;
                return;
            }
            container.innerHTML = data.map(e => `
                <div class="card-p" style="border-left:5px solid #3b82f6;">
                    <div class="card-header-p">
                        <img src="${e.foto_url || '../assets/img/default-user.png'}" class="card-img-p">
                        <div style="flex:1">
                            <h4 style="margin:0; font-weight:800;">${e.nombre}</h4>
                            <span class="role-badge-p" style="background:#e0f2fe; color:#0369a1;">${e.servicio_tipo}</span>
                        </div>
                    </div>
                    <div style="font-size:0.85rem; color:#64748b; display:grid; gap:4px;">
                        <div><b>Empresa:</b> ${e.empresa || 'Independiente'}</div>
                        <div style="margin-top:10px; padding:10px; background:#f8fafc; border-radius:10px; text-align:center;">
                            <small>CÓDIGO DE ACCESO</small><br>
                            <code style="font-size:1.1rem; font-weight:900; color:#1e3a8a;">${e.codigo_qr}</code>
                        </div>
                    </div>
                </div>
            `).join('');
        });
}

function initForms() {
    // Form Usuario
    document.getElementById('f-cu').onsubmit = function(e) {
        e.preventDefault();
        fetch('../api/usuarios/registrar.php', { method: 'POST', body: new FormData(this) })
            .then(r => r.json())
            .then(res => {
                if (res.success) { alert('Usuario creado'); clMod('modalAddUser'); loadUCards(); }
                else alert(res.error);
            });
    };

    // Form Externo
    const fEx = document.getElementById('f-ex');
    if (fEx) {
        fEx.onsubmit = function(e) {
            e.preventDefault();
            fetch('../api/empleados_externos/registrar.php', { method: 'POST', body: new FormData(this) })
                .then(r => r.json())
                .then(res => {
                    if (res.success) { alert('Externo registrado. QR: ' + res.codigo_qr); clMod('modalAddEx'); loadExCards(); }
                    else alert(res.error);
                });
        };
    }
    
    // Form Perfil
    document.getElementById('frm-p').onsubmit = function(e) {
        e.preventDefault();
        alert('Perfil guardado (Simulando persistencia)');
    };
}

function opMod(id) { document.getElementById(id).style.display = 'block'; }
function clMod(id, event) { 
    if (typeof id === 'string') document.getElementById(id).style.display = 'none';
    else id.style.display = 'none';
}
function prev(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('p-img').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
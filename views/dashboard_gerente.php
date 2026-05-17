<?php
require_once '../auth/session.php';
verificarRol(['gerente']);
require_once '../config/database.php';

// Obtener datos detallados del Gerente
$gerente_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("
    SELECT u.*, r.nombre as rol_nombre, a.username, a.ultimo_login 
    FROM usuarios u 
    JOIN roles r ON u.rol_id = r.id 
    LEFT JOIN autenticacion a ON u.id = a.usuario_id 
    WHERE u.id = ?
");
$stmt->execute([$gerente_id]);
$gerente_data = $stmt->fetch(PDO::FETCH_ASSOC);

$roles = $pdo->query("SELECT id, nombre FROM roles WHERE nombre NOT IN ('visitante')")->fetchAll(PDO::FETCH_ASSOC);
$apartamentos = $pdo->query("SELECT id, numero, torre FROM apartamentos ORDER BY torre, numero")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Gerencia | Sistema de Control de Accesos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/css/dashboard_gerente.css">
    <style>
        :root {
            --font-main: 'Inter', sans-serif;
            --primary-dark: #0f172a;
            --accent-blue: #3b82f6;
            --success-green: #10b981;
            --warning-orange: #f59e0b;
        }
        body { font-family: var(--font-main); }
        .modal-header-p { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem; }
        .btn-close-p { background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; transition: 0.2s; }
        .btn-close-p:hover { color: var(--primary-dark); }
        .form-section-title { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #64748b; margin: 1.5rem 0 0.8rem; letter-spacing: 0.05em; border-left: 3px solid var(--accent-blue); padding-left: 10px; }
        .form-section-title:first-child { margin-top: 0; }
        .nav-item-p small { font-size: 0.6rem; opacity: 0.5; margin-right: 5px; }
    </style>
</head>
<body>

    <!-- Navegación Superior Corporativa -->
    <header class="premium-header">
        <div class="brand-p">
            Control Ph <small style="font-size: 0.6rem; letter-spacing: 2px; opacity: 0.6; margin-left: 8px;">ENTERPRISE</small>
        </div>
        
        <nav class="nav-p">
            <a href="javascript:void(0)" class="nav-item-p active" onclick="swTab('home', this)">Panel General</a>
            <a href="javascript:void(0)" class="nav-item-p" onclick="swTab('visitas', this)">Bitácora de Accesos</a>
            <a href="javascript:void(0)" class="nav-item-p" onclick="swTab('usuarios', this)">Gestión de Usuarios</a>
            <a href="javascript:void(0)" class="nav-item-p" onclick="swTab('externos', this)">Proveedores Externos</a>
            <a href="javascript:void(0)" class="nav-item-p" onclick="swTab('perfil', this)">Mi Cuenta</a>
        </nav>

        <div class="user-badge-p">
            <img src="<?php echo $gerente_data['foto_url'] ?: '../assets/img/default-user.png'; ?>" style="width: 32px; height: 32px; border-radius: 8px; object-fit: cover;">
            <div style="line-height: 1;">
                <div style="font-weight: 600; font-size: 0.85rem;"><?php echo $gerente_data['nombre']; ?></div>
                <div style="font-size: 0.65rem; opacity: 0.7;">Administrador</div>
            </div>
            <a href="../auth/logout.php" style="color: white; margin-left:15px; opacity: 0.4; text-decoration: none; font-size: 0.8rem;" title="Cerrar Sesión">SALIR</a>
        </div>
    </header>

    <main class="container-p">

        <!-- SECCIÓN 1: INICIO & ANALÍTICA -->
        <section id="sec-home" class="dashboard-section active">
            <div style="display: flex; gap: 10px; margin-bottom: 2rem;">
                <button class="btn-metric-p active" data-metric="visitas" onclick="swMetric('visitas')">Flujo de Entradas</button>
                <button class="btn-metric-p" data-metric="ocupacion" onclick="swMetric('ocupacion')" style="color:#10b981">Ocupación en Tiempo Real</button>
                <button class="btn-metric-p" data-metric="servicios" onclick="swMetric('servicios')" style="color:#f59e0b">Servicios Técnicos</button>
            </div>

            <div class="analytics-grid">
                <div class="glass-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2 style="margin:0; font-weight: 800; font-size: 1.2rem;" id="chart-main-title">Análisis Operativo</h2>
                        <div class="chart-filters" style="display: flex; gap: 8px;">
                            <button class="btn-filter-p active" onclick="loadCh('dia', this)">Hoy</button>
                            <button class="btn-filter-p" onclick="loadCh('semana', this)">Últimos 7 días</button>
                        </div>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="mainChart"></canvas>
                    </div>
                </div>

                <div class="stat-group-p">
                    <div class="mini-stat">
                        <div class="label">Accesos Registrados (Hoy)</div>
                        <div class="val" id="st-total">-</div>
                    </div>
                    <div class="mini-stat" style="border-left-color: #10b981;">
                        <div class="label">Personas en Edificio</div>
                        <div class="val" style="color: #10b981;" id="st-in">-</div>
                    </div>
                    <div class="mini-stat" style="border-left-color: #f59e0b;">
                        <div class="label">Servicios Activos</div>
                        <div class="val" style="color: #f59e0b;" id="st-serv">-</div>
                    </div>
                </div>
            </div>

            <div class="premium-table-container">
                <div style="padding: 1.2rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9;">
                    <h3 style="margin:0; font-weight: 800; font-size: 1rem;">Ultimos Movimientos</h3>
                    <button class="btn-action-p" onclick="swTab('visitas')">Ir a Bitácora Completa</button>
                </div>
                <table class="p-table" id="table-home-visitas">
                    <thead>
                        <tr>
                            <th>Visitante</th>
                            <th>Unidad / Torre</th>
                            <th>Residente</th>
                            <th>Autorización</th>
                            <th>Estado</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="tbody-home-visitas"></tbody>
                </table>
            </div>
        </section>

        <!-- SECCIÓN 2: VISITAS (COMPLETO) -->
        <section id="sec-visitas" class="dashboard-section" style="padding-top: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="margin:0; font-weight: 800; font-size: 1.5rem;">Bitácora de Accesos</h1>
                <div style="display: flex; gap: 10px;">
                    <input type="date" id="vis-date" class="input-p" value="<?php echo date('Y-m-d'); ?>" onchange="loadVisDetailed('tbody-visitas-full')">
                </div>
            </div>
            
            <div class="premium-table-container">
                <table class="p-table">
                    <thead>
                        <tr>
                            <th>Visitante</th>
                            <th>Unidad / Torre</th>
                            <th>Residente</th>
                            <th>Personal de Seguridad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-visitas-full"></tbody>
                </table>
            </div>
        </section>

        <!-- SECCIÓN 3: USUARIOS -->
        <section id="sec-usuarios" class="dashboard-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="margin:0; font-weight: 800; font-size: 1.5rem;">Censo de Usuarios</h1>
                <button class="btn-main-p" onclick="opMod('modalAddUser')">+ Registrar Nuevo Usuario</button>
            </div>

            <div class="filters-bar-p">
                <div class="search-field-p">
                    <input type="text" id="src-u" class="input-p" placeholder="Filtrar por nombre o identificación..." onkeyup="fU()">
                </div>
                <select id="flt-rol" class="filter-select-p" onchange="fU()">
                    <option value="">Todos los Roles</option>
                    <?php foreach($roles as $r): ?><option value="<?php echo $r['nombre']; ?>"><?php echo ucfirst($r['nombre']); ?></option><?php endforeach; ?>
                </select>
                <select id="flt-st" class="filter-select-p" onchange="fU()">
                    <option value="">Estado: Todos</option>
                    <option value="1">Activos</option>
                    <option value="0">Desactivados</option>
                </select>
            </div>

            <div class="user-cards-container" id="cards-u"></div>
        </section>

        <!-- SECCIÓN 4: EXTERNOS -->
        <section id="sec-externos" class="dashboard-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="margin:0; font-weight: 800; font-size: 1.5rem;">Registro de Proveedores</h1>
                <button class="btn-main-p" onclick="opMod('modalAddEx')">+ Registro de Personal Externo</button>
            </div>
            <div class="user-cards-container" id="cards-ex"></div>
        </section>

        <!-- SECCIÓN 5: PERFIL -->
        <section id="sec-perfil" class="dashboard-section">
            <div class="glass-card" style="max-width: 900px; margin: 0 auto;">
                <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 3rem;">
                    <img src="<?php echo $gerente_data['foto_url'] ?: '../assets/img/default-user.png'; ?>" id="p-img" style="width: 120px; height: 120px; border-radius: 12px; object-fit: cover; border: 4px solid #f1f5f9;">
                    <div>
                        <h1 style="margin:0; font-weight: 800;"><?php echo $gerente_data['nombre']; ?></h1>
                        <p style="color: var(--accent-blue); font-weight: 700; font-size: 0.9rem; margin: 5px 0; text-transform: uppercase;"><?php echo $gerente_data['rol_nombre']; ?></p>
                        <div style="display:flex; gap: 10px; margin-top: 15px;">
                            <input type="file" id="p-file" style="display:none" onchange="prev(this)">
                            <button class="btn-action-p" onclick="document.getElementById('p-file').click()">Cambiar Imagen de Perfil</button>
                        </div>
                    </div>
                </div>

                <form id="frm-p">
                    <div class="grid-form">
                        <div><label class="lbl-p">Nombre y Apellidos</label><input type="text" name="nombre" class="input-p" value="<?php echo $gerente_data['nombre']; ?>"></div>
                        <div><label class="lbl-p">Identificación Oficial</label><input type="text" class="input-p" value="<?php echo $gerente_data['cedula']; ?>" readonly style="background:#f8fafc; color:#94a3b8;"></div>
                        <div><label class="lbl-p">Correo Electrónico</label><input type="email" name="email" class="input-p" value="<?php echo $gerente_data['email']; ?>"></div>
                        <div><label class="lbl-p">Teléfono de Enlace</label><input type="text" name="telefono" class="input-p" value="<?php echo $gerente_data['telefono']; ?>"></div>
                        <div><label class="lbl-p">Última Actividad</label><input type="text" class="input-p" value="<?php echo $gerente_data['ultimo_login']; ?>" readonly style="background:#f8fafc; color:#94a3b8;"></div>
                        <div><label class="lbl-p">Nombre de Usuario</label><input type="text" class="input-p" value="<?php echo $gerente_data['username']; ?>" readonly style="background:#f8fafc; color:#94a3b8;"></div>
                    </div>
                    <div style="margin-top: 2rem; border-top: 1px solid #f1f5f9; padding-top: 2rem; text-align: right;">
                        <button type="submit" class="btn-main-p" style="padding: 1rem 3rem; font-size: 0.9rem; border-radius: 8px;">Guardar Cambios de Perfil</button>
                    </div>
                </form>
            </div>
        </section>

    </main>

    <!-- MODAL DETALLES -->
    <div id="modalUD" class="modal-overlay-p" onclick="clMod(this, event)">
        <div class="modal-content-p" onclick="event.stopPropagation()">
            <div class="modal-header-p">
                <h3 style="margin:0; font-weight:800; font-size: 1rem; color: var(--primary-dark);">Ficha Detallada</h3>
                <button class="btn-close-p" onclick="clMod('modalUD')">&times;</button>
            </div>
            <div id="ud-c"></div>
        </div>
    </div>

    <!-- MODAL AÑADIR USUARIO (REDISEÑADO) -->
    <div id="modalAddUser" class="modal-overlay-p" onclick="clMod(this, event)">
        <div class="modal-content-p" style="max-width: 600px;" onclick="event.stopPropagation()">
            <div class="modal-header-p">
                <h3 style="margin:0; font-weight:800; font-size: 1rem; color: var(--primary-dark);">Registro de Nuevo Usuario</h3>
                <button class="btn-close-p" onclick="clMod('modalAddUser')">&times;</button>
            </div>
            <form id="f-cu">
                <div class="form-section-title">Información Básica</div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                    <input type="text" name="nombre" class="input-p" placeholder="Nombre completo" required>
                    <input type="text" name="cedula" class="input-p" placeholder="Identificación / Cédula" required>
                    <input type="email" name="email" class="input-p" placeholder="Correo electrónico">
                    <input type="text" name="telefono" class="input-p" placeholder="Teléfono">
                </div>

                <div class="form-section-title">Seguridad y Emergencias</div>
                <div style="display:grid; grid-template-columns: 100px 1fr 1fr; gap:12px;">
                    <select name="tipo_sangre" class="input-p">
                        <option value="">Sangre</option>
                        <option value="O+">O+</option><option value="O-">O-</option>
                        <option value="A+">A+</option><option value="A-">A-</option>
                    </select>
                    <input type="text" name="contacto_emergencia" class="input-p" placeholder="Contacto de emergencia (Nombre/Telf)">
                    <input type="text" name="placa_principal" class="input-p" placeholder="Placa vehicular principal">
                </div>

                <div class="form-section-title">Credenciales de Acceso</div>
                <div style="display:grid; grid-template-columns:100px 1fr 1fr; gap:12px;">
                    <select name="rol_id" class="input-p" required>
                        <option value="">Rol...</option>
                        <?php foreach($roles as $r): ?><option value="<?php echo $r['id']; ?>"><?php echo ucfirst($r['nombre']); ?></option><?php endforeach; ?>
                    </select>
                    <input type="text" name="username" class="input-p" placeholder="Usuario de acceso" required>
                    <input type="password" name="password" class="input-p" placeholder="Contraseña temporal" required>
                </div>

                <div style="margin-top:2rem; padding-top:1.5rem; border-top:1px solid #f1f5f9;">
                    <button type="submit" class="btn-main-p" style="width:100%; height: 50px; font-size: 0.9rem; border-radius: 8px;">Finalizar Registro e Inicializar Cuenta</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL AÑADIR EXTERNO -->
    <div id="modalAddEx" class="modal-overlay-p" onclick="clMod(this, event)">
        <div class="modal-content-p" style="max-width: 500px;" onclick="event.stopPropagation()">
            <div class="modal-header-p">
                <h3 style="margin:0; font-weight:800; font-size: 1rem; color: var(--primary-dark);">Habilitación de Proveedor</h3>
                <button class="btn-close-p" onclick="clMod('modalAddEx')">&times;</button>
            </div>
            <form id="f-ex">
                <input type="text" name="nombre" class="input-p" placeholder="Nombre completo" required style="margin-bottom:1rem;">
                <input type="text" name="cedula" class="input-p" placeholder="Identificación oficial" required style="margin-bottom:1rem;">
                <input type="text" name="empresa" class="input-p" placeholder="Nombre de la empresa" style="margin-bottom:1rem;">
                <select name="servicio_tipo" class="input-p" required style="margin-bottom:1.5rem;">
                    <option value="">Especialidad del servicio...</option>
                    <option value="Delivery">Entrega / Logística</option>
                    <option value="Mantenimiento">Servicio Técnico</option>
                    <option value="Plomería">Fontanería / Plomería</option>
                    <option value="Seguridad Privada">Seguridad Privada</option>
                </select>
                <button type="submit" class="btn-main-p" style="width:100%; height: 50px; border-radius: 8px;">Generar Token de Acceso Profesional</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/dashboard_gerente.js?v=<?php echo time(); ?>"></script>
</body>
</html>
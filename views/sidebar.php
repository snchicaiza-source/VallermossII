<?php
// views/sidebar.php - Sidebar dinámico por rol
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rol = $_SESSION['rol'] ?? $_SESSION['usuario_rol'] ?? '';
$nombre = $_SESSION['nombres'] ?? $_SESSION['usuario_nombres'] ?? 'Usuario';
$vivienda = $_SESSION['numero_vivienda'] ?? $_SESSION['usuario_vivienda'] ?? '';
$correo = $_SESSION['correo'] ?? $_SESSION['usuario_correo'] ?? '';

$paginaActual = basename($_SERVER['SCRIPT_NAME'], '.php');

$menuItems = [];

if (strtoupper($rol) === 'ADMINISTRADOR') {
    $menuItems = [
        ['icon' => 'fa-solid fa-bullhorn', 'label' => 'Comunicados', 'file' => 'comunicados.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-shield-halved', 'label' => 'Usuarios', 'file' => 'usuarios.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-file-invoice-dollar', 'label' => 'Verificar Pagos', 'file' => 'verificar_pagos.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-cash-register', 'label' => 'Recaudaciones', 'file' => 'recaudacion.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-file-invoice', 'label' => 'Estado de Cuenta', 'file' => 'estado_cuenta.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-certificate', 'label' => 'Cert. Expensas', 'file' => 'certificado_expensas.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-handshake', 'label' => 'Convenios', 'file' => 'convenios.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-truck-field', 'label' => 'Proveedores', 'file' => 'proveedores.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-chart-pie', 'label' => 'Ejec. Presupuestaria', 'file' => 'ejecucion_presupuestaria.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-scale-balanced', 'label' => 'Doc. Legal', 'file' => 'documentacion_legal.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-folder-open', 'label' => 'Tramites', 'file' => 'tramites.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-boxes-stacked', 'label' => 'Activos', 'file' => 'activos.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-building', 'label' => 'Espacios', 'file' => 'espacios.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-triangle-exclamation', 'label' => 'Incidencias', 'file' => 'incidencias.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-square-poll-vertical', 'label' => 'Encuestas', 'file' => 'encuestas.php', 'section' => 'administrador'],
    ];
} elseif (strtoupper($rol) === 'DIRECTIVA') {
    $menuItems = [
        ['icon' => 'fa-solid fa-landmark', 'label' => 'Dashboard', 'file' => 'dashboard.php', 'section' => 'directiva'],
        ['icon' => 'fa-solid fa-chart-line', 'label' => 'Ejec. Presupuestaria', 'file' => 'ejecucion_presupuestaria.php', 'section' => 'directiva'],
        ['icon' => 'fa-solid fa-scale-balanced', 'label' => 'Doc. Legal', 'file' => 'documentacion_legal.php', 'section' => 'directiva'],
        ['icon' => 'fa-solid fa-file-invoice-dollar', 'label' => 'Verificar Pagos', 'file' => 'verificar_pagos.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-bullhorn', 'label' => 'Comunicados', 'file' => 'comunicados.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-triangle-exclamation', 'label' => 'Incidencias', 'file' => 'incidencias.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-square-poll-vertical', 'label' => 'Encuestas', 'file' => 'encuestas.php', 'section' => 'directiva'],
    ];
} elseif (strtoupper($rol) === 'RESIDENTE') {
    $menuItems = [
        ['icon' => 'fa-solid fa-chart-line', 'label' => 'Mi Panel', 'file' => 'dashboard.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-user-gear', 'label' => 'Mi Perfil', 'file' => 'perfil.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-file-invoice-dollar', 'label' => 'Estado de Cuenta', 'file' => 'estado_cuenta.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-receipt', 'label' => 'Reporte Pagos', 'file' => 'reporte_pagos.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-calendar-check', 'label' => 'Reservas', 'file' => 'reservas.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-chart-bar', 'label' => 'Encuestas', 'file' => 'encuestas.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-screwdriver-wrench', 'label' => 'Reportar Danos', 'file' => 'reportar_danos.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-flag', 'label' => 'Denuncias', 'file' => 'denuncias.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-certificate', 'label' => 'Cert. Deuda', 'file' => 'certificado_deuda.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-book', 'label' => 'Normativa', 'file' => 'normativa.php', 'section' => 'residente'],
    ];
}

$rolBadge = 'badge-info';
if (strtoupper($rol) === 'ADMINISTRADOR') {
    $rolBadge = 'badge-danger';
} elseif (strtoupper($rol) === 'DIRECTIVA') {
    $rolBadge = 'badge-warning';
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
/* SIDEBAR INLINE STYLES - siempre aplican */
#sidebar, .sidebar {
    width: 260px;
    height: 100vh;
    background: linear-gradient(180deg, #2E2A27 0%, #1E1B19 100%);
    color: #C8BFB4;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.sidebar-header {
    padding: 20px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 70px;
}
.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}
.sidebar-brand > i {
    font-size: 1.6rem;
    color: #A38F78;
    min-width: 30px;
    text-align: center;
}
.sidebar-brand h2 {
    font-size: 1rem;
    font-weight: 700;
    color: #EAE5DF;
    letter-spacing: 1.5px;
    margin: 0;
    white-space: nowrap;
}
.sidebar-brand small {
    font-size: 0.65rem;
    color: #8A8078;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.sidebar-toggle {
    background: none;
    border: none;
    color: #C8BFB4;
    cursor: pointer;
    padding: 6px 8px;
    border-radius: 6px;
    transition: all 0.3s;
    font-size: 0.85rem;
    opacity: 0.7;
}
.sidebar-toggle:hover {
    background: #3E3732;
    opacity: 1;
}
.sidebar-user {
    padding: 16px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    gap: 12px;
}
.user-avatar {
    font-size: 2.2rem;
    color: #A38F78;
    min-width: 38px;
    text-align: center;
}
.user-info {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.user-name {
    font-weight: 600;
    font-size: 0.85rem;
    color: #EAE5DF;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.user-info .badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.6rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 3px;
    width: fit-content;
}
.badge-danger { background: #B86B61; color: #fff; }
.badge-warning { background: #D4A84B; color: #fff; }
.badge-info { background: #6B8FAD; color: #fff; }
.badge-success { background: #6E7E65; color: #fff; }
.user-house {
    font-size: 0.7rem;
    color: #8A8078;
    margin-top: 2px;
    white-space: nowrap;
}
.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 12px 0;
}
.sidebar-nav::-webkit-scrollbar { width: 4px; }
.sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.sidebar-nav li {
    margin: 2px 10px;
}
.sidebar-nav li a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    color: #C8BFB4;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.3s;
    font-size: 0.88rem;
    font-weight: 500;
    white-space: nowrap;
}
.sidebar-nav li a i {
    min-width: 20px;
    text-align: center;
    font-size: 0.95rem;
}
.sidebar-nav li a:hover {
    background-color: #3E3732;
    color: #FFFFFF;
}
.sidebar-nav li a:hover i {
    color: #A38F78;
}
.sidebar-nav li a.active {
    background-color: #7D6B56;
    color: #FFFFFF;
    font-weight: 600;
}
.sidebar-nav li a.active i {
    color: #FFFFFF;
}
.sidebar-footer {
    padding: 12px 10px;
    border-top: 1px solid rgba(255,255,255,0.08);
}
.btn-logout {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 11px 14px;
    background: none;
    border: none;
    color: #C8BFB4;
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.3s;
    font-size: 0.88rem;
    font-weight: 500;
    font-family: inherit;
}
.btn-logout:hover {
    background-color: #B86B61;
    color: #FFFFFF;
}
.btn-logout i { min-width: 20px; text-align: center; }

/* Main content */
.main-content {
    margin-left: 260px;
    flex: 1;
    min-height: 100vh;
    padding: 24px 32px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Content header */
.content-header {
    margin-bottom: 24px;
}
.content-header h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #36322E;
    display: flex;
    align-items: center;
    gap: 10px;
}
.content-header h1 i { color: #A38F78; }
.content-header .subtitle {
    color: #7A7268;
    font-size: 0.9rem;
    margin-top: 4px;
}

/* Cards */
.card {
    background: #FFFFFF;
    border: 1px solid #D8CFC4;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(54, 50, 46, 0.08);
    padding: 20px;
    margin-bottom: 20px;
}
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #D8CFC4;
}
.card-header h2, .card-header h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #36322E;
}

/* Tables */
.table-responsive { overflow-x: auto; }
table {
    width: 100%;
    border-collapse: collapse;
}
table th {
    background: #F7F5F0;
    color: #7A7268;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 10px 14px;
    text-align: left;
    border-bottom: 2px solid #D8CFC4;
}
table td {
    padding: 10px 14px;
    font-size: 0.88rem;
    color: #36322E;
    border-bottom: 1px solid #EDE9E3;
    vertical-align: middle;
}
table tr:hover td { background: #FDFCFA; }

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 18px;
    border: none;
    border-radius: 6px;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    font-family: inherit;
}
.btn-primary { background: #A38F78; color: #fff; }
.btn-primary:hover { background: #7D6B56; }
.btn-success { background: #6E7E65; color: #fff; }
.btn-success:hover { background: #566B4E; }
.btn-danger { background: #B86B61; color: #fff; }
.btn-danger:hover { background: #964F47; }
.btn-outline { background: transparent; border: 1.5px solid #D8CFC4; color: #36322E; }
.btn-outline:hover { border-color: #A38F78; background: #F7F5F0; }
.btn-sm { padding: 5px 10px; font-size: 0.78rem; }
.btn-outline-danger { background: transparent; border: 1.5px solid #B86B61; color: #B86B61; }
.btn-outline-danger:hover { background: #B86B61; color: #fff; }

/* Alerts */
.alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 0.88rem;
    font-weight: 500;
}
.alert-success { background: #E8F0E5; color: #3D5A35; border: 1px solid #C5D9BE; }
.alert-danger { background: #F5E5E3; color: #8B3A31; border: 1px solid #E8C5C0; }
.alert-warning { background: #FDF5E6; color: #8B6914; border: 1px solid #F0DEB0; }

/* Forms */
.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    font-weight: 600;
    font-size: 0.85rem;
    color: #36322E;
    margin-bottom: 6px;
}
.form-control, select, textarea {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid #D8CFC4;
    border-radius: 6px;
    font-size: 0.95rem;
    color: #36322E;
    background: #fff;
    font-family: inherit;
    transition: border-color 0.3s;
}
.form-control:focus, select:focus, textarea:focus {
    outline: none;
    border-color: #A38F78;
    box-shadow: 0 0 0 3px rgba(163, 143, 120, 0.15);
}
.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

/* Stats */
.stats-grid, .summary-cards, .info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 18px;
    margin-bottom: 24px;
}
@media (min-width: 768px) {
    .stats-grid { grid-template-columns: repeat(3, 1fr); }
    .summary-cards { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
}
.stat-card {
    background: #FFFFFF;
    border: 1px solid #D8CFC4;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(54, 50, 46, 0.08);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}
.stat-info { display: flex; flex-direction: column; }
.stat-value { font-size: 1.4rem; font-weight: 700; color: #36322E; }
.stat-label { font-size: 0.8rem; color: #7A7268; text-transform: uppercase; letter-spacing: 0.3px; }
.info-item { padding: 10px 0; border-bottom: 1px solid #D8CFC4; }
.info-item:last-child { border-bottom: none; }
.info-item label { display: block; font-size: 0.78rem; color: #7A7268; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }
.info-item span { font-size: 0.95rem; color: #36322E; font-weight: 600; }
.cert-header { text-align: center; padding: 20px; border-bottom: 2px solid #A38F78; margin-bottom: 20px; }
.cert-header h2 { color: #7D6B56; margin-bottom: 4px; }

@media print {
    .sidebar, .btn-logout, .btn { display: none !important; }
    .main-content { margin-left: 0 !important; padding: 10px !important; }
    .card { box-shadow: none !important; border: 1px solid #ccc !important; }
    body { background: #fff !important; }
}

@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.mobile-open { transform: translateX(0); }
    .main-content { margin-left: 0; padding: 16px; }
}

/* Notification Bell */
.notif-bell-wrapper {
    position: relative;
}
.notif-bell-btn {
    background: none;
    border: none;
    color: #C8BFB4;
    cursor: pointer;
    padding: 6px 8px;
    border-radius: 6px;
    transition: all 0.3s;
    font-size: 1rem;
    position: relative;
}
.notif-bell-btn:hover {
    background: #3E3732;
    color: #FFFFFF;
}
.notif-badge {
    position: absolute;
    top: 0;
    right: 0;
    background: #B86B61;
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    min-width: 16px;
    height: 16px;
    line-height: 16px;
    text-align: center;
    border-radius: 8px;
    padding: 0 4px;
    display: none;
}
.notif-badge.visible { display: block; }
.notif-dropdown {
    display: none;
    position: fixed;
    top: 0;
    left: 260px;
    width: 380px;
    height: 100vh;
    background: #FFFFFF;
    border-left: 1px solid #D8CFC4;
    box-shadow: 4px 0 24px rgba(0,0,0,0.12);
    z-index: 2000;
    overflow: hidden;
    transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
    transform: translateX(-100%);
}
.notif-dropdown.open {
    display: flex;
    flex-direction: column;
    transform: translateX(0);
}

.notif-dropdown-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #EDE9E3;
    background: #FDFCFA;
    flex-shrink: 0;
}
.notif-dropdown-header h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #36322E;
    margin: 0;
}
.notif-mark-all {
    background: none;
    border: none;
    color: #A38F78;
    cursor: pointer;
    font-size: 0.82rem;
    font-weight: 600;
    font-family: inherit;
}
.notif-mark-all:hover { color: #7D6B56; text-decoration: underline; }
.notif-dropdown-body {
    flex: 1;
    overflow-y: auto;
}
.notif-item {
    display: flex;
    gap: 12px;
    padding: 14px 20px;
    border-bottom: 1px solid #F0ECE7;
    cursor: pointer;
    transition: background 0.2s;
    position: relative;
}
.notif-item:hover { background: #FDFCFA; }
.notif-item.no-leida { background: #FDF8F0; }
.notif-item.no-leida:hover { background: #FAF3E6; }
.notif-item-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
}
.notif-item-icon.comunicado { background: #E8F0E5; color: #6E7E65; }
.notif-item-icon.pago { background: #E5EDF5; color: #6B8FAD; }
.notif-item-icon.reserva { background: #F5E5F0; color: #AD6B8F; }
.notif-item-icon.incidencia { background: #F5E5E3; color: #B86B61; }
.notif-item-icon.general { background: #F7F5F0; color: #A38F78; }
.notif-item-content {
    flex: 1;
    min-width: 0;
}
.notif-item-title {
    font-size: 0.88rem;
    font-weight: 600;
    color: #36322E;
    margin-bottom: 2px;
}
.notif-item-text {
    font-size: 0.8rem;
    color: #7A7268;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.notif-item-time {
    font-size: 0.72rem;
    color: #A38F78;
    margin-top: 4px;
}
.notif-item-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #A38F78;
    position: absolute;
    top: 20px;
    right: 16px;
}
.notif-item-actions {
    display: flex;
    flex-direction: column;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s;
    flex-shrink: 0;
}
.notif-item:hover .notif-item-actions { opacity: 1; }
.notif-item-actions button {
    background: none;
    border: none;
    cursor: pointer;
    color: #A38F78;
    font-size: 0.75rem;
    padding: 2px 4px;
    border-radius: 3px;
}
.notif-item-actions button:hover {
    background: #F7F5F0;
    color: #36322E;
}
.notif-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: #A38F78;
}
.notif-empty i { font-size: 2.5rem; margin-bottom: 12px; opacity: 0.5; }
.notif-empty p { font-size: 0.9rem; }

</style>

<aside id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="fa-solid fa-building"></i>
            <div>
                <h2>VALLERMOSSO II</h2>
                <small>Conjunto Habitacional</small>
            </div>
        </div>
    <div style="display: flex; align-items: center; gap: 4px;">
        <div class="notif-bell-wrapper">
            <button id="notifBellBtn" class="notif-bell-btn" title="Notificaciones">
                <i class="fa-solid fa-bell"></i>
                <span id="notifBadge" class="notif-badge">0</span>
            </button>
        </div>
    </div>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar">
            <i class="fa-solid fa-circle-user"></i>
        </div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($nombre) ?></span>
            <span class="badge <?= $rolBadge ?>"><?= htmlspecialchars($rol) ?></span>
            <?php if (!empty($vivienda)): ?>
                <small class="user-house"><i class="fa-solid fa-house"></i> <?= htmlspecialchars($vivienda) ?></small>
            <?php endif; ?>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <?php foreach ($menuItems as $item): ?>
                <?php
                    $isActive = ($paginaActual === pathinfo($item['file'], PATHINFO_FILENAME));
                    $href = "../{$item['section']}/{$item['file']}";
                ?>
                <li>
                    <a href="<?= $href ?>" class="<?= $isActive ? 'active' : '' ?>">
                        <i class="<?= $item['icon'] ?>"></i>
                        <span class="nav-text"><?= $item['label'] ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <form action="../../controllers/AuthController.php" method="POST">
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span class="nav-text">Cerrar Sesion</span>
            </button>
        </form>
    </div>
</aside>

<!-- Notificaciones Dropdown -->
<div id="notifDropdown" class="notif-dropdown">
    <div class="notif-dropdown-header">
        <h3><i class="fa-solid fa-bell" style="color: #A38F78;"></i> Notificaciones</h3>
        <button class="notif-mark-all" onclick="notifMarcarTodas()">Marcar todo como leido</button>
    </div>
    <div id="notifDropdownBody" class="notif-dropdown-body">
        <div class="notif-empty">
            <i class="fa-solid fa-bell-slash"></i>
            <p>Cargando notificaciones...</p>
        </div>
    </div>
</div>

<script>
(function() {
    var bell = document.getElementById('notifBellBtn');
    var dropdown = document.getElementById('notifDropdown');
    var badge = document.getElementById('notifBadge');
    var body = document.getElementById('notifDropdownBody');
    var open = false;

    function notifTimeAgo(dateStr) {
        var d = new Date(dateStr.replace(' ', 'T') + (dateStr.indexOf('+') === -1 && dateStr.indexOf('Z') === -1 ? '+00:00' : ''));
        var now = new Date();
        var diff = Math.floor((now - d) / 1000);
        if (diff < 60) return 'Hace un momento';
        if (diff < 3600) return 'Hace ' + Math.floor(diff / 60) + ' min';
        if (diff < 86400) return 'Hace ' + Math.floor(diff / 3600) + ' h';
        if (diff < 604800) return 'Hace ' + Math.floor(diff / 86400) + ' dia(s)';
        return d.toLocaleDateString('es-EC', {day: 'numeric', month: 'short'});
    }

    function notifIcon(tipo) {
        var icons = {
            'COMUNICADO': '<i class="fa-solid fa-bullhorn"></i>',
            'PAGO': '<i class="fa-solid fa-file-invoice-dollar"></i>',
            'RESERVA': '<i class="fa-solid fa-calendar-check"></i>',
            'INCIDENCIA': '<i class="fa-solid fa-triangle-exclamation"></i>'
        };
        return icons[tipo] || '<i class="fa-solid fa-bell"></i>';
    }

    function notifClass(tipo) {
        var cls = {'COMUNICADO':'comunicado','PAGO':'pago','RESERVA':'reserva','INCIDENCIA':'incidencia'};
        return cls[tipo] || 'general';
    }

    function notifUrl(refTipo, refId) {
        var map = {
            'comunicado': 'administrador/comunicados',
            'pago': 'residente/reporte_pagos',
            'reserva': 'residente/reservas',
            'incidencia': 'administrador/incidencias'
        };
        var path = map[refTipo] || '';
        if (path) return '../' + path + '.php';
        return null;
    }

    function renderNotifs(data) {
        var total = data.total_no_leidas;
        if (total > 0) {
            badge.textContent = total > 99 ? '99+' : total;
            badge.classList.add('visible');
        } else {
            badge.classList.remove('visible');
        }

        if (!data.notificaciones || data.notificaciones.length === 0) {
            body.innerHTML = '<div class="notif-empty"><i class="fa-solid fa-bell-slash"></i><p>No tienes notificaciones nuevas</p></div>';
            return;
        }

        var html = '';
        data.notificaciones.forEach(function(n) {
            var url = notifUrl(n.referencia_tipo, n.referencia_id);
            var clickAttr = url ? 'onclick="notifClick(\'' + url + '\',' + n.id + ')"' : 'onclick="notifMarcarUna(' + n.id + ')"';
            var leida = n.leida == 0 ? 'no-leida' : '';
            var dot = n.leida == 0 ? '<div class="notif-item-dot"></div>' : '';

            html += '<div class="notif-item ' + leida + '" ' + clickAttr + '>';
            html += '  <div class="notif-item-icon ' + notifClass(n.tipo) + '">' + notifIcon(n.tipo) + '</div>';
            html += '  <div class="notif-item-content">';
            html += '    <div class="notif-item-title">' + escHtml(n.titulo) + '</div>';
            html += '    <div class="notif-item-text">' + escHtml(n.mensaje || '') + '</div>';
            html += '    <div class="notif-item-time">' + notifTimeAgo(n.fecha_creacion) + '</div>';
            html += '  </div>';
            html += '  <div class="notif-item-actions">';
            html += '    <button onclick="event.stopPropagation();notifEliminar(' + n.id + ')" title="Eliminar"><i class="fa-solid fa-times"></i></button>';
            html += '  </div>';
            html += dot;
            html += '</div>';
        });
        body.innerHTML = html;
    }

    function escHtml(s) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(s));
        return d.innerHTML;
    }

    function fetchNotifs() {
        fetch('../api/notificaciones.php?action=list')
            .then(function(r) { return r.json(); })
            .then(function(data) { renderNotifs(data); })
            .catch(function() { body.innerHTML = '<div class="notif-empty"><i class="fa-solid fa-exclamation-triangle"></i><p>Error al cargar notificaciones</p></div>'; });
    }

    function fetchCount() {
        fetch('../api/notificaciones.php?action=count')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var t = data.total_no_leidas;
                if (t > 0) { badge.textContent = t > 99 ? '99+' : t; badge.classList.add('visible'); }
                else { badge.classList.remove('visible'); }
            })
            .catch(function(){});
    }

    bell.addEventListener('click', function(e) {
        e.stopPropagation();
        open = !open;
        if (open) {
            dropdown.classList.add('open');
            fetchNotifs();
        } else {
            dropdown.classList.remove('open');
        }
    });

    dropdown.addEventListener('click', function(e) { e.stopPropagation(); });

    document.addEventListener('click', function() {
        if (open) { dropdown.classList.remove('open'); open = false; }
    });

    // Poll cada 30 segundos
    setInterval(fetchCount, 30000);
    fetchCount();

    // Expose globals
    window.notifClick = function(url, id) {
        notifMarcarUna(id);
        window.location.href = url;
    };

    window.notifMarcarUna = function(id) {
        fetch('../api/notificaciones.php?action=marcar_leida&id_notificacion=' + id, {method:'POST'})
            .then(function(r){return r.json();})
            .then(function(d){
                if(d.total_no_leidas !== undefined){
                    if(d.total_no_leidas>0){badge.textContent=d.total_no_leidas>99?'99+':d.total_no_leidas;badge.classList.add('visible');}
                    else{badge.classList.remove('visible');}
                }
                fetchNotifs();
            });
    };

    window.notifEliminar = function(id) {
        fetch('../api/notificaciones.php?action=eliminar&id_notificacion=' + id, {method:'POST'})
            .then(function(r){return r.json();})
            .then(function(d){
                if(d.total_no_leidas !== undefined){
                    if(d.total_no_leidas>0){badge.textContent=d.total_no_leidas>99?'99+':d.total_no_leidas;badge.classList.add('visible');}
                    else{badge.classList.remove('visible');}
                }
                fetchNotifs();
            });
    };

    window.notifMarcarTodas = function() {
        fetch('../api/notificaciones.php?action=marcar_todas', {method:'POST'})
            .then(function(r){return r.json();})
            .then(function(d){
                badge.classList.remove('visible');
                fetchNotifs();
            });
    };
})();
</script>

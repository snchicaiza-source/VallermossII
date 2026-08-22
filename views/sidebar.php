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
        ['icon' => 'fa-solid fa-folder-open', 'label' => 'Trámites', 'file' => 'tramites.php', 'section' => 'administrador'],
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
        ['icon' => 'fa-solid fa-bullhorn', 'label' => 'Comunicados', 'file' => 'comunicados.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-triangle-exclamation', 'label' => 'Incidencias', 'file' => 'incidencias.php', 'section' => 'administrador'],
        ['icon' => 'fa-solid fa-truck-field', 'label' => 'Estado Proveedores', 'file' => 'proveedores.php', 'section' => 'directiva'],
        ['icon' => 'fa-solid fa-square-poll-vertical', 'label' => 'Encuestas', 'file' => 'encuestas.php', 'section' => 'directiva'],
    ];
} elseif (strtoupper($rol) === 'RESIDENTE') {
    $menuItems = [
        ['icon' => 'fa-solid fa-chart-line', 'label' => 'Mi Panel', 'file' => 'dashboard.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-user-gear', 'label' => 'Mi Perfil', 'file' => 'perfil.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-file-invoice-dollar', 'label' => 'Estado de Cuenta', 'file' => 'estado_cuenta.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-receipt', 'label' => 'Reporte Pagos', 'file' => 'reporte_pagos.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-money-check-dollar', 'label' => 'Recibos de Pago', 'file' => 'recibos_pago.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-calendar-check', 'label' => 'Reservas', 'file' => 'reservas.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-chart-bar', 'label' => 'Encuestas', 'file' => 'encuestas.php', 'section' => 'residente'],
        ['icon' => 'fa-solid fa-screwdriver-wrench', 'label' => 'Reportar Daños', 'file' => 'reportar_danos.php', 'section' => 'residente'],
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

/* ============ CAMPANA DE NOTIFICACIONES ESTILO FACEBOOK ============ */
.notif-bell-wrapper {
    position: relative;
}
.notif-bell-btn {
    background: none;
    border: none;
    color: #C8BFB4;
    cursor: pointer;
    padding: 6px 8px;
    border-radius: 50%;
    transition: all 0.25s;
    font-size: 1rem;
    position: relative;
}
.notif-bell-btn:hover {
    background: #3E3732;
    color: #FFFFFF;
}
.notif-bell-btn.ringing i {
    animation: notifRing 0.6s ease-in-out;
}
@keyframes notifRing {
    0% { transform: rotate(0); }
    20% { transform: rotate(18deg); }
    40% { transform: rotate(-14deg); }
    60% { transform: rotate(10deg); }
    80% { transform: rotate(-6deg); }
    100% { transform: rotate(0); }
}
.notif-badge {
    position: absolute;
    top: -2px;
    right: -4px;
    background: #E0331B;
    color: #fff;
    font-size: 0.62rem;
    font-weight: 700;
    min-width: 17px;
    height: 17px;
    line-height: 17px;
    text-align: center;
    border-radius: 9px;
    padding: 0 4px;
    display: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.35);
}
.notif-badge.visible { display: block; }
.notif-badge.pulse { animation: notifPulse 1.2s ease-out 2; }
@keyframes notifPulse {
    0% { box-shadow: 0 0 0 0 rgba(224, 51, 27, 0.55); }
    70% { box-shadow: 0 0 0 10px rgba(224, 51, 27, 0); }
    100% { box-shadow: 0 0 0 0 rgba(224, 51, 27, 0); }
}

/* Panel flotante anclado bajo la campana */
.notif-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 1998;
    background: transparent;
}
.notif-backdrop.open { display: block; }

.notif-dropdown {
    position: fixed;
    top: 64px;
    left: 272px;
    width: 400px;
    max-height: min(560px, calc(100vh - 84px));
    background: #FFFFFF;
    border: 1px solid #E3DCD3;
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(30, 27, 25, 0.22), 0 2px 8px rgba(30, 27, 25, 0.10);
    z-index: 2000;
    overflow: hidden;
    display: none;
    flex-direction: column;
    opacity: 0;
    transform: translateY(-8px) scale(0.98);
    transition: opacity 0.18s ease, transform 0.18s ease;
}
.notif-dropdown.open {
    display: flex;
    opacity: 1;
    transform: translateY(0) scale(1);
}
@media (max-width: 768px) {
    .notif-dropdown {
        left: 12px;
        right: 12px;
        width: auto;
        top: 60px;
        max-height: 70vh;
    }
}

.notif-dropdown-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 14px 18px;
    border-bottom: 1px solid #EDE9E3;
    flex-shrink: 0;
}
.notif-dropdown-header h3 {
    font-size: 1.05rem;
    font-weight: 700;
    color: #36322E;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.notif-count-pill {
    background: #E0331B;
    color: #fff;
    font-size: 0.68rem;
    font-weight: 700;
    min-width: 20px;
    height: 20px;
    line-height: 20px;
    text-align: center;
    border-radius: 10px;
    padding: 0 6px;
    display: none;
}
.notif-count-pill.visible { display: inline-block; }
.notif-mark-all {
    background: none;
    border: none;
    color: #A38F78;
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 600;
    font-family: inherit;
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 4px 8px;
    border-radius: 6px;
    transition: all 0.2s;
}
.notif-mark-all:hover { background: #F7F5F0; color: #7D6B56; }

.notif-dropdown-body {
    flex: 1;
    overflow-y: auto;
    overscroll-behavior: contain;
}
.notif-dropdown-body::-webkit-scrollbar { width: 5px; }
.notif-dropdown-body::-webkit-scrollbar-thumb { background: #D8CFC4; border-radius: 4px; }

.notif-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 13px 18px;
    cursor: pointer;
    transition: background 0.15s;
    position: relative;
    border-bottom: 1px solid #F3F0EB;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #F7F5F0; }
.notif-item.no-leida { background: #FDF6EC; }
.notif-item.no-leida:hover { background: #FAF0DE; }

.notif-item-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.05rem;
    position: relative;
}
.notif-item-icon.comunicado { background: linear-gradient(135deg, #E8F0E5, #D5E3CF); color: #56704C; }
.notif-item-icon.pago       { background: linear-gradient(135deg, #E5EDF5, #CFDEED); color: #4F7796; }
.notif-item-icon.reserva    { background: linear-gradient(135deg, #F5E5F0, #EACFDF); color: #96567C; }
.notif-item-icon.incidencia { background: linear-gradient(135deg, #F5E5E3, #EDCFCB); color: #A34F45; }
.notif-item-icon.proveedor  { background: linear-gradient(135deg, #F0EAE0, #E0D3BE); color: #7D6B56; }
.notif-item-icon.encuesta   { background: linear-gradient(135deg, #E5F0EF, #CFE3E1); color: #4F807B; }
.notif-item-icon.sistema    { background: linear-gradient(135deg, #F7F5F0, #E8E3DA); color: #A38F78; }

.notif-item-content {
    flex: 1;
    min-width: 0;
}
.notif-item-title {
    font-size: 0.87rem;
    font-weight: 600;
    color: #36322E;
    margin-bottom: 2px;
    line-height: 1.3;
}
.no-leida .notif-item-title { font-weight: 700; }
.notif-item-text {
    font-size: 0.79rem;
    color: #7A7268;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.35;
}
.notif-item-time {
    font-size: 0.71rem;
    color: #A38F78;
    margin-top: 4px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.notif-item-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: #A38F78;
    position: absolute;
    top: 16px;
    right: 16px;
    box-shadow: 0 0 0 3px rgba(163, 143, 120, 0.18);
}
.notif-item-actions {
    opacity: 0;
    transition: opacity 0.15s;
    flex-shrink: 0;
    margin-top: 2px;
}
.notif-item:hover .notif-item-actions { opacity: 1; }
.notif-item-actions button {
    background: none;
    border: none;
    cursor: pointer;
    color: #A38F78;
    font-size: 0.78rem;
    padding: 4px 6px;
    border-radius: 5px;
}
.notif-item-actions button:hover {
    background: #EDE9E3;
    color: #8B3A31;
}
.notif-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 20px;
    color: #A38F78;
    text-align: center;
}
.notif-empty i { font-size: 2.4rem; margin-bottom: 12px; opacity: 0.45; }
.notif-empty p { font-size: 0.88rem; margin: 0; }
.notif-empty small { font-size: 0.76rem; color: #B5AC9F; margin-top: 4px; }

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
                <span class="nav-text">Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>

<!-- Campana: panel flotante estilo Facebook -->
<div id="notifBackdrop" class="notif-backdrop"></div>
<div id="notifDropdown" class="notif-dropdown" role="dialog" aria-label="Notificaciones">
    <div class="notif-dropdown-header">
        <h3><i class="fa-solid fa-bell" style="color: #A38F78;"></i> Notificaciones
            <span id="notifCountPill" class="notif-count-pill">0</span>
        </h3>
        <button class="notif-mark-all" onclick="notifMarcarTodas()" title="Marcar todas como leidas">
            <i class="fa-solid fa-check-double"></i> Marcar todo como leído
        </button>
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
    var backdrop = document.getElementById('notifBackdrop');
    var badge = document.getElementById('notifBadge');
    var pill = document.getElementById('notifCountPill');
    var body = document.getElementById('notifDropdownBody');
    var open = false;
    var ultimoTotal = null;

    function setContador(total) {
        if (total > 0) {
            badge.textContent = total > 99 ? '99+' : total;
            badge.classList.add('visible');
            pill.textContent = total > 99 ? '99+' : total;
            pill.classList.add('visible');
        } else {
            badge.classList.remove('visible');
            pill.classList.remove('visible');
        }
    }

    function notifTimeAgo(dateStr) {
        var d = new Date(dateStr.replace(' ', 'T') + (dateStr.indexOf('+') === -1 && dateStr.indexOf('Z') === -1 ? '+00:00' : ''));
        var now = new Date();
        var diff = Math.floor((now - d) / 1000);
        if (diff < 60) return 'Hace un momento';
        if (diff < 3600) return 'Hace ' + Math.floor(diff / 60) + ' min';
        if (diff < 86400) return 'Hace ' + Math.floor(diff / 3600) + ' h';
        if (diff < 604800) return 'Hace ' + Math.floor(diff / 86400) + ' dia' + (Math.floor(diff / 86400) > 1 ? 's' : '');
        return d.toLocaleDateString('es-EC', {day: 'numeric', month: 'short'});
    }

    function notifIcon(tipo) {
        var icons = {
            'COMUNICADO': '<i class="fa-solid fa-bullhorn"></i>',
            'PAGO': '<i class="fa-solid fa-file-invoice-dollar"></i>',
            'RESERVA': '<i class="fa-solid fa-calendar-check"></i>',
            'INCIDENCIA': '<i class="fa-solid fa-triangle-exclamation"></i>',
            'PROVEEDOR': '<i class="fa-solid fa-truck-field"></i>',
            'ENCUESTA': '<i class="fa-solid fa-square-poll-vertical"></i>',
            'SISTEMA': '<i class="fa-solid fa-gear"></i>'
        };
        return icons[tipo] || '<i class="fa-solid fa-bell"></i>';
    }

    function notifClass(tipo) {
        var cls = {'COMUNICADO':'comunicado','PAGO':'pago','RESERVA':'reserva','INCIDENCIA':'incidencia','PROVEEDOR':'proveedor','ENCUESTA':'encuesta','SISTEMA':'sistema'};
        return cls[tipo] || 'sistema';
    }

    function notifUrl(refTipo, refId) {
        // Mapa segun el rol de quien ve la notificacion
        var esGestion = <?= in_array(strtoupper($rol), ['ADMINISTRADOR', 'DIRECTIVA']) ? 'true' : 'false' ?>;
        var map;
        if (esGestion) {
            map = {
                'comunicado': '<?= strtoupper($rol) === 'ADMINISTRADOR' ? 'administrador/comunicados' : 'directiva/dashboard' ?>',
                'pago': 'administrador/verificar_pagos',
                'reserva': 'directiva/dashboard',
                'incidencia': 'administrador/incidencias',
                'proveedor': '<?= strtoupper($rol) === 'ADMINISTRADOR' ? 'administrador/proveedores' : 'directiva/proveedores' ?>',
                'encuesta': '<?= strtoupper($rol) === 'ADMINISTRADOR' ? 'administrador/encuestas' : 'directiva/encuestas' ?>'
            };
        } else {
            map = {
                'comunicado': 'residente/dashboard',
                'pago': 'residente/reporte_pagos',
                'reserva': 'residente/reservas',
                'incidencia': 'residente/reportar_danos',
                'encuesta': 'residente/encuestas'
            };
        }
        var path = map[refTipo] || '';
        if (path) return '../' + path + '.php';
        return null;
    }

    function renderNotifs(data) {
        setContador(data.total_no_leidas);

        if (!data.notificaciones || data.notificaciones.length === 0) {
            body.innerHTML = '<div class="notif-empty"><i class="fa-solid fa-bell-slash"></i><p>No tienes notificaciones</p><small>Aquí verás los avisos del conjunto</small></div>';
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
            html += '    <div class="notif-item-time"><i class="fa-regular fa-clock"></i> ' + notifTimeAgo(n.fecha_creacion) + '</div>';
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
        fetch('../../api/notificaciones.php?action=list')
            .then(function(r) { return r.json(); })
            .then(function(data) { renderNotifs(data); })
            .catch(function() { body.innerHTML = '<div class="notif-empty"><i class="fa-solid fa-exclamation-triangle"></i><p>Error al cargar notificaciones</p></div>'; });
    }

    // Polling ligero: solo cuenta no leidas. Si aumentan, avisa con animacion.
    function fetchCount() {
        fetch('../../api/notificaciones.php?action=count')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var t = data.total_no_leidas;
                if (ultimoTotal !== null && t > ultimoTotal) {
                    bell.classList.remove('ringing');
                    void bell.offsetWidth; /* reinicia animacion */
                    bell.classList.add('ringing');
                    badge.classList.remove('pulse');
                    void badge.offsetWidth;
                    badge.classList.add('pulse');
                    if (open) fetchNotifs();
                }
                ultimoTotal = t;
                setContador(t);
            })
            .catch(function(){});
    }

    function abrirPanel() {
        open = true;
        dropdown.classList.add('open');
        backdrop.classList.add('open');
        fetchNotifs();
    }

    function cerrarPanel() {
        open = false;
        dropdown.classList.remove('open');
        backdrop.classList.remove('open');
    }

    bell.addEventListener('click', function(e) {
        e.stopPropagation();
        if (open) cerrarPanel(); else abrirPanel();
    });

    backdrop.addEventListener('click', cerrarPanel);
    dropdown.addEventListener('click', function(e) { e.stopPropagation(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') cerrarPanel(); });

    // Tiempo real por polling corto (15s): compatible con hosting compartido.
    setInterval(fetchCount, 15000);
    fetchCount();

    // Expose globals
    window.notifClick = function(url, id) {
        notifMarcarUna(id);
        window.location.href = url;
    };

    window.notifMarcarUna = function(id) {
        fetch('../../api/notificaciones.php?action=marcar_leida&id_notificacion=' + id, {method:'POST'})
            .then(function(r){return r.json();})
            .then(function(d){
                ultimoTotal = d.total_no_leidas !== undefined ? d.total_no_leidas : ultimoTotal;
                fetchNotifs();
            });
    };

    window.notifEliminar = function(id) {
        fetch('../../api/notificaciones.php?action=eliminar&id_notificacion=' + id, {method:'POST'})
            .then(function(r){return r.json();})
            .then(function(d){
                ultimoTotal = d.total_no_leidas !== undefined ? d.total_no_leidas : ultimoTotal;
                fetchNotifs();
            });
    };

    window.notifMarcarTodas = function() {
        fetch('../../api/notificaciones.php?action=marcar_todas', {method:'POST'})
            .then(function(r){return r.json();})
            .then(function(){
                ultimoTotal = 0;
                setContador(0);
                fetchNotifs();
            });
    };
})();
</script>

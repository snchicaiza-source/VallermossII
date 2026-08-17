<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../models/Pago.php';
require_once __DIR__ . '/../../models/Comunicado.php';

verificarRol(['RESIDENTE']);

$id_usuario = $_SESSION['id_usuario'];

$pagoModel = new Pago();
$misPagos = $pagoModel->obtenerPorUsuario($id_usuario);

$comunicadoModel = new Comunicado();
$comunicados = $comunicadoModel->obtenerTodos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Residente - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="app-layout">
    <!-- Sidebar Residente -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">Vallermosso II</h2>
            <span class="user-badge"><i class="fa-solid fa-house-user"></i> <?= htmlspecialchars($_SESSION['usuario_nombres']) ?></span>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-chart-line"></i> <span>Mi Panel</span></a>
            </li>
            <li class="nav-item logout-section">
                <form action="../../controllers/AuthController.php" method="POST">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-danger btn-block"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</button>
                </form>
            </li>
        </ul>
    </aside>

    <!-- Contenido Principal -->
    <main class="main-content">
        <header class="content-header">
            <h1>Bienvenido(a), <?= htmlspecialchars($_SESSION['usuario_nombres']) ?></h1>
            <p class="subtitle">Consulta los avisos del condominio y reporta tus comprobantes de alícuotas.</p>
        </header>

        <!-- Mensajes Flash -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Sección Subir Comprobante -->
        <section class="card form-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-upload"></i> Reportar Pago de Alícuota</h2>
            </div>
            <div class="card-body">
                <form action="../../controllers/ResidenteController.php" method="POST" enctype="multipart/form-data" class="grid-form">
                    <input type="hidden" name="action" value="subir_pago">

                    <div class="form-group">
                        <label for="monto">Monto ($)</label>
                        <input type="number" step="0.01" id="monto" name="monto" class="form-control" placeholder="0.00" required>
                    </div>

                    <div class="form-group">
                        <label for="concepto">Concepto / Mes</label>
                        <input type="text" id="concepto" name="concepto" class="form-control" placeholder="Ej. Alícuota Agosto 2026" required>
                    </div>

                    <div class="form-group span-full">
                        <label for="comprobante">Comprobante (Imagen o PDF)</label>
                        <input type="file" id="comprobante" name="comprobante" class="form-control" accept="image/*,application/pdf" required>
                    </div>

                    <div class="form-actions span-full">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-cloud-arrow-up"></i> Registrar Pago</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Historial de Pagos del Residente -->
        <section class="card table-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-history"></i> Mis Pagos Registrados</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($misPagos)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No has registrado transferencias.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($misPagos as $p): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($p['fecha_registro'])) ?></td>
                                        <td><?= htmlspecialchars($p['concepto']) ?></td>
                                        <td><strong>$<?= number_format($p['monto'], 2) ?></strong></td>
                                        <td>
                                            <?php if ($p['estado'] === 'APROBADO'): ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> APROBADO</span>
                                            <?php elseif ($p['estado'] === 'RECHAZADO'): ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> RECHAZADO</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning"><i class="fa-solid fa-clock"></i> PENDIENTE</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>

</body>
</html>
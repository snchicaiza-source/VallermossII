<?php
session_start();
require_once __DIR__ . '/../../config/auth_middleware.php';
require_once __DIR__ . '/../../config/db.php';

verificarRol(['RESIDENTE']);

$id_usuario = $_SESSION['id_usuario'];
$pdo = Database::obtenerConexion();

$stmt = $pdo->query("SELECT * FROM encuestas WHERE activa = 1 ORDER BY fecha_creacion DESC");
$encuestas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$votosUsuario = [];
$stmtV = $pdo->prepare("SELECT id_encuesta, respuesta FROM encuestas_votos WHERE id_usuario = :id");
$stmtV->execute([':id' => $id_usuario]);
while ($v = $stmtV->fetch(PDO::FETCH_ASSOC)) {
    $votosUsuario[$v['id_encuesta']] = $v['respuesta'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuestas - Vallermosso II</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="app-layout">
    <?php include_once __DIR__ . '/../sidebar.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1><i class="fa-solid fa-square-poll-vertical"></i> Encuestas</h1>
            <p class="subtitle">Participa en las encuestas del condominio.</p>
        </header>


        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <?php if (empty($encuestas)): ?>
            <section class="card">
                <div class="card-body text-center" style="padding: 40px;">
                    <i class="fa-solid fa-square-poll-vertical" style="font-size: 3rem; color: var(--text-muted);"></i>
                    <p style="margin-top: 16px; color: var(--text-muted);">No hay encuestas activas en este momento.</p>
                </div>
            </section>
        <?php endif; ?>

        <?php foreach ($encuestas as $enc): ?>
            <?php
                $opciones = json_decode($enc['opciones'], true) ?? [];
                $yaVoto = isset($votosUsuario[$enc['id']]);
                $respuestaVotada = $votosUsuario[$enc['id']] ?? null;
            ?>
            <section class="card">
                <div class="card-header">
                    <h2><i class="fa-solid fa-square-poll-vertical"></i> <?= htmlspecialchars($enc['titulo']) ?></h2>
                    <small class="text-muted">Creada: <?= date('d/m/Y', strtotime($enc['fecha_creacion'])) ?>
                        <?php if (!empty($enc['fecha_cierre'])): ?> | Cierra: <?= date('d/m/Y', strtotime($enc['fecha_cierre'])) ?><?php endif; ?>
                    </small>
                </div>
                <div class="card-body">
                    <?php if (!empty($enc['descripcion'])): ?>
                        <p style="margin-bottom: 16px;"><?= htmlspecialchars($enc['descripcion']) ?></p>
                    <?php endif; ?>

                    <?php if ($yaVoto): ?>
                        <div class="alert alert-success">
                            <i class="fa-solid fa-circle-check"></i> Ya votaste. Tu respuesta: <strong><?= htmlspecialchars($respuestaVotada) ?></strong>
                        </div>
                    <?php else: ?>
                        <form action="../../controllers/ResidenteController.php" method="POST">
                            <input type="hidden" name="action" value="votar_encuesta">
                            <input type="hidden" name="id_encuesta" value="<?= $enc['id'] ?>">

                            <?php foreach ($opciones as $i => $opcion): ?>
                                <div style="padding: 10px 0; border-bottom: 1px solid var(--secondary);">
                                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                        <input type="radio" name="respuesta" value="<?= htmlspecialchars($opcion) ?>" required>
                                        <span><?= htmlspecialchars($opcion) ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>

                            <div style="margin-top: 16px;">
                                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check-to-slot"></i> Votar</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </main>
</div>
<script src="../../public/js/sidebar.js"></script>
</body>
</html>

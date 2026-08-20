<?php
// views/partials/notificaciones_panel.php
// Panel de notificaciones de comunicados - incluir en cada modulo
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rol = $_SESSION['rol'] ?? $_SESSION['usuario_rol'] ?? '';
if (!class_exists('Database')) {
    require_once __DIR__ . '/../../config/db.php';
}

$pdo = Database::obtenerConexion();

$ultimosComunicados = $pdo->query("SELECT c.id_comunicado, c.titulo, c.mensaje, c.canal, c.fecha_envio, u.nombres as autor FROM comunicados c LEFT JOIN usuarios u ON c.enviado_por = u.id_usuario ORDER BY c.fecha_envio DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);

$totalComunicados = $pdo->query("SELECT COUNT(*) FROM comunicados")->fetchColumn();
$totalNotificacionesLog = $pdo->query("SELECT COUNT(*) FROM notificaciones_log WHERE estado = 'FALLIDO'")->fetchColumn();
?>

<div style="background: #FDF5E6; border: 1px solid #F0DEB0; border-radius: 10px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <i class="fa-solid fa-bell" style="font-size: 1.3rem; color: #D4A84B;"></i>
        <div>
            <strong style="color: #36322E; font-size: 0.9rem;">Notificaciones</strong>
            <span style="color: #7A7268; font-size: 0.82rem; margin-left: 8px;">
                <?= $totalComunicados ?> comunicado<?= $totalComunicados != 1 ? 's' : '' ?> publicado<?= $totalComunicados != 1 ? 's' : '' ?>
                <?php if ($totalNotificacionesLog > 0): ?>
                    | <span style="color: #B86B61;"><?= $totalNotificacionesLog ?> sin enviar</span>
                <?php endif; ?>
            </span>
        </div>
    </div>
</div>

<?php if (!empty($ultimosComunicados)): ?>
<section class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-bullhorn"></i> Comunicados Recientes</h2>
    </div>
    <div class="card-body">
        <?php foreach ($ultimosComunicados as $c): ?>
            <div style="display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #EDE9E3;">
                <div style="flex-shrink: 0; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; background: #F7F5F0; color: #A38F78;">
                    <?php if ($c['canal'] === 'WHATSAPP'): ?>
                        <i class="fa-brands fa-whatsapp"></i>
                    <?php elseif ($c['canal'] === 'EMAIL'): ?>
                        <i class="fa-solid fa-envelope"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-bullhorn"></i>
                    <?php endif; ?>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <strong style="color: #36322E; font-size: 0.88rem;"><?= htmlspecialchars($c['titulo']) ?></strong>
                        <small style="color: #7A7268; white-space: nowrap; margin-left: 8px;"><?= date('d/m/Y H:i', strtotime($c['fecha_envio'])) ?></small>
                    </div>
                    <p style="margin: 4px 0 0; color: #7A7268; font-size: 0.82rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= htmlspecialchars(substr($c['mensaje'], 0, 120)) ?><?= strlen($c['mensaje']) > 120 ? '...' : '' ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

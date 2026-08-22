<?php
// views/partials/paginacion.php
// Parcial reutilizable de paginacion SERVER-SIDE.
// Variables esperadas:
//   $total      -> total de registros (int)
//   $pagina     -> pagina actual (int)
//   $porPagina  -> registros por pagina (int)
//   $ancla      -> (opcional) id de ancla para volver a la tabla, ej. 'listado'
$totalPaginas = max(1, (int)ceil($total / $porPagina));
if ($pagina > $totalPaginas) $pagina = $totalPaginas;

function paginacionUrl($numPagina, $ancla = null) {
    $params = $_GET;
    if ($numPagina <= 1) {
        unset($params['pagina']);
    } else {
        $params['pagina'] = $numPagina;
    }
    $query = http_build_query($params);
    return basename($_SERVER['SCRIPT_NAME']) . ($query ? '?' . $query : '') . ($ancla ? '#' . $ancla : '');
}

$desde = $total > 0 ? (($pagina - 1) * $porPagina + 1) : 0;
$hasta = min($pagina * $porPagina, $total);
?>
<div class="paginacion">
    <span class="tabla-info">Mostrando <?= $desde ?> a <?= $hasta ?> de <?= $total ?> registro(s)</span>
    <div class="paginacion-botones">
        <?php if ($pagina > 1): ?>
            <a class="pag-btn" href="<?= paginacionUrl($pagina - 1, $ancla ?? null) ?>">&laquo;</a>
        <?php else: ?>
            <button class="pag-btn" disabled>&laquo;</button>
        <?php endif; ?>

        <?php
        // Ventana de paginas: 1 ... p-1 p p+1 ... ultima
        $rango = [];
        for ($i = 1; $i <= $totalPaginas; $i++) {
            if ($i === 1 || $i === $totalPaginas || abs($i - $pagina) <= 1) {
                $rango[] = $i;
            }
        }
        $ultimo = 0;
        foreach ($rango as $p):
            if ($p - $ultimo > 1): ?><span class="pag-puntos">&hellip;</span><?php endif;
            $ultimo = $p;
            if ($p === $pagina): ?>
                <span class="pag-btn activa"><?= $p ?></span>
            <?php else: ?>
                <a class="pag-btn" href="<?= paginacionUrl($p, $ancla ?? null) ?>"><?= $p ?></a>
            <?php endif;
        endforeach; ?>

        <?php if ($pagina < $totalPaginas): ?>
            <a class="pag-btn" href="<?= paginacionUrl($pagina + 1, $ancla ?? null) ?>">&raquo;</a>
        <?php else: ?>
            <button class="pag-btn" disabled>&raquo;</button>
        <?php endif; ?>
    </div>
</div>

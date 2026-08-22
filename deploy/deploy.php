<?php
$source = __DIR__;
$deploy = __DIR__ . '/deploy';

echo "=== Preparando despliegue para InfinityFree ===\n\n";

if (is_dir($deploy)) {
    echo "Limpiando carpeta deploy/ anterior...\n";
    rrmdir($deploy);
}
mkdir($deploy, 0755, true);

$skip = ['.', '..', '.git', 'deploy', '_export.php', '_check.php', '_fix.php'];
$items = array_diff(scandir($source), $skip);

foreach ($items as $item) {
    $fullPath = $source . '/' . $item;
    if (is_dir($fullPath)) {
        copyDir($fullPath, $deploy . '/' . $item);
    } else {
        copy($fullPath, $deploy . '/' . $item);
    }
}

if (file_exists($deploy . '/config/db.production.php')) {
    if (file_exists($deploy . '/config/db.php')) unlink($deploy . '/config/db.php');
    rename($deploy . '/config/db.production.php', $deploy . '/config/db.php');
    echo "[OK] config/db.php reemplazado con credenciales de InfinityFree\n";
}

if (file_exists($source . '/vallermosso2_db.sql')) {
    copy($source . '/vallermosso2_db.sql', $deploy . '/vallermosso2_db.sql');
    echo "[OK] vallermosso2_db.sql incluido\n";
}

echo "\nDespliegue listo en: deploy/\n";

function copyDir($src, $dst) {
    @mkdir($dst, 0755, true);
    foreach (array_diff(scandir($src), ['.', '..']) as $item) {
        $s = $src . '/' . $item;
        $d = $dst . '/' . $item;
        is_dir($s) ? copyDir($s, $d) : copy($s, $d);
    }
}

function rrmdir($dir) {
    if (is_dir($dir)) {
        foreach (array_diff(scandir($dir), ['.', '..']) as $item) {
            $path = $dir . '/' . $item;
            is_dir($path) ? rrmdir($path) : unlink($path);
        }
        rmdir($dir);
    }
}

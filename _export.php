<?php
$pdo = new PDO('mysql:host=localhost;dbname=vallermosso2_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$sql = "-- Vallermosso II - Database Export\n";
$sql .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($tables as $table) {
    $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    $sql .= "DROP TABLE IF EXISTS `$table`;\n" . $create['Create Table'] . ";\n\n";
    $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
        $cols = array_keys($rows[0]);
        foreach ($rows as $row) {
            $vals = [];
            foreach ($row as $v) { $vals[] = $v === null ? 'NULL' : $pdo->quote($v); }
            $sql .= "INSERT INTO `$table` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $vals) . ");\n";
        }
        $sql .= "\n";
    }
}
$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
file_put_contents(__DIR__ . '/vallermosso2_db.sql', $sql);
echo "Exported " . count($tables) . " tables\n";

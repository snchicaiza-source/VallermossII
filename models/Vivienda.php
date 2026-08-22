<?php
// models/Vivienda.php
// Catalogo fijo de viviendas del conjunto. Los usuarios se asignan a una vivienda del catalogo.
// Relacion: 1 vivienda -> N usuarios (la columna usuarios.numero_vivienda guarda el codigo).
require_once __DIR__ . '/../config/db.php';

class Vivienda {

    private static $tablaLista = false;

    public static function asegurarTabla() {
        if (self::$tablaLista) return;
        try {
            $pdo = Database::obtenerConexion();
            $pdo->exec("CREATE TABLE IF NOT EXISTS viviendas (
                id_vivienda INT AUTO_INCREMENT PRIMARY KEY,
                codigo VARCHAR(30) NOT NULL UNIQUE,
                activa TINYINT(1) NOT NULL DEFAULT 1,
                creada_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Siembra inicial: importa las viviendas ya escritas en usuarios
            $total = (int)$pdo->query("SELECT COUNT(*) FROM viviendas")->fetchColumn();
            if ($total === 0) {
                $pdo->exec("INSERT INTO viviendas (codigo)
                            SELECT DISTINCT TRIM(numero_vivienda) FROM usuarios
                            WHERE numero_vivienda IS NOT NULL AND TRIM(numero_vivienda) != ''
                            ON DUPLICATE KEY UPDATE codigo = VALUES(codigo)");
            }
            self::$tablaLista = true;
        } catch (PDOException $e) {
            error_log('[viviendas] ' . $e->getMessage());
        }
    }

    public static function obtenerTodas($soloActivas = true) {
        self::asegurarTabla();
        try {
            $pdo = Database::obtenerConexion();
            $sql = "SELECT * FROM viviendas" . ($soloActivas ? " WHERE activa = 1" : "") . " ORDER BY codigo ASC";
            return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function crear($codigo) {
        self::asegurarTabla();
        $codigo = trim($codigo);
        if ($codigo === '' || mb_strlen($codigo) > 30) {
            return ['ok' => false, 'error' => 'El codigo de vivienda es obligatorio (maximo 30 caracteres).'];
        }
        try {
            $pdo = Database::obtenerConexion();
            $existe = $pdo->prepare("SELECT id_vivienda FROM viviendas WHERE codigo = :c LIMIT 1");
            $existe->execute([':c' => $codigo]);
            if ($existe->fetch()) {
                return ['ok' => false, 'error' => "La vivienda \"{$codigo}\" ya existe en el catálogo."];
            }
            $stmt = $pdo->prepare("INSERT INTO viviendas (codigo, activa) VALUES (:c, 1)");
            $stmt->execute([':c' => $codigo]);
            return ['ok' => true];
        } catch (PDOException $e) {
            return ['ok' => false, 'error' => 'No se pudo crear la vivienda.'];
        }
    }

    public static function cambiarEstado($id, $activa) {
        self::asegurarTabla();
        try {
            $pdo = Database::obtenerConexion();
            $pdo->prepare("UPDATE viviendas SET activa = :a WHERE id_vivienda = :i")
                ->execute([':a' => $activa ? 1 : 0, ':i' => (int)$id]);
            return ['ok' => true];
        } catch (PDOException $e) {
            return ['ok' => false, 'error' => 'No se pudo actualizar la vivienda.'];
        }
    }

    public static function eliminar($id) {
        self::asegurarTabla();
        try {
            $pdo = Database::obtenerConexion();

            // No permite eliminar si hay usuarios asignados a esa vivienda
            $q = $pdo->prepare("SELECT codigo FROM viviendas WHERE id_vivienda = :i");
            $q->execute([':i' => (int)$id]);
            $codigo = (string)$q->fetchColumn();
            if ($codigo === '') {
                return ['ok' => false, 'error' => 'La vivienda no existe.'];
            }

            $u = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE TRIM(numero_vivienda) = :c");
            $u->execute([':c' => $codigo]);
            if ((int)$u->fetchColumn() > 0) {
                return ['ok' => false, 'error' => "No se puede eliminar \"{$codigo}\" porque tiene usuarios asignados. Desactívala en su lugar."];
            }

            $pdo->prepare("DELETE FROM viviendas WHERE id_vivienda = :i")->execute([':i' => (int)$id]);
            return ['ok' => true];
        } catch (PDOException $e) {
            return ['ok' => false, 'error' => 'No se pudo eliminar la vivienda.'];
        }
    }
}

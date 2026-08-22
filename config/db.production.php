<?php
// config/db.php - PRODUCCION (InfinityFree)
// Reemplaza el db.php original al subir al hosting

class Database {
    private static $host = 'sql306.infinityfree.com';
    private static $db_name = 'if0_42707973_vallermosso2_db';
    private static $username = 'if0_42707973';
    private static $password = 'gFl5DZC8KMgO';
    private static $conn = null;

    public static function obtenerConexion() {
        if (self::$conn === null) {
            try {
                self::$conn = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4",
                    self::$username,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("Error de conexion a la base de datos: " . $e->getMessage());
            }
        }
        return self::$conn;
    }

    public static function conectar() {
        return self::obtenerConexion();
    }

    public static function connect() {
        return self::obtenerConexion();
    }
}

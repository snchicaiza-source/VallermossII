<?php
// config/db.php
class Database {
    private static $host = 'localhost';
    private static $db   = 'vallermosso2_db';
    private static $user = 'root';
    private static $pass = ''; // Por defecto en XAMPP es vacío
    private static $pdo  = null;

    public static function connect() {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=utf8mb4",
                    self::$user,
                    self::$pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                die("Error crítico de conexión a la base de datos: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
?>
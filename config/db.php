<?php

class Database {
    private static $host = 'localhost';
    private static $db_name = 'vallermosso2_db';
    private static $username = 'root';
    private static $password = '';
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
                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }
        return self::$conn;
    }

    // Alias por si usas conectar() o connect() en otras partes del código
    public static function conectar() {
        return self::obtenerConexion();
    }

    public static function connect() {
        return self::obtenerConexion();
    }
}
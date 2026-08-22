<?php

class Database {
    // Detecta el entorno: InfinityFree (produccion) o XAMPP local
    private static $esProduccion = false;

    private static function configurar() {
        $host_actual = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        if (strpos($host_actual, 'infinityfreeapp.com') !== false || strpos($host_actual, 'infinityfree.com') !== false) {
            self::$esProduccion = true;
        }
    }

    private static $conn = null;

    public static function obtenerConexion() {
        if (self::$conn === null) {
            self::configurar();

            if (self::$esProduccion) {
                $host = 'sql309.infinityfree.com';
                $db_name = 'if0_42705605_vallermosso2';
                $username = 'if0_42705605';
                $password = 'kfKRCnwWue';
            } else {
                $host = 'localhost';
                $db_name = 'vallermosso2_db';
                $username = 'root';
                $password = '';
            }

            try {
                self::$conn = new PDO(
                    "mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8mb4",
                    $username,
                    $password,
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
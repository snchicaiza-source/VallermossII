<?php
require_once __DIR__ . '/../config/db.php';

class Comunicado {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::obtenerConexion();
    }

    public function crear($titulo, $contenido, $prioridad, $id_usuario) {
        // Verificar dinámicamente qué columnas existen en la tabla
        $columnas = $this->obtenerColumnas();
        
        $campos = ['titulo' => $titulo, 'contenido' => $contenido];
        if (in_array('prioridad', $columnas)) $campos['prioridad'] = $prioridad;
        if (in_array('id_usuario', $columnas)) $campos['id_usuario'] = $id_usuario;

        $cols = implode(', ', array_keys($campos));
        $params = ':' . implode(', :', array_keys($campos));

        $stmt = $this->pdo->prepare("INSERT INTO comunicados ({$cols}) VALUES ({$params})");
        return $stmt->execute($campos);
    }

    public function obtenerTodos() {
        $columnas = $this->obtenerColumnas();
        
        // Detectar cuál columna usar para ordenar
        $columnaOrden = '1';
        foreach (['fecha_publicacion', 'fecha', 'created_at', 'id_comunicado'] as $col) {
            if (in_array($col, $columnas)) {
                $columnaOrden = $col;
                break;
            }
        }

        $stmt = $this->pdo->prepare("SELECT * FROM comunicados ORDER BY {$columnaOrden} DESC");
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Normalizar claves para la vista
        return array_map(function($item) {
            if (!isset($item['fecha_publicacion'])) {
                $item['fecha_publicacion'] = $item['fecha'] ?? $item['created_at'] ?? date('Y-m-d H:i:s');
            }
            if (!isset($item['prioridad'])) {
                $item['prioridad'] = 'INFORMATIVO';
            }
            if (!isset($item['publicado_por'])) {
                $item['publicado_por'] = 'Administración';
            }
            return $item;
        }, $resultados);
    }

    public function eliminar($id_comunicado) {
        $columnas = $this->obtenerColumnas();
        $idCol = in_array('id_comunicado', $columnas) ? 'id_comunicado' : 'id';

        $stmt = $this->pdo->prepare("DELETE FROM comunicados WHERE {$idCol} = :id");
        return $stmt->execute([':id' => $id_comunicado]);
    }

    private function obtenerColumnas() {
        try {
            $stmt = $this->pdo->query("DESCRIBE comunicados");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }
}
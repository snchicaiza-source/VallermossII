<?php
require_once __DIR__ . '/../config/db.php';

class Usuario {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::obtenerConexion();
    }

    // Busca únicamente en la columna real 'correo'
    public function obtenerPorCorreo($correo) {
        try {
            $sql = "SELECT * FROM usuarios WHERE correo = :correo LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':correo' => trim($correo)]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerTodos() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM usuarios ORDER BY id_usuario DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Listado paginado con busqueda opcional por nombre, cedula, correo o vivienda.
    // Devuelve ['datos' => [...], 'total' => int]
    public function obtenerPaginado($buscar = '', $pagina = 1, $porPagina = 10) {
        $porPagina = max(1, min(100, (int)$porPagina));
        $pagina = max(1, (int)$pagina);
        $offset = ($pagina - 1) * $porPagina;
        $buscar = trim((string)$buscar);

        try {
            if ($buscar !== '') {
                $like = '%' . $buscar . '%';
                $where = "WHERE nombres LIKE :b OR cedula LIKE :b OR correo LIKE :b OR numero_vivienda LIKE :b";
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios $where");
                $stmt->execute([':b' => $like]);
                $total = (int)$stmt->fetchColumn();

                $stmt = $this->pdo->prepare("SELECT * FROM usuarios $where ORDER BY id_usuario DESC LIMIT :limite OFFSET :offset");
                $stmt->bindValue(':b', $like);
                $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $total = (int)$this->pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
                $stmt = $this->pdo->prepare("SELECT * FROM usuarios ORDER BY id_usuario DESC LIMIT :limite OFFSET :offset");
                $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return ['datos' => $datos, 'total' => $total];
        } catch (PDOException $e) {
            return ['datos' => [], 'total' => 0];
        }
    }

    // Verifica si ya existe OTRO usuario con la misma cedula.
    public function existeCedula($cedula, $exceptoId = 0) {
        try {
            $stmt = $this->pdo->prepare("SELECT id_usuario FROM usuarios WHERE cedula = :c AND id_usuario != :id LIMIT 1");
            $stmt->execute([':c' => trim($cedula), ':id' => (int)$exceptoId]);
            return (bool)$stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerPorId($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = :id LIMIT 1");
            $stmt->execute([':id' => (int)$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Actualiza los datos editables del usuario. Si se envia password, tambien la cambia.
    public function actualizar($id, $datos) {
        try {
            $sql = "UPDATE usuarios SET cedula = :cedula, nombres = :nombres, correo = :correo,
                    telefono_whatsapp = :telefono, numero_vivienda = :vivienda, rol = :rol";
            $params = [
                ':cedula'   => $datos['cedula'],
                ':nombres'  => $datos['nombres'],
                ':correo'   => $datos['correo'],
                ':telefono' => $datos['telefono'],
                ':vivienda' => $datos['numero_vivienda'],
                ':rol'      => $datos['rol'],
                ':id'       => (int)$id
            ];
            if (!empty($datos['password'])) {
                $sql .= ", clave_hash = :clave";
                $params[':clave'] = password_hash($datos['password'], PASSWORD_BCRYPT);
            }
            $sql .= " WHERE id_usuario = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Cambia unicamente la clave del usuario (flujo de recuperacion de contrasena).
    public function actualizarClave($id, $password) {
        try {
            $stmt = $this->pdo->prepare("UPDATE usuarios SET clave_hash = :hash WHERE id_usuario = :id");
            return $stmt->execute([
                ':hash' => password_hash($password, PASSWORD_BCRYPT),
                ':id'   => (int)$id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Cambia el estado del usuario (ACTIVO / BLOQUEADO). Migra el enum si falta BLOQUEADO.
    public function cambiarEstado($id, $estado) {
        $estado = ($estado === 'BLOQUEADO') ? 'BLOQUEADO' : 'ACTIVO';
        try {
            // En modo SQL no estricto, guardar BLOQUEADO con el enum viejo no falla:
            // guarda cadena vacia. Se verifica y amplía el enum ANTES de actualizar.
            $col = $this->pdo->query("SHOW COLUMNS FROM usuarios LIKE 'estado'")->fetch(PDO::FETCH_ASSOC);
            if ($col && strpos(strtoupper($col['Type']), 'BLOQUEADO') === false) {
                $this->pdo->exec("ALTER TABLE usuarios MODIFY estado ENUM('ACTIVO','INACTIVO','BLOQUEADO') DEFAULT 'ACTIVO'");
            }

            $stmt = $this->pdo->prepare("UPDATE usuarios SET estado = :estado WHERE id_usuario = :id");
            $stmt->execute([':estado' => $estado, ':id' => (int)$id]);

            // Verifica que el cambio realmente aplico
            $actual = $this->pdo->prepare("SELECT estado FROM usuarios WHERE id_usuario = :id");
            $actual->execute([':id' => (int)$id]);
            return strtoupper((string)$actual->fetchColumn()) === $estado;
        } catch (PDOException $e) {
            return false;
        }
    }
}
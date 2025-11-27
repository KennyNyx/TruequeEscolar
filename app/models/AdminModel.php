<?php
class AdminModel {
    private PDO $db;
    private string $table = 'administradores';
    private string $pk = 'id_admin'; 

    /**
     * Inicializa la conexión PDO que usará el modelo para las consultas.
     */
    public function __construct(PDO $dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Obtener todos los administradores
     * Ejecuta una consulta SELECT * sobre la tabla de administradores.
     * Devuelve un array asociativo con todos los registros o un array vacío en caso de error.
     *
     * /Recibe: Nada.
     * /Devuelve: array (lista de administradores) o [] en error.
     */
    public function obtenerAdmins() {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AdminModel::obtenerAdmins error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un administrador por ID
     * Devuelve el registro del administrador si existe, o false si hay error o no existe.
     *
     * /Recibe: int $id
     * /Devuelve: array|false
     */
   public function obtenerAdmin(int $id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$this->pk} = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

   /**
     * Registrar un nuevo administrador
     * Valida campos mínimos, verifica que el correo no exista, hashea la contraseña
     * y crea el registro en la base de datos.
     *
     * /Recibe: array $data (nombre, correo, contrasena)
     * /Devuelve: int ID insertado en caso de éxito, false en caso de error
     */
    public function registrarAdmin(array $data) {
        try {
            if (empty($data['nombre']) || empty($data['correo']) || empty($data['contrasena'])) {
                return false;
            }
            if ($this->emailExists($data['correo'])) {
                return false;
            }
            $hash = password_hash($data['contrasena'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO {$this->table} (nombre, correo, contra) VALUES (:nombre, :correo, :contra)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nombre', $data['nombre']);
            $stmt->bindValue(':correo', $data['correo']);
            $stmt->bindValue(':contra', $hash);

            if ($stmt->execute()) {
                return (int)$this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualizar administrador
     * Actualiza nombre, correo y opcionalmente la contraseña. Verifica que el correo
     * no pertenezca a otro registro distinto al ID proporcionado.
     *
     * /Recibe: array $data (id, nombre, correo, contrasena [opcional])
     * /Devuelve: bool
     */
    public function actualizarAdmin(array $data): bool {
        try {
            $passwordSQL = "";
            if (!empty($data['contrasena'])) {
                $passwordSQL = ", contra = :contra";
            }

            if ($this->emailExists($data['correo'], $data['id'])) {
                return false;
            }

            $sql = "UPDATE {$this->table} SET nombre = :nombre, correo = :correo $passwordSQL WHERE {$this->pk} = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nombre', $data['nombre']);
            $stmt->bindValue(':correo', $data['correo']);
            $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);
            
            if (!empty($data['contrasena'])) {
                $hash = password_hash($data['contrasena'], PASSWORD_DEFAULT);
                $stmt->bindValue(':contra', $hash);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Eliminar un administrador por ID
     * Ejecuta una consulta DELETE para eliminar el registro.
     *
     * /Recibe: int $id
     * /Devuelve: bool
     */
    public function eliminarAdmin(int $id): bool {
        try {
            $sql = "DELETE FROM {$this->table} WHERE {$this->pk} = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Comprueba si existe un correo en la tabla
     * Permite opcionalmente excluir un ID para cuando se verifica en actualizaciones.
     *
     * /Recibe: string $correo, int|null $excludeId
     * /Devuelve: bool
     */
    private function emailExists(string $correo, int $excludeId = null): bool {
        try {
            $sql = "SELECT COUNT(1) FROM {$this->table} WHERE correo = :correo";
            if ($excludeId !== null) {
                $sql .= " AND {$this->pk} != :excludeId";
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':correo', $correo);
            if ($excludeId !== null) {
                $stmt->bindValue(':excludeId', $excludeId, PDO::PARAM_INT);
            }
            $stmt->execute();
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }
}
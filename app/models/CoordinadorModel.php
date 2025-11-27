<?php
class CoordinadorModel {
    private PDO $db;
    private string $table = 'coordinadores'; 
    private string $pk = 'id_coordi';   

    public function __construct(PDO $dbConnection) {
        $this->db = $dbConnection;
    }

    // --- NUEVO MÉTODO: Obtener todos los coordinadores ---
    public function obtenerCoordinadores() {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("CoordinadorModel::obtenerCoordinadores error: " . $e->getMessage());
            return [];
        }
    }

    // ... (Mantén tus métodos existentes: registrar, eliminar, emailExists, actualizar)

    public function registrarCoordinador(array $data) {
        try {
            if (empty($data['nombre']) || empty($data['correo']) || empty($data['contrasena'])) {
                return false;
            }
            if ($this->emailExists($data['correo'])) {
                return 'email_exists'; 
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

    public function actualizarCoordinador(array $data): bool {
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

    public function eliminarCoordinador(int $id): bool {
        try {
            $sql = "DELETE FROM {$this->table} WHERE {$this->pk} = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

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

    public function obtenerCoordinadorPorId($id) {
        try {
            $sql = "SELECT * FROM coordinadores WHERE id_coordi = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener coordinador: " . $e->getMessage());
            return false;
        }
    }
}
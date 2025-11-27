<?php
class ResenaModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Crea una nueva reseña (sin necesidad de reunión previa)
     * /Recibe: array $data con keys:
     *    id_reunion (nullable), id_alumno_resena, id_alumno_evaluado, objeto_evaluado,
     *    calificacion, comentario, imagenes (json o string)
     * /Devuelve: bool
     */
    public function crearResena($data) {
        try {
            $sql = "INSERT INTO resenas 
                    (id_reunion, id_alumno_resena, id_alumno_evaluado, objeto_evaluado, 
                     calificacion, comentario, imagenes) 
                    VALUES 
                    (:id_reunion, :id_alumno_resena, :id_alumno_evaluado, :objeto_evaluado,
                     :calificacion, :comentario, :imagenes)";
            
            $stmt = $this->db->prepare($sql);
            
            $id_reunion = $data['id_reunion'] ?? null;
            $stmt->bindParam(':id_reunion', $id_reunion, PDO::PARAM_INT);
            $stmt->bindParam(':id_alumno_resena', $data['id_alumno_resena'], PDO::PARAM_INT);
            $stmt->bindParam(':id_alumno_evaluado', $data['id_alumno_evaluado'], PDO::PARAM_INT);
            $stmt->bindParam(':objeto_evaluado', $data['objeto_evaluado']);
            $stmt->bindParam(':calificacion', $data['calificacion'], PDO::PARAM_INT);
            $stmt->bindParam(':comentario', $data['comentario']);
            $stmt->bindParam(':imagenes', $data['imagenes']);
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                error_log("Reseña creada exitosamente por alumno: {$data['id_alumno_resena']}");
            }
            
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al crear reseña: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una reseña existente
     *
     * /Recibe: array $data con keys: id_resena, objeto_evaluado, calificacion, comentario, imagenes
     * /Devuelve: bool
     */
    public function actualizarResena($data) {
        try {
            $sql = "UPDATE resenas 
                    SET objeto_evaluado = :objeto_evaluado,
                        calificacion = :calificacion,
                        comentario = :comentario,
                        imagenes = :imagenes,
                        editado = 1,
                        fecha_edicion = NOW()
                    WHERE id_resena = :id_resena";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':objeto_evaluado', $data['objeto_evaluado']);
            $stmt->bindParam(':calificacion', $data['calificacion'], PDO::PARAM_INT);
            $stmt->bindParam(':comentario', $data['comentario']);
            $stmt->bindParam(':imagenes', $data['imagenes']);
            $stmt->bindParam(':id_resena', $data['id_resena'], PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar reseña: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todas las reseñas NO eliminadas con datos del evaluador y evaluado
     *
     * /Devuelve: array
     */
    public function obtenerTodasResenas() {
        try {
            $sql = "SELECT res.*, 
                    ar.nombre AS nombre_resena, 
                    ar.carrera AS carrera_resena,
                    ae.nombre AS nombre_evaluado, 
                    ae.carrera AS carrera_evaluado,
                    ae.correo AS correo_evaluado
                    FROM resenas res
                    JOIN alumnos ar ON res.id_alumno_resena = ar.ID
                    JOIN alumnos ae ON res.id_alumno_evaluado = ae.ID
                    WHERE res.eliminado = 0
                    ORDER BY res.fecha_creacion DESC";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener todas las reseñas: " . $e->getMessage());
            return [];
        }
    }

   /**
     * Obtiene reseñas recibidas por un alumno
     *
     * /Recibe: int $id_alumno
     * /Devuelve: array
     */
    public function obtenerResenasPorAlumno($id_alumno) {
        try {
            $sql = "SELECT res.*, 
                    ar.nombre AS nombre_resena, 
                    ar.carrera AS carrera_resena
                    FROM resenas res
                    JOIN alumnos ar ON res.id_alumno_resena = ar.ID
                    WHERE res.id_alumno_evaluado = :id_alumno
                    AND res.eliminado = 0
                    ORDER BY res.fecha_creacion DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener reseñas del alumno: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene reseñas creadas por un alumno específico
     */
    public function obtenerResenasDelAlumno($id_alumno) {
        try {
            $sql = "SELECT res.*, 
                    ae.nombre AS nombre_evaluado, 
                    ae.carrera AS carrera_evaluado
                    FROM resenas res
                    JOIN alumnos ae ON res.id_alumno_evaluado = ae.ID
                    WHERE res.id_alumno_resena = :id_alumno
                    AND res.eliminado = 0
                    ORDER BY res.fecha_creacion DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener reseñas creadas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcula el promedio de calificación de un alumno
     * 
     *  /Devuelve: array ['promedio'=>float,'total'=>int]
     */
    public function obtenerPromedioCalificacion($id_alumno) {
        try {
            $sql = "SELECT AVG(calificacion) as promedio, COUNT(*) as total
                    FROM resenas
                    WHERE id_alumno_evaluado = :id_alumno
                    AND eliminado = 0";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener promedio: " . $e->getMessage());
            return ['promedio' => 0, 'total' => 0];
        }
    }

    /**
     * Busca un alumno por correo
     */
    public function buscarAlumnoPorCorreo($correo) {
        try {
            $sql = "SELECT ID, nombre, correo, carrera FROM alumnos WHERE correo = :correo LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar alumno: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene una reseña por su ID (incluye datos del evaluado)
     *
     * /Devuelve: array|false
     */
    public function obtenerResenaPorId($id_resena) {
        try {
            $sql = "SELECT res.*, 
                    ar.nombre AS nombre_resena,
                    ae.nombre AS nombre_evaluado,
                    ae.correo AS correo_evaluado
                    FROM resenas res
                    JOIN alumnos ar ON res.id_alumno_resena = ar.ID
                    JOIN alumnos ae ON res.id_alumno_evaluado = ae.ID
                    WHERE res.id_resena = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_resena, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener reseña: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Soft delete: marca reseña como eliminada por el propio alumno
     *
     * /Recibe: int $id_resena, int $id_alumno (quien elimina)
     * /Devuelve: bool
     */
    public function eliminarResena($id_resena, $id_alumno) {
        try {
            $sql = "UPDATE resenas 
                    SET eliminado = 1,
                        fecha_eliminacion = NOW(),
                        eliminado_por = :id_alumno
                    WHERE id_resena = :id 
                    AND id_alumno_resena = :alumno";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_resena, PDO::PARAM_INT);
            $stmt->bindParam(':alumno', $id_alumno, PDO::PARAM_INT);
            $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar reseña: " . $e->getMessage());
            return false;
        }
    }

     /**
     * Soft delete por coordinador (puede eliminar cualquier reseña)
     *
     * /Devuelve: bool
     */
    public function eliminarResenaCoordinador($id_resena) {
        try {
            $sql = "UPDATE resenas 
                    SET eliminado = 1,
                        fecha_eliminacion = NOW(),
                        eliminado_por = NULL
                    WHERE id_resena = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_resena, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar reseña (coordinador): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener reseñas eliminadas (para administradores)
     *
     * /Devuelve: array
     */
    public function obtenerResenasEliminadas() {
        try {
            $sql = "SELECT res.*, 
                    ar.nombre AS nombre_resena, 
                    ar.carrera AS carrera_resena,
                    ae.nombre AS nombre_evaluado, 
                    ae.carrera AS carrera_evaluado,
                    ae.correo AS correo_evaluado
                    FROM resenas res
                    JOIN alumnos ar ON res.id_alumno_resena = ar.ID
                    JOIN alumnos ae ON res.id_alumno_evaluado = ae.ID
                    WHERE res.eliminado = 1
                    ORDER BY res.fecha_eliminacion DESC";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener reseñas eliminadas: " . $e->getMessage());
            return [];
        }
    }
}
?>
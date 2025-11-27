<?php
class ReunionModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Crea una nueva reunión
     * Inserta una reunión con estado inicial pendiente para todos los roles.
     * Registra correos de creador y participante para facilitar búsquedas futuras.
     *
     * /Recibe: array $data con keys:
     *    id_creador, id_participante, correo_creador, correo_participante,
     *    objeto_creador, objeto_participante, lugar, fecha, hora, notas
     * /Devuelve: bool
     */
    public function crearReunion($data) {
        try {
            $sql = "INSERT INTO reuniones 
                    (id_alumno_creador, id_alumno_participante, correo_creador, correo_participante, 
                     objeto_creador, objeto_participante, lugar, fecha_reunion, hora_reunion, notas) 
                    VALUES 
                    (:id_creador, :id_participante, :correo_creador, :correo_participante, 
                     :objeto_creador, :objeto_participante, :lugar, :fecha, :hora, :notas)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_creador', $data['id_creador'], PDO::PARAM_INT);
            $stmt->bindParam(':id_participante', $data['id_participante'], PDO::PARAM_INT);
            $stmt->bindParam(':correo_creador', $data['correo_creador']);
            $stmt->bindParam(':correo_participante', $data['correo_participante']);
            $stmt->bindParam(':objeto_creador', $data['objeto_creador']);
            $stmt->bindParam(':objeto_participante', $data['objeto_participante']);
            $stmt->bindParam(':lugar', $data['lugar']);
            $stmt->bindParam(':fecha', $data['fecha']);
            $stmt->bindParam(':hora', $data['hora']);
            $stmt->bindParam(':notas', $data['notas']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al crear reunión: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene TODAS las reuniones (para coordinadores)
     * Muestra solo reuniones NO eliminadas (eliminado = 0).
     * Incluye información de ambos alumnos.
     *
     * /Recibe: Nada.
     * /Devuelve: array de reuniones o [] en error
     */
    public function obtenerTodasReuniones() {
        try {
            $sql = "SELECT r.*, 
                    ac.nombre AS nombre_creador, ac.carrera AS carrera_creador,
                    ap.nombre AS nombre_participante, ap.carrera AS carrera_participante
                    FROM reuniones r
                    JOIN alumnos ac ON r.id_alumno_creador = ac.ID
                    JOIN alumnos ap ON r.id_alumno_participante = ap.ID
                    WHERE r.eliminado = 0
                    ORDER BY r.fecha_reunion DESC, r.hora_reunion DESC";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener todas las reuniones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todas las reuniones donde el alumno participa (como creador o participante)
     * Solo muestra reuniones NO eliminadas (eliminado = 0).
     * Incluye información de ambos participantes.
     *
     * /Recibe: int $id_alumno
     * /Devuelve: array de reuniones o [] en error
     */
    public function obtenerReunionesPorAlumno($id_alumno) {
        try {
            $sql = "SELECT r.*, 
                    ac.nombre AS nombre_creador, ac.carrera AS carrera_creador,
                    ap.nombre AS nombre_participante, ap.carrera AS carrera_participante
                    FROM reuniones r
                    JOIN alumnos ac ON r.id_alumno_creador = ac.ID
                    JOIN alumnos ap ON r.id_alumno_participante = ap.ID
                    WHERE (r.id_alumno_creador = :id_alumno OR r.id_alumno_participante = :id_alumno)
                    AND r.eliminado = 0
                    ORDER BY r.fecha_reunion DESC, r.hora_reunion DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener reuniones: " . $e->getMessage());
            return [];
        }
    }

   /**
     * Actualiza el estado de confirmación según el rol del usuario
     * Roles soportados: 'creador', 'participante', 'coordinador'
     * Estados válidos: 'confirmado', 'cancelado'
     *
     * Después de actualizar, calcula automáticamente el estado general
     * en base a los tres estados individuales.
     *
     * /Recibe: int $id_reunion, string $rol, string $estado
     * /Devuelve: bool
     */
    public function actualizarEstado($id_reunion, $rol, $estado) {
        try {
  
            $columna = '';
            if ($rol === 'creador') {
                $columna = 'estado_creador';
            } elseif ($rol === 'participante') {
                $columna = 'estado_participante';
            } elseif ($rol === 'coordinador') {
                $columna = 'estado_coordinador';
            } else {
                return false;
            }

            $sql = "UPDATE reuniones SET $columna = :estado WHERE id_reunion = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id_reunion, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
         
                $this->actualizarEstadoGeneral($id_reunion);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error al actualizar estado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el estado general de la reunión basado en los estados individuales

     * - Si ALGUNO cancela => estado_general = 'cancelada'
     * - Si TODOS confirman => estado_general = 'confirmada'
     * - En otro caso => estado_general = 'pendiente'
     *
     * /Recibe: int $id_reunion
     * /Devuelve: void (actualiza internamente)
     */
    private function actualizarEstadoGeneral($id_reunion) {
        try {
           
            $sql = "SELECT estado_creador, estado_participante, estado_coordinador 
                    FROM reuniones WHERE id_reunion = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_reunion, PDO::PARAM_INT);
            $stmt->execute();
            $estados = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$estados) return;

            $nuevoEstado = 'pendiente';

            if ($estados['estado_creador'] === 'cancelado' || 
                $estados['estado_participante'] === 'cancelado' || 
                $estados['estado_coordinador'] === 'cancelado') {
                $nuevoEstado = 'cancelada';
            }
          
            elseif ($estados['estado_creador'] === 'confirmado' && 
                    $estados['estado_participante'] === 'confirmado' && 
                    $estados['estado_coordinador'] === 'confirmado') {
                $nuevoEstado = 'confirmada';
            }

           
            $sql = "UPDATE reuniones SET estado_general = :estado WHERE id_reunion = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->bindParam(':id', $id_reunion, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar estado general: " . $e->getMessage());
        }
    }

    /**
     * Obtiene una reunión específica por ID
     * Solo si NO está eliminada (eliminado = 0).
     * Incluye nombres de ambos alumnos.
     *
     * /Recibe: int $id_reunion
     * /Devuelve: array|false
     */
    public function obtenerReunionPorId($id_reunion) {
        try {
            $sql = "SELECT r.*, 
                    ac.nombre AS nombre_creador, 
                    ap.nombre AS nombre_participante
                    FROM reuniones r
                    JOIN alumnos ac ON r.id_alumno_creador = ac.ID
                    JOIN alumnos ap ON r.id_alumno_participante = ap.ID
                    WHERE r.id_reunion = :id AND r.eliminado = 0";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_reunion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener reunión: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca un alumno por su correo electrónico
     *
     * /Recibe: string $correo
     * /Devuelve: array|false (datos del alumno o false si no existe)
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
     * SOFT DELETE: Marca una reunión como eliminada (no la borra físicamente)
     *
     * Registra la fecha de eliminación y quién la eliminó para auditoría.
     *
     * /Recibe: int $id_reunion, int $id_usuario (quien elimina)
     * /Devuelve: bool
     */
    public function eliminarReunion($id_reunion, $id_usuario) {
        try {
            $sql = "UPDATE reuniones 
                    SET eliminado = 1, 
                        fecha_eliminacion = NOW(), 
                        eliminado_por = :usuario 
                    WHERE id_reunion = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_reunion, PDO::PARAM_INT);
            $stmt->bindParam(':usuario', $id_usuario, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar reunión: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todas las reuniones eliminadas (para administradores)
     *
     * /Devuelve: array
     */
    public function obtenerReunionesEliminadas() {
        try {
            $sql = "SELECT r.*, 
                    ac.nombre AS nombre_creador,
                    ap.nombre AS nombre_participante,
                    ae.nombre AS eliminado_por_nombre
                    FROM reuniones r
                    JOIN alumnos ac ON r.id_alumno_creador = ac.ID
                    JOIN alumnos ap ON r.id_alumno_participante = ap.ID
                    LEFT JOIN alumnos ae ON r.eliminado_por = ae.ID
                    WHERE r.eliminado = 1
                    ORDER BY r.fecha_eliminacion DESC";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener reuniones eliminadas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Restaura una reunión eliminada (soft undelete)
     * /Recibe: int $id_reunion
     * /Devuelve: bool
     */
    public function restaurarReunion($id_reunion) {
        try {
            $sql = "UPDATE reuniones 
                    SET eliminado = 0, 
                        fecha_eliminacion = NULL, 
                        eliminado_por = NULL 
                    WHERE id_reunion = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_reunion, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al restaurar reunión: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los detalles de una reunión pendiente
     * Solo se pueden actualizar reuniones con estado_general = 'pendiente'.
     * /Recibe: array $data con keys:
     *    id_reunion, id_participante, correo_participante,
     *   objeto_creador, objeto_participante, lugar, fecha, hora, notas
     * /Devuelve: bool
     */
    public function actualizarReunion($data) {
        try {
            $sql = "UPDATE reuniones 
                    SET id_alumno_participante = :id_participante,
                        correo_participante = :correo_participante,
                        objeto_creador = :objeto_creador,
                        objeto_participante = :objeto_participante,
                        lugar = :lugar,
                        fecha_reunion = :fecha,
                        hora_reunion = :hora,
                        notas = :notas
                    WHERE id_reunion = :id_reunion 
                    AND estado_general = 'pendiente'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_reunion', $data['id_reunion'], PDO::PARAM_INT);
            $stmt->bindParam(':id_participante', $data['id_participante'], PDO::PARAM_INT);
            $stmt->bindParam(':correo_participante', $data['correo_participante']);
            $stmt->bindParam(':objeto_creador', $data['objeto_creador']);
            $stmt->bindParam(':objeto_participante', $data['objeto_participante']);
            $stmt->bindParam(':lugar', $data['lugar']);
            $stmt->bindParam(':fecha', $data['fecha']);
            $stmt->bindParam(':hora', $data['hora']);
            $stmt->bindParam(':notas', $data['notas']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar reunión: " . $e->getMessage());
            return false;
        }
    }
}
?>
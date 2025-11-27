<?php

/**
 * Clase EstudianteModel
 * Maneja las operaciones de base de datos para los alumnos.
 */
class EstudianteModel {
    private $db;

    public function __construct($dbConnection) {
        // Recibe la conexión PDO (instancia de la función conectar())
        $this->db = $dbConnection;
    }   

    /**
     * Registra un nuevo alumno en la base de datos.
     * @param array $data Los datos del alumno (nombre, carrera, etc., incluyendo la contraseña *hasheada*).
     * @return bool Devuelve true si el registro fue exitoso, false en caso contrario.
     */
    public function registrarEstudiante($data) {
        // La columna de contraseña en la tabla 'alumnos' es 'contra', no 'contrasena'
        $sql = "INSERT INTO alumnos (nombre, carrera, cuatrimestre, turno, correo, contra) 
                VALUES (:nombre, :carrera, :cuatrimestre, :turno, :correo, :contra)";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            // Vincular los parámetros de forma segura para prevenir inyección SQL
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':carrera', $data['carrera']);
            $stmt->bindParam(':cuatrimestre', $data['cuatrimestre']);
            $stmt->bindParam(':turno', $data['turno']);
            $stmt->bindParam(':correo', $data['correo']);
            $stmt->bindParam(':contra', $data['contrasena_hashed']); // Usamos el hash de la contraseña

            return $stmt->execute();
        } catch (PDOException $e) {
            // En un entorno de producción, registrar este error en un log en lugar de imprimirlo.
            error_log("Error al registrar alumno: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica las credenciales de un estudiante.
     * Retorna los datos del estudiante (id, nombre, correo) si es exitoso, o false si falla.
     */
    public function login(string $correo, string $contrasena_plain) {
        try {
            // Asumo que la PK de alumnos es 'ID'. Si no, cámbialo aquí.
            $sql = "SELECT ID AS id, nombre, correo, contra 
                    FROM alumnos 
                    WHERE correo = :correo 
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':correo', $correo);
            $stmt->execute();

            $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si se encontró el usuario Y si la contraseña coincide
            if ($estudiante && password_verify($contrasena_plain, $estudiante['contra'])) {
                
                unset($estudiante['contra']); // No guardar el hash en la sesión
                return $estudiante; 
            
            } else {
                // Usuario no encontrado o contraseña incorrecta
                return false;
            }

        } catch (PDOException $e) {
            error_log("EstudianteModel::login error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un correo electrónico ya existe en la tabla de alumnos.
     * @param string $correo El correo a verificar.
     * @return bool Devuelve true si el correo ya existe, false en caso contrario.
     */
    public function consultarEstudiantePorCorreo($correo) {
        try {
            $sql = "SELECT ID FROM alumnos WHERE correo = :correo LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();
            
            // fetch() devuelve false si no encuentra ninguna fila
            // Si devuelve una fila (es decir, no es false), el correo existe.
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;

        } catch (PDOException $e) {
            error_log("Error al consultar correo: " . $e->getMessage());
            // En caso de error de BD, es más seguro asumir que existe
            // para prevenir duplicados, aunque podrías manejarlo de otra forma.
            return true; 
        }
    }

   /**
     * Consulta todos los estudiantes de la base de datos.
     * @return array Devuelve un array con todos los estudiantes.
     */
    public function consultarEstudiantes() {
        try {
            $sql = "SELECT * FROM alumnos ORDER BY nombre ASC";
            // Usamos query() porque no hay parámetros de entrada
            $stmt = $this->db->query($sql);
            
            // ¡CORRECCIÓN! Usamos fetchAll() de PDO, no fetch_all() de MySQLi
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Devuelve todos los resultados
            
        } catch (PDOException $e) {
            error_log("Error al consultar alumnos: " . $e->getMessage());
            return []; // Devuelve un array vacío en caso de error
        }
    }

    /**
     * Obtiene los datos de un estudiante específico por su ID.
     * @param int $id El ID del estudiante.
     * @return array|false Devuelve los datos del estudiante o false si no se encuentra.
     */
    public function consultarEstudiantePorId($id) {
        try {
            $sql = "SELECT * FROM alumnos WHERE ID = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); // Devuelve un solo resultado
        } catch (PDOException $e) {
            error_log("Error al obtener alumno por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos de un estudiante en la base de datos.
     * @param array $data Los datos del alumno a actualizar (incluyendo el ID).
     * @return bool Devuelve true si la actualización fue exitosa, false en caso contrario.
     */
    public function actualizarEstudiante($data) {
        // Opcional: Manejar actualización de contraseña
        // Si la contraseña viene vacía, no la actualizamos.
        if (!empty($data['contrasena'])) {
            $contrasena_hashed = password_hash($data['contrasena'], PASSWORD_DEFAULT);
            $sql = "UPDATE alumnos SET 
                        nombre = :nombre, 
                        carrera = :carrera, 
                        cuatrimestre = :cuatrimestre, 
                        turno = :turno, 
                        correo = :correo, 
                        contra = :contra 
                    WHERE ID = :id";
        } else {
            // No actualizar la contraseña
            $sql = "UPDATE alumnos SET 
                        nombre = :nombre, 
                        carrera = :carrera, 
                        cuatrimestre = :cuatrimestre, 
                        turno = :turno, 
                        correo = :correo 
                    WHERE ID = :id";
        }

        try {
            $stmt = $this->db->prepare($sql);
            
            // ¡CORRECCIÓN! Usamos bindParam() de PDO
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':carrera', $data['carrera']);
            $stmt->bindParam(':cuatrimestre', $data['cuatrimestre']);
            $stmt->bindParam(':turno', $data['turno']);
            $stmt->bindParam(':correo', $data['correo']);
            $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);

            // Vincular la contraseña solo si se va a actualizar
            if (!empty($data['contrasena'])) {
                $stmt->bindParam(':contra', $contrasena_hashed);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar alumno: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un estudiante de la base de datos por su ID.
     * @param int $id El ID del estudiante a eliminar.
     * @return bool Devuelve true si la eliminación fue exitosa, false en caso contrario.
     */
    public function eliminarEstudiante($id) {
            $sql = "DELETE FROM alumnos WHERE ID = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
    }
}
?>
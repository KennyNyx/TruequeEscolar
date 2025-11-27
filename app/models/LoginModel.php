<?php
class LoginModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Verifica las credenciales del usuario y retorna sus datos con el correo incluido.
     * Valida el correo y contraseña del usuario contra la tabla correspondiente
     * según su rol. Utiliza password_verify() para validar contraseñas hasheadas.
     *
     * Roles soportados:
     * - 'alumno' => tabla 'alumnos', ID => 'ID'
     * - 'administrador' => tabla 'administradores', ID => 'id_admin'
     * - 'coordinador' => tabla 'coordinadores', ID => 'id_coordi'
     *
     * IMPORTANTE: El correo se incluye en la respuesta porque es necesario
     * para el sistema de reuniones y chat.
     *
     * /Recibe: string $correo, string $contrasena, string $role
     * /Devuelve: array con datos de usuario (id, nombre, correo, role, datos_completos) si es válido
     *            false si el rol no existe, no se encuentra el usuario, o la contraseña es incorrecta
     */
    public function verificarUsuario($correo, $contrasena, $role) {
        
        $tabla_map = [
            'alumno' => 'alumnos',
            'administrador' => 'administradores',
            'coordinador' => 'coordinadores'
        ];

        $id_map = [
            'alumno' => 'ID',
            'administrador' => 'id_admin',
            'coordinador' => 'id_coordi'
        ];

        if (!array_key_exists($role, $tabla_map)) {
            return false; 
        }
        
        $tabla = $tabla_map[$role];
        $id_columna = $id_map[$role];

        try {
            $sql = "SELECT * FROM `$tabla` WHERE correo = :correo";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($contrasena, $usuario['contra'])) {

                return [
                    'id' => $usuario[$id_columna],
                    'nombre' => $usuario['nombre'],
                    'correo' => $usuario['correo'], 
                    'role' => $role,
                    'datos_completos' => $usuario 
                ];
            } else {
                return false;
            }

        } catch (PDOException $e) {
            error_log("Error en LoginModel: " . $e->getMessage());
            return false;
        }
    }
}
?>
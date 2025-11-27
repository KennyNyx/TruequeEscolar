<?php

class UserController {
    
    private $estudianteModel;
    private $adminModel;
    private $coordinadorModel;

    public function __construct($dbConnection) {
        $this->estudianteModel = new EstudianteModel($dbConnection);
        $this->adminModel = new AdminModel($dbConnection);
        $this->coordinadorModel = new CoordinadorModel($dbConnection);
    }

    /**
     * ACCIÓN: registrar (Lógica del formulario de registro)
     */
    public function registrar() {
        
        $redirect_url_base = 'index.php?action=mostrarRegistro';
        $message = '';
        $type = 'error'; 
        $datos_previos = $_POST;

        $rol = trim($_POST['rol'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = filter_var($_POST['correo'] ?? '', FILTER_SANITIZE_EMAIL);
        $contrasena = $_POST['contrasena'] ?? '';

        if (empty($rol) || empty($nombre) || empty($correo) || empty($contrasena)) {
            $message = "Todos los campos (Nombre, Tipo de Usuario, Correo, Contraseña) son obligatorios.";
        } else {
            switch ($rol) {
                case 'alumnos':
                    $carrera = trim($_POST['carrera'] ?? '');
                    $cuatrimestre = filter_var($_POST['cuatrimestre'] ?? '', FILTER_SANITIZE_NUMBER_INT);
                    $turno = trim($_POST['turno'] ?? '');

                    if (empty($carrera) || empty($cuatrimestre) || empty($turno)) {
                        $message = "Para registrar un alumno, todos los campos son obligatorios.";
                        break; 
                    }

                    if ($this->estudianteModel->consultarEstudiantePorCorreo($correo)) {
                        $message = "Error: El correo electrónico ya está registrado para un alumno.";
                        break; 
                    }
                    
                    $contrasena_hashed = password_hash($contrasena, PASSWORD_DEFAULT);
                    
                    $datosEstudiante = [
                        'nombre' => $nombre,
                        'carrera' => $carrera,
                        'cuatrimestre' => $cuatrimestre,
                        'turno' => $turno,
                        'correo' => $correo,
                        'contrasena_hashed' => $contrasena_hashed,
                    ];

                    if ($this->estudianteModel->registrarEstudiante($datosEstudiante)) {
                        $message = "Registro de estudiante exitoso. ¡Bienvenido!";
                        $type = "success";
                        $datos_previos = [];
                    } else {
                        $message = "Error al registrar el estudiante en la base de datos.";
                    }
                    break; 

                case 'administrador':
                    $datosAdmin = [
                        'nombre' => $nombre,
                        'correo' => $correo,
                        'contrasena' => $contrasena,
                    ];

                    $resultado = $this->adminModel->registrarAdmin($datosAdmin);

                    if (is_int($resultado) && $resultado > 0) {
                        $message = "Registro de administrador exitoso.";
                        $type = "success";
                        $datos_previos = [];
                    } else if ($resultado === 'email_exists') {
                        $message = "Error: El correo electrónico ya está registrado para un administrador.";
                    } else {
                        $message = "Error al registrar el administrador en la base de datos.";
                    }
                    break; 

                case 'coordinador':
                    $datosCoord = [
                        'nombre' => $nombre,
                        'correo' => $correo,
                        'contrasena' => $contrasena,
                    ];
                    
                    $resultado = $this->coordinadorModel->registrarCoordinador($datosCoord);

                    if (is_int($resultado) && $resultado > 0) {
                        $message = "Registro de coordinador exitoso.";
                        $type = "success";
                        $datos_previos = [];
                    } else if ($resultado === 'email_exists') {
                        $message = "Error: El correo electrónico ya está registrado para un coordinador.";
                    } else {
                        $message = "Error al registrar el coordinador en la base de datos.";
                    }
                    break; 

                default:
                    $message = "Error: El tipo de usuario seleccionado no es válido.";
                    break;
            }
        }

        $redirect_url = $redirect_url_base;
        
        if ($type === 'success') {
            $redirect_url .= '&message=' . urlencode($message) . '&type=success';
        } else {
            $query_data = array_merge(['message' => $message, 'type' => 'error'], $datos_previos);
            $redirect_url .= '&' . http_build_query($query_data);
        }
        
        header("Location: " . $redirect_url);
        exit();
    }

    /**
     * ACCIÓN: consultar (Leer)
     */
    public function consultar() {
        $datosEstudiante = $this->estudianteModel->consultarEstudiantes(); 
        $usuario_a_editar = false; 
        include "app/views/consultarUsuarios.php"; 
    }

    /**
     * ACCIÓN: editar (Preparar Actualización)
     */
    public function editar() {
        $id = filter_var($_GET['id'] ?? null, FILTER_SANITIZE_NUMBER_INT);
        
        if ($id) {
            $usuario_a_editar = $this->estudianteModel->consultarEstudiantePorId($id);

            if ($usuario_a_editar) {
                $datosEstudiante = $this->estudianteModel->consultarEstudiantes(); 
                include "app/views/consultarUsuarios.php"; 
            } else {
                $message = "Usuario con ID $id no encontrado.";
                $type = "error";
                header("Location: index.php?action=user_consultar&message=" . urlencode($message) . "&type=" . $type);
                exit();
            }
        } else {
            header("Location: index.php?action=user_consultar");
            exit();
        }
    }

    /**
     * ACCIÓN: actualizar (Ejecutar Actualización)
     */
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            
            $datosActualizar = [
                'id' => filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT),
                'nombre' => trim($_POST['nombre'] ?? ''),
                'carrera' => trim($_POST['carrera'] ?? ''),
                'cuatrimestre' => filter_var($_POST['cuatrimestre'] ?? '', FILTER_SANITIZE_NUMBER_INT),
                'turno' => trim($_POST['turno'] ?? ''),
                'correo' => filter_var($_POST['correo'] ?? '', FILTER_SANITIZE_EMAIL),
                'contrasena' => $_POST['contrasena'] ?? '' 
            ];

            if ($this->estudianteModel->actualizarEstudiante($datosActualizar)) {
                $message = "Usuario actualizado correctamente.";
                $type = "success";
            } else {
                $message = "Error al actualizar el usuario. Verifique los datos o la conexión a la BD.";
                $type = "error";
            }
            
            header("Location: index.php?action=user_consultar&message=" . urlencode($message) . "&type=" . $type);
            exit();
            
        } else {
            header("Location: index.php?action=user_consultar");
            exit();
        }
    }

    /**
     * ACCIÓN: eliminar (Eliminar)
     * CORRECCIÓN: Redirección incorrecta que causaba ir al login
     */
    public function eliminar() {
        // Verificar que el ID exista
        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
            
            // Intentar eliminar el estudiante
            if ($this->estudianteModel->eliminarEstudiante($id)) {
                $message = "Usuario eliminado correctamente.";
                $type = "success";
            } else {
                $message = "Error al eliminar el usuario.";
                $type = "error";
            }
            
            // ⭐ CORRECCIÓN: Redirección correcta (antes tenía controller= en lugar de action=)
            header("Location: index.php?action=user_consultar&message=" . urlencode($message) . "&type=" . $type);
            exit();
            
        } else {
            // Si no hay ID, redirigir a la consulta
            $message = "No se proporcionó un ID válido.";
            $type = "error";
            header("Location: index.php?action=user_consultar&message=" . urlencode($message) . "&type=" . $type);
            exit();
        }
    }

    /**
     * Acción por defecto/Login
     */
    public function login() {
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php?action=vistaAdmin");
        } else {
            header("Location: index.php?action=login"); 
        }
        exit();
    }

}
?>
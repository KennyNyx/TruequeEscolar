<?php
class LoginController {
    private $model;

    /**
     * Inicializa la conexión a la base de datos y crea una instancia del modelo CoordinadorModel.
     */
    public function __construct($db) {
        $this->model = new LoginModel($db);
    }

    /**
     * Muestra el formulario de login
     * Carga la vista login.php que contiene el formulario para que
     * el usuario ingrese sus credenciales (correo, contraseña y rol).
     */
    public function showLogin() {
        include "app/views/login.php";
    }

    /**
     * Procesa el formulario de login del usuario
     * Obtiene los datos del formulario (correo, contraseña, rol).
     * Valida que los campos no estén vacíos. Si la validación falla, redirige con error 4.
     * Verifica las credenciales usando el modelo LoginModel.
     * Si son correctas, crea la sesión con los datos del usuario y redirige según su rol.
     * Si son incorrectas, redirige con error 1.
     * 
     * Roles soportados:
     * - alumno: Redirige a vistaAlumnos
     * - administrador: Redirige a vistaAdmin
     * - coordinador: Redirige a vistaCoordinador
     * 
     * /Recibe: POST[correo], POST[contrasena], POST[role]
     * /Devuelve: Redirige a index.php según el resultado del login
     */
    public function doLogin() {
        $correo = trim($_POST['correo'] ?? '');
        $contrasena = $_POST['contrasena'] ?? ''; 
        $role = $_POST['role'] ?? ''; 

        if ($correo === '' || $contrasena === '' || $role === '') {
            header("Location: index.php?action=login&error=4");
            exit();
        }
        $user = $this->model->verificarUsuario($correo, $contrasena, $role);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_correo'] = $user['correo']; 
            if ($role === 'administrador') {
                header("Location: index.php?action=vistaAdmin");
            } else if ($role === 'coordinador') {
                header("Location: index.php?action=vistaCoordinador");
            } else {
                header("Location: index.php?action=vistaAlumnos");
            }
            exit();
        } else {
            header("Location: index.php?action=login&error=1&correo=" . urlencode($correo));
            exit();
        }
    }
}
?>
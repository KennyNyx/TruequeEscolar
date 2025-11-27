<?php
class AdminController {
    private $db;
    private $adminModel;
    /**
     * Inicializa la conexión a la base de datos y crea una instancia del modelo AdminModel.
     */
    public function __construct() {
        try {
            if (!isset($this->db)) {
                 $this->db = conectar();
            }
            $this->adminModel = new AdminModel($this->db);
        } catch (Exception $e) {
            $message = "Error interno del servidor.";
            error_log("Error fatal en AdminController: " . $e->getMessage());
            header("Location: index.php?message=" . urlencode($message) . '&type=error');
            exit();
        }
    }

    /**
     * Obtiene y muestra la lista de todos los administradores.
     * Verifica que el usuario sea administrador antes de proceder.
     * Si no tiene permisos, lo redirige al login.
     */
    public function consultar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
            header("Location: index.php?action=login");
            exit;
        }
        $admins = $this->adminModel->obtenerAdmins();        
        $nombre_usuario = $_SESSION['user_nombre'] ?? 'Admin';
        include "app/views/consultarAdmins.php";
    }

    /**
     * Registra un nuevo administrador en el sistema
     * Valida que los datos POST contengan nombre, correo y contraseña.
     * Si algún campo está vacío, redirige con error. Si el registro
     * es exitoso, redirige con mensaje de éxito.
     * 
     * /Recibe: POST[nombre], POST[correo], POST[contrasena]
     * /Devuelve: Nada. Redirige a la vista con mensaje de éxito.
     */
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=admin_consultar");
            exit();
        }
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');
        if (empty($nombre) || empty($correo) || empty($contrasena)) {
             header("Location: index.php?action=admin_consultar&message=Campos vacios&type=error");
             exit();
        }
        $data = ['nombre' => $nombre, 'correo' => $correo, 'contrasena' => $contrasena];
        $resultado = $this->adminModel->registrarAdmin($data);
        if ($resultado) {
            header("Location: index.php?action=admin_consultar&message=Administrador registrado&type=success");
        } else {
            header("Location: index.php?action=admin_consultar&message=Error al registrar&type=error");
        }
        exit();
    }

    /**
     * Actualiza los datos de un administrador existente
     * Recibe los datos actualizados (id, nombre, correo, contraseña) por POST.
     * Valida que sea una petición POST y procesa la actualización. 
     * Redirige con mensaje de éxito o error según el resultado.
     * 
     * /Recibe: POST[id], POST[nombre], POST[correo], POST[contrasena]
     * /Devuelve: Nada. Redirige a admin_consultar con un mensaje
     */
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== "POST") {
            header("Location: index.php?action=admin_consultar");
            exit();
        }
        $data = [
            "id" => $_POST["id"],
            "nombre" => trim($_POST["nombre"]),
            "correo" => trim($_POST["correo"]),
            "contrasena" => trim($_POST["contrasena"])
        ];
        $ok = $this->adminModel->actualizarAdmin($data);
        $message = $ok ? "Administrador actualizado." : "Error al actualizar.";
        $type = $ok ? "success" : "error";

        header("Location: index.php?action=admin_consultar&message={$message}&type={$type}");
        exit();
    }

    /**
     * Elimina un administrador del sistema
     * Obtiene el ID del administrador a eliminar desde GET y lo valida.
     * Si es válido, procede a eliminar. Redirige con mensaje indicando
     * si la eliminación fue exitosa o falló.
     * 
     * /Recibe: GET[id]
     * /Devuelve: Nada. Redirige a admin_consultar con un mensaje
     */
    public function eliminar() {
        if (!isset($_GET['id'])) {
            header("Location: index.php?action=admin_consultar");
            exit();
        }
        $id = (int) $_GET['id'];
        $ok = $this->adminModel->eliminarAdmin($id);
        $message = $ok ? "Administrador eliminado." : "Error al eliminar.";
        $type = $ok ? "success" : "error";

        header("Location: index.php?action=admin_consultar&message={$message}&type={$type}");
        exit();
    }

    /**
     * Carga la vista de edición para el administrador con el que se esta trabajando.
     * Verifica permisos de administrador y obtiene el ID del admin a editar.
     * Recupera los datos del administrador y carga la vista con la información
     * actual y la lista completa de administradores.
     * 
     * /Recibe: GET[id]
     * /Devuelve: Nada. 
     */
    public function editar() {
        // 1. Seguridad
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
            header("Location: index.php?action=login");
            exit;
        }
        if (!isset($_GET['id'])) {
            header("Location: index.php?action=admin_consultar");
            exit();
        }
        $id = (int)$_GET['id'];
        $adminEditar = $this->adminModel->obtenerAdmin($id);
        $admins = $this->adminModel->obtenerAdmins(); 
        $nombre_usuario = $_SESSION['user_nombre'] ?? 'Admin';
        $activar_edicion = true; 

 
        include "app/views/consultarAdmins.php";
    }

    /**
     * Obtiene y muestra las reseñas eliminadas del sistema
     * Crea una instancia del modelo de reseñas y recupera todos
     * los registros de reseñas que han sido eliminadas. Carga la
     * vista correspondiente para visualizarlas.
     */
    public function verResenasEliminadas() {
        $resenaModel = new ResenaModel($this->db);
        $resenas_eliminadas = $resenaModel->obtenerResenasEliminadas();
        include "app/views/admin_resenas_eliminadas.php";
    }
}
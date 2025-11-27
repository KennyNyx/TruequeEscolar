<?php
class CoordinadorController {

    private $db;
    private $coordiModel;

    /**
     * Inicializa la conexión a la base de datos y crea una instancia del modelo CoordinadorModel.
     */
    public function __construct() {
        try {
            if (!isset($this->db)) {
                 $this->db = conectar();
            }
            $this->coordiModel = new CoordinadorModel($this->db);
        } catch (Exception $e) {
        }
    }

    /**
     * Vista principal del coordinador.
     * Verifica que el usuario tenga rol 'coordinador'. Si no, redirige al login.
     * Carga la vista principal del coordinador (vistaCoordi.php) y puede pasar estadísticas.
     * 
     * /Recibe: $_SESSION para verificar rol
     * /Devuelve:  la vista app/views/vistaCoordi.php
     */
    public function vistaCoordinador() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'coordinador') {
             header("Location: index.php?action=login");
             exit;
        }
        include "app/views/vistaCoordi.php";
    }

    /**
     * Obtiene y muestra la lista de coordinadores
     * Verifica que el usuario sea administrador antes de mostrar la lista.
     * Recupera todos los coordinadores del modelo y carga la vista consultarCoordi.php.
     */
    public function consultar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Solo admins pueden ver coordinadores (o el rol que decidas)
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
             header("Location: index.php?action=login");
             exit;
        }
        $coordinadores = $this->coordiModel->obtenerCoordinadores();
        $nombre_usuario = $_SESSION['user_nombre'] ?? 'Admin';

        include "app/views/consultarCoordi.php";
    }

    /**
     * Registra un nuevo coordinador
     * Procesa petición POST con nombre, correo y contraseña; delega el registro al modelo.
     * Redirige a la lista con mensaje de éxito o error. (No valida campos en este método.)
     * 
     * /Recibe: POST[nombre], POST[correo], POST[contrasena]
     * /Devuelve: nada 
     */
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=coord_consultar");
            exit();
        }
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');

        $data = ['nombre' => $nombre, 'correo' => $correo, 'contrasena' => $contrasena];
        $resultado = $this->coordiModel->registrarCoordinador($data);

        if ($resultado && $resultado !== 'email_exists') {
            header("Location: index.php?action=coord_consultar&message=Coordinador registrado&type=success");
        } else {
            header("Location: index.php?action=coord_consultar&message=Error al registrar&type=error");
        }
        exit();
    }

    /**
     *  Actualiza los datos de un coordinador existente
     * Recibe datos por POST (id, nombre, correo, contrasena) y solicita al modelo la actualización.
     * Redirige a la lista con mensaje indicando resultado.
     * 
     * /Recibe: POST[id], POST[nombre], POST[correo], POST[contrasena]
     * /Devuelve: Redirige a index.php?action=coord_consultar
     */
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== "POST") {
            header("Location: index.php?action=coord_consultar");
            exit();
        }
        $data = [
            "id" => $_POST["id"],
            "nombre" => trim($_POST["nombre"]),
            "correo" => trim($_POST["correo"]),
            "contrasena" => trim($_POST["contrasena"])
        ];
        $ok = $this->coordiModel->actualizarCoordinador($data);
        $message = $ok ? "Actualizado con éxito." : "Error al actualizar.";
        $type = $ok ? "success" : "error";
        header("Location: index.php?action=coord_consultar&message={$message}&type={$type}");
        exit();
    }

    /**
     * Elimina un coordinador
     * Lee el parámetro GET[id], valida y solicita al modelo la eliminación.
     * Redirige a la lista con mensaje indicando éxito o fallo.
     * 
     * /Recibe: GET[id]
     * /Devuelve: Redirige a index.php?action=coord_consultar 
     */
    public function eliminar() {
        if (!isset($_GET['id'])) {
            header("Location: index.php?action=coord_consultar");
            exit();
        }
        $id = (int) $_GET['id'];
        $ok = $this->coordiModel->eliminarCoordinador($id);
        $message = $ok ? "Eliminado correctamente." : "Error al eliminar.";
        $type = $ok ? "success" : "error";
        header("Location: index.php?action=coord_consultar&message={$message}&type={$type}");
        exit();
    }
    
    /**
     * Carga la vista de edición para un coordinador específico
     * Verifica permisos de administrador, valida el id recibido por GET, obtiene
     * los datos del coordinador y además carga la lista completa para la tabla.
     * Prepara variables para la vista en modo edición y la incluye.
     * 
     * /Recibe: GET[id]
     * /Devuelve: Redirige la vista app/views/consultarCoordi.php con modo edición
     */
    public function editar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrador') {
            header("Location: index.php?action=login");
            exit();
        }

        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: index.php?action=coord_consultar");
            exit();
        }

        $id = (int) $_GET['id'];
        $coordinador = $this->coordiModel->obtenerCoordinadorPorId($id);
        
        if (!$coordinador) {
            header("Location: index.php?action=coord_consultar&message=Coordinador no encontrado&type=error");
            exit();
        }
        $coordinadores = $this->coordiModel->obtenerCoordinadores();        
        $nombre_usuario = $_SESSION['user_nombre'] ?? 'Admin';
        $usuario_a_editar = $coordinador;
        $modo_edicion = true;

        include "app/views/consultarCoordi.php";
    }
}
?>
 
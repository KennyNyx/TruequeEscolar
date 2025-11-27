<?php
class ReunionController {
    private $db;
    private $reunionModel;

    /**
     * Inicializa la conexión a la base de datos y crea una instancia del modelo ResenaModel.
     */
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->reunionModel = new ReunionModel($dbConnection);
    }

    /**
     * Muestra el formulario para crear una reunión
     * Verifica que exista sesión y que el rol sea 'alumno'. Carga la vista crear_reunion.php
     * pasando nombre y correo del usuario desde la sesión.
     *
     * /Recibe:usa $_SESSION
     * /Devuelve:  la vista app/views/crear_reunion.php o redirige a login.
     */
    public function mostrarFormularioCrear() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
            header("Location: index.php?action=login");
            exit();
        }
        $nombre_usuario = $_SESSION['user_nombre'];
        $correo_usuario = $_SESSION['user_correo'] ?? '';
        
        include "app/views/crear_reunion.php";
    }

    /**
     * Procesa la creación de una nueva reunión
     * Valida método POST, sesión y campos del formulario. Verifica que no se cree una reunión
     * consigo mismo y que el participante exista. Prepara datos y delega la inserción al modelo.
     * Redirige con mensaje de éxito o error.
     *
     * Recibe: POST[correo_participante], POST[objeto_creador], POST[objeto_participante],
     *          POST[lugar], POST[fecha], POST[hora], POST[notas]
     * Devuelve: Redirección a gestionar_reuniones o a crear_reunion en caso de error.
     */
    public function crearReunion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=crear_reunion");
            exit();
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
            header("Location: index.php?action=login");
            exit();
        }
        $correo_participante = filter_var($_POST['correo_participante'] ?? '', FILTER_SANITIZE_EMAIL);
        $objeto_creador = trim($_POST['objeto_creador'] ?? '');
        $objeto_participante = trim($_POST['objeto_participante'] ?? '');
        $lugar = trim($_POST['lugar'] ?? '');
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $notas = trim($_POST['notas'] ?? '');

        if (empty($correo_participante) || empty($objeto_creador) || empty($objeto_participante) || 
            empty($lugar) || empty($fecha) || empty($hora)) {
            header("Location: index.php?action=crear_reunion&error=campos_vacios");
            exit();
        }

        if ($correo_participante === $_SESSION['user_correo']) {
            header("Location: index.php?action=crear_reunion&error=mismo_correo");
            exit();
        }

        $alumno_participante = $this->reunionModel->buscarAlumnoPorCorreo($correo_participante);
        if (!$alumno_participante) {
            header("Location: index.php?action=crear_reunion&error=alumno_no_encontrado");
            exit();
        }
        $data = [
            'id_creador' => $_SESSION['user_id'],
            'id_participante' => $alumno_participante['ID'],
            'correo_creador' => $_SESSION['user_correo'],
            'correo_participante' => $correo_participante,
            'objeto_creador' => $objeto_creador,
            'objeto_participante' => $objeto_participante,
            'lugar' => $lugar,
            'fecha' => $fecha,
            'hora' => $hora,
            'notas' => $notas
        ];

        if ($this->reunionModel->crearReunion($data)) {
            header("Location: index.php?action=gestionar_reuniones&mensaje=reunion_creada");
        } else {
            header("Location: index.php?action=crear_reunion&error=error_db");
        }
        exit();
    }

    /**
     * Muestra las reuniones del usuario
     * Si el rol es 'coordinador' obtiene todas las reuniones; si es alumno, obtiene
     * solo las reuniones del alumno. Carga la vista mis_reuniones.php con los datos.
     *
     * Recibe: usa $_SESSION
     * Devuelve:  la vista app/views/mis_reuniones.php o redirige al login.
     */
    public function gestionarReuniones() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $nombre_usuario = $_SESSION['user_nombre'];
        $rol = $_SESSION['user_role'];

        if ($rol === 'coordinador') {
            $reuniones = $this->reunionModel->obtenerTodasReuniones();
        } 
        else {
            $reuniones = $this->reunionModel->obtenerReunionesPorAlumno($_SESSION['user_id']);
        }

        include "app/views/mis_reuniones.php";
    }

    /**
     * Confirma una reunión
     * 
     * Valida sesión y existencia de la reunión, determina el rol del usuario
     * en la reunión (creador/participante/coordinador) y actualiza el estado a 'confirmado'.
     *
     * /Recibe: GET[id]
     * /Devuelve: Redirección a gestionar_reuniones con mensaje de resultado.
     */
    public function confirmarReunion() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
            header("Location: index.php?action=gestionar_reuniones");
            exit();
        }

        $id_reunion = (int)$_GET['id'];
        $reunion = $this->reunionModel->obtenerReunionPorId($id_reunion);

        if (!$reunion) {
            header("Location: index.php?action=gestionar_reuniones&error=reunion_no_encontrada");
            exit();
        }

        $rol = $this->determinarRol($reunion, $_SESSION['user_id'], $_SESSION['user_role']);

        if (!$rol) {
            header("Location: index.php?action=gestionar_reuniones&error=sin_permiso");
            exit();
        }

        if ($this->reunionModel->actualizarEstado($id_reunion, $rol, 'confirmado')) {
            header("Location: index.php?action=gestionar_reuniones&mensaje=confirmado");
        } else {
            header("Location: index.php?action=gestionar_reuniones&error=error_confirmar");
        }
        exit();
    }

    /**
     * Cancela una reunión
     * Similar a confirmarReunion(), determina rol y actualiza el estado a 'cancelado'.
     *
     * /Recibe: GET[id]
     * /Devuelve: Redirección a gestionar_reuniones con mensaje de resultado.
     */
    public function cancelarReunion() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
            header("Location: index.php?action=gestionar_reuniones");
            exit();
        }

        $id_reunion = (int)$_GET['id'];
        $reunion = $this->reunionModel->obtenerReunionPorId($id_reunion);

        if (!$reunion) {
            header("Location: index.php?action=gestionar_reuniones&error=reunion_no_encontrada");
            exit();
        }

        $rol = $this->determinarRol($reunion, $_SESSION['user_id'], $_SESSION['user_role']);

        if (!$rol) {
            header("Location: index.php?action=gestionar_reuniones&error=sin_permiso");
            exit();
        }

        if ($this->reunionModel->actualizarEstado($id_reunion, $rol, 'cancelado')) {
            header("Location: index.php?action=gestionar_reuniones&mensaje=cancelado");
        } else {
            header("Location: index.php?action=gestionar_reuniones&error=error_cancelar");
        }
        exit();
    }

    /**
     * Elimina una reunión (soft delete)
     * Marca la reunión como eliminada en la BD (soft delete). Verifica sesión y delega
     * la acción al modelo. Redirige con mensaje de resultado.
     *
     * /Recibe: GET[id]
     * /Devuelve: Redirección a gestionar_reuniones con mensaje.
     */
    public function eliminarReunion() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        if (!isset($_GET['id'])) {
            header("Location: index.php?action=gestionar_reuniones");
            exit();
        }
        $id_reunion = (int)$_GET['id'];
        $id_usuario = $_SESSION['user_id'];
        $resultado = $this->reunionModel->eliminarReunion($id_reunion, $id_usuario);

        if ($resultado) {
            header("Location: index.php?action=gestionar_reuniones&mensaje=reunion_eliminada");
        } else {
            header("Location: index.php?action=gestionar_reuniones&error=error_eliminar");
        }
        exit();
    }

    /**
     * Determina el rol del usuario en una reunión específica
     * 
     * /Recibe: $reunion (array), $user_id (int), $user_role (string)
     * /Devuelve: 'coordinador' | 'creador' | 'participante' | false
     */
    private function determinarRol($reunion, $user_id, $user_role) {
        if ($user_role === 'coordinador') {
            return 'coordinador';
        }
        if ($reunion['id_alumno_creador'] == $user_id) {
            return 'creador';
        }
        if ($reunion['id_alumno_participante'] == $user_id) {
            return 'participante';
        }
        return false;
    }

    /**
     * Muestra el formulario para editar una reunión
     * Verifica sesión, permisos (solo el creador puede editar) y que la reunión esté en estado 'pendiente'.
     * Carga la vista editar_reunion.php con los datos necesarios.
     *
     * /Recibe: GET[id]
     * /Devuelve: Incluye la vista app/views/editar_reunion.php o redirige en caso de error.
     */
    public function mostrarFormularioEditar() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
            header("Location: index.php?action=login");
            exit();
        }

        if (!isset($_GET['id'])) {
            header("Location: index.php?action=gestionar_reuniones");
            exit();
        }
        $id_reunion = (int)$_GET['id'];
        $reunion = $this->reunionModel->obtenerReunionPorId($id_reunion);

        if (!$reunion) {
            header("Location: index.php?action=gestionar_reuniones&error=reunion_no_encontrada");
            exit();
        }

        if ($reunion['id_alumno_creador'] != $_SESSION['user_id']) {
            header("Location: index.php?action=gestionar_reuniones&error=sin_permiso");
            exit();
        }

        if ($reunion['estado_general'] !== 'pendiente') {
            header("Location: index.php?action=gestionar_reuniones&error=no_editable");
            exit();
        }

        $nombre_usuario = $_SESSION['user_nombre'];
        include "app/views/editar_reunion.php";
    }

    /**
     * Procesa la actualización de una reunión
     * Valida método POST, permisos (solo creador) y estado (pendiente). Sanitiza campos,
     * verifica participante y delega la actualización al modelo. Redirige con mensaje.
     *
     * /Recibe: POST[id_reunion], POST[correo_participante], POST[objeto_creador], POST[objeto_participante],
     *          POST[lugar], POST[fecha], POST[hora], POST[notas]
     * /Devuelve: Redirección a gestionar_reuniones o a editar_reunion en caso de error.
     */
    public function actualizarReunion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=gestionar_reuniones");
            exit();
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
            header("Location: index.php?action=login");
            exit();
        }

        $id_reunion = (int)($_POST['id_reunion'] ?? 0);
        $reunion = $this->reunionModel->obtenerReunionPorId($id_reunion);

        if (!$reunion) {
            header("Location: index.php?action=gestionar_reuniones&error=reunion_no_encontrada");
            exit();
        }
        if ($reunion['id_alumno_creador'] != $_SESSION['user_id']) {
            header("Location: index.php?action=gestionar_reuniones&error=sin_permiso");
            exit();
        }
        if ($reunion['estado_general'] !== 'pendiente') {
            header("Location: index.php?action=gestionar_reuniones&error=no_editable");
            exit();
        }

        $correo_participante = filter_var($_POST['correo_participante'] ?? '', FILTER_SANITIZE_EMAIL);
        $objeto_creador = trim($_POST['objeto_creador'] ?? '');
        $objeto_participante = trim($_POST['objeto_participante'] ?? '');
        $lugar = trim($_POST['lugar'] ?? '');
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $notas = trim($_POST['notas'] ?? '');

        if (empty($correo_participante) || empty($objeto_creador) || empty($objeto_participante) || 
            empty($lugar) || empty($fecha) || empty($hora)) {
            header("Location: index.php?action=editar_reunion&id=$id_reunion&error=campos_vacios");
            exit();
        }

        if ($correo_participante === $_SESSION['user_correo']) {
            header("Location: index.php?action=editar_reunion&id=$id_reunion&error=mismo_correo");
            exit();
        }

        $alumno_participante = $this->reunionModel->buscarAlumnoPorCorreo($correo_participante);
        
        if (!$alumno_participante) {
            header("Location: index.php?action=editar_reunion&id=$id_reunion&error=alumno_no_encontrado");
            exit();
        }

        $data = [
            'id_reunion' => $id_reunion,
            'id_participante' => $alumno_participante['ID'],
            'correo_participante' => $correo_participante,
            'objeto_creador' => $objeto_creador,
            'objeto_participante' => $objeto_participante,
            'lugar' => $lugar,
            'fecha' => $fecha,
            'hora' => $hora,
            'notas' => $notas
        ];

        if ($this->reunionModel->actualizarReunion($data)) {
            header("Location: index.php?action=gestionar_reuniones&mensaje=reunion_actualizada");
        } else {
            header("Location: index.php?action=editar_reunion&id=$id_reunion&error=error_db");
        }
        exit();
    }
}
?>
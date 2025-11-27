<?php
class ResenaController {
    private $db;
    private $resenaModel;

    /**
     * Inicializa la conexión a la base de datos y crea una instancia del modelo ResenaModel.
     */
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->resenaModel = new ResenaModel($dbConnection);
    }

    /**
     * Muestra el menú de reseñas
     * Verifica sesión, obtiene el nombre del usuario desde la sesión y carga la vista del menú.
     *
     * /Recibe: Nada (usa $_SESSION)
     * /Devuelve: Incluye la vista app/views/resenas_menu.php
     */
    public function mostrarMenuResenas() {
        $this->verificarSesion();
        $nombre_usuario = $_SESSION['user_nombre'];
        include "app/views/resenas_menu.php";
    }

    /**
     *  Muestra el formulario para crear una reseña
     * Verifica sesión, obtiene nombre y correo del usuario (si existe) y carga la vista.
     *
     * /Recibe: Nada (usa $_SESSION)
     * /Devuelve: Incluye la vista app/views/crear_resenas.php
     */
    public function mostrarFormularioResena() {
        $this->verificarSesion();
        $nombre_usuario = $_SESSION['user_nombre'];
        $correo_usuario = $_SESSION['user_correo'] ?? '';
        include "app/views/crear_resenas.php";
    }

    /**
     * Procesa el envío del formulario de reseña
     * Valida método POST y sesión. Sanitiza y valida campos, busca al alumno evaluado por correo,
     * procesa imágenes (si las hay) y delega la inserción al modelo. Redirige según el resultado.
     *
     * /Recibe: POST[correo_evaluado], POST[categoria|objeto_evaluado], POST[calificacion], POST[comentario], FILES[imagenes]
     * /Devuelve: Redirección a ver_resenas o volver al formulario con error
     */
    public function procesarResena() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=crear_resena");
            exit();
        }
        $this->verificarSesion();

        $correo_evaluado = filter_var($_POST['correo_evaluado'] ?? '', FILTER_SANITIZE_EMAIL);
        $objeto_evaluado = trim($_POST['categoria'] ?? $_POST['objeto_evaluado'] ?? '');
        $calificacion = (int)($_POST['calificacion'] ?? 0);
        $comentario = trim($_POST['comentario'] ?? '');
        $user_id = $_SESSION['user_id'];

        if (empty($correo_evaluado) || empty($calificacion) || empty($comentario)) {
            header("Location: index.php?action=crear_resena&error=campos_vacios");
            exit();
        }

        $alumno_evaluado = $this->resenaModel->buscarAlumnoPorCorreo($correo_evaluado);
        if (!$alumno_evaluado) {
            header("Location: index.php?action=crear_resena&error=alumno_no_encontrado");
            exit();
        }
        $imagenes = $this->procesarImagenesResena($_FILES['imagenes'] ?? []);

        $data = [
            'id_reunion' => null,
            'id_alumno_resena' => $user_id,
            'id_alumno_evaluado' => $alumno_evaluado['ID'],
            'objeto_evaluado' => $objeto_evaluado,
            'calificacion' => $calificacion,
            'comentario' => $comentario,
            'imagenes' => json_encode($imagenes)
        ];

        if ($this->resenaModel->crearResena($data)) {
            header("Location: index.php?action=ver_resenas&success=1");
        } else {
            header("Location: index.php?action=crear_resena&error=error_db");
        }
        exit();
    }

    /**
     * Muestra listado de reseñas
     *  Verifica sesión, obtiene todas las reseñas, calcula promedio general y carga la vista
     * con los datos necesarios. Controla la existencia del archivo de vista.
     *
     * /Recibe: Nada (usa $_SESSION)
     * /Devuelve: Incluye la vista app/views/ver_resenas.php
     */
    public function verListadoResenas() {
        $this->verificarSesion();

        $resenas = $this->resenaModel->obtenerTodasResenas();

        // Calcular promedio
        $promedio_general = 0;
        if (count($resenas) > 0) {
            $suma = 0;
            foreach ($resenas as $r) {
                $suma += $r['calificacion'];
            }
            $promedio_general = round($suma / count($resenas), 1);
        }
        $nombre_usuario = $_SESSION['user_nombre'];
        $user_role = $_SESSION['user_role'];
        $user_id = $_SESSION['user_id'];
        
        if(file_exists("app/views/ver_resenas.php")){
            include "app/views/ver_resenas.php";
        } else {
            include "ver_resenas.php";
        }
    }

    /**
     * Muestra formulario de edición de reseña
     * Verifica sesión y permisos (solo alumnos pueden editar sus reseñas), valida el id recibido,
     * obtiene la reseña y carga la vista de edición.
     *
     * /Recibe: GET[id]
     * /Devuelve: Incluye la vista app/views/editar_resena.php o redirige en caso de error
     */
    public function mostrarFormularioEditar() {
        $this->verificarSesion();
        
        if ($_SESSION['user_role'] !== 'alumno') {
            header("Location: index.php?action=ver_resenas&error=sin_permiso");
            exit();
        }

        if (!isset($_GET['id'])) {
            header("Location: index.php?action=ver_resenas");
            exit();
        }

        $id_resena = (int)$_GET['id'];
        $resena = $this->resenaModel->obtenerResenaPorId($id_resena);

        if (!$resena || $resena['id_alumno_resena'] != $_SESSION['user_id']) {
            header("Location: index.php?action=ver_resenas&error=sin_permiso");
            exit();
        }

        $nombre_usuario = $_SESSION['user_nombre'];
        include "app/views/editar_resena.php";
    }

    /**
     * Actualiza una reseña existente
     * Valida método POST y permisos (solo alumno que creó la reseña). Procesa imágenes nuevas
     * y mantiene las existentes cuando aplique. Delegar la actualización al modelo y redirige.
     *
     * /Recibe: POST[id_resena], POST[objeto_evaluado], POST[calificacion], POST[comentario], FILES[imagenes] (opcional)
     * /Devuelve: Redirección a ver_resenas o volver al formulario con error
     */
    public function actualizarResena() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=ver_resenas");
            exit();
        }
        $this->verificarSesion();

        if ($_SESSION['user_role'] !== 'alumno') {
            header("Location: index.php?action=ver_resenas&error=sin_permiso");
            exit();
        }
        $id_resena = (int)($_POST['id_resena'] ?? 0);
        $objeto_evaluado = trim($_POST['objeto_evaluado'] ?? '');
        $calificacion = (int)($_POST['calificacion'] ?? 0);
        $comentario = trim($_POST['comentario'] ?? '');
        $user_id = $_SESSION['user_id'];

        if (empty($objeto_evaluado) || empty($calificacion) || empty($comentario)) {
            header("Location: index.php?action=editar_resena&id=$id_resena&error=campos_vacios");
            exit();
        }

        $resena = $this->resenaModel->obtenerResenaPorId($id_resena);
        if (!$resena || $resena['id_alumno_resena'] != $user_id) {
            header("Location: index.php?action=ver_resenas&error=sin_permiso");
            exit();
        }

        $imagenes_actuales = json_decode($resena['imagenes'] ?? '[]', true);
        if (!empty($_FILES['imagenes']['name'][0])) {
            $imagenes_nuevas = $this->procesarImagenesResena($_FILES['imagenes']);
            $imagenes = array_merge($imagenes_actuales, $imagenes_nuevas);
        } else {
            $imagenes = $imagenes_actuales;
        }

        $data = [
            'id_resena' => $id_resena,
            'objeto_evaluado' => $objeto_evaluado,
            'calificacion' => $calificacion,
            'comentario' => $comentario,
            'imagenes' => json_encode($imagenes)
        ];
        if ($this->resenaModel->actualizarResena($data)) {
            header("Location: index.php?action=ver_resenas&mensaje=resena_actualizada");
        } else {
            header("Location: index.php?action=editar_resena&id=$id_resena&error=error_db");
        }
        exit();
    }

    /**
     * Elimina una reseña según su  rol
     * Coordinadores pueden eliminar cualquiera; alumnos solo las suyas. Delegar al modelo y redirigir.
     *
     * /Recibe: GET[id]
     * /Devuelve: Redirección a ver_resenas con mensaje de resultado
     */
    public function eliminarResena() {
        $this->verificarSesion();

        if (!isset($_GET['id'])) {
            header("Location: index.php?action=ver_resenas");
            exit();
        }
        $id_resena = (int)$_GET['id'];
        $user_role = $_SESSION['user_role'];
        $user_id = $_SESSION['user_id'];

        if ($user_role === 'coordinador') {
            if ($this->resenaModel->eliminarResenaCoordinador($id_resena)) {
                header("Location: index.php?action=ver_resenas&mensaje=resena_eliminada");
            } else {
                header("Location: index.php?action=ver_resenas&error=error_eliminar");
            }
        } 
        elseif ($user_role === 'alumno') {
            if ($this->resenaModel->eliminarResena($id_resena, $user_id)) {
                header("Location: index.php?action=ver_resenas&mensaje=resena_eliminada");
            } else {
                header("Location: index.php?action=ver_resenas&error=sin_permiso");
            }
        } else {
            header("Location: index.php?action=ver_resenas&error=sin_permiso");
        }
        exit();
    }

    /**
     * Verifica que exista una sesión activa; si no, redirige al login
     */
    private function verificarSesion() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit();
        }
    }

    /**
     * Procesa y valida imágenes subidas para reseñas
     * Valida existencia, errores de subida, extensiones permitidas y tipo MIME real.
     * Crea el directorio uploads/resenas/ si no existe y guarda cada imagen con nombre único.
     * Registra errores en log y devuelve un array con los nombres de archivo guardados.
     *
     * /Recibe: $files (estructura $_FILES['imagenes'])
     * /Devuelve: array de nombres de archivo guardados (puede ser vacío)
     */
    private function procesarImagenesResena($files) {
        $imagenes = [];

        if (empty($files) || empty($files['name']) || empty($files['name'][0])) {
            return $imagenes;
        }
        $uploadDir = 'uploads/resenas/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $total_archivos = count($files['name']);

        for ($i = 0; $i < $total_archivos; $i++) {
            // Validar que no hay error en el archivo
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                error_log("Error al subir imagen $i: " . $files['error'][$i]);
                continue;
            }

            if (!isset($files['tmp_name'][$i]) || empty($files['tmp_name'][$i])) {
                continue;
            }

            $tmp_name = $files['tmp_name'][$i];
            $nombre_original = $files['name'][$i];
            
            $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
            
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($extension, $extensiones_permitidas)) {
                error_log("Extensión no permitida: $extension para archivo: $nombre_original");
                continue;
            }

            $tipo_mime = mime_content_type($tmp_name);
            $mimes_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($tipo_mime, $mimes_permitidos)) {
                error_log("Tipo MIME no válido: $tipo_mime");
                continue;
            }

            $nombre_unico = uniqid() . "_" . time() . "." . $extension;
            $destino = $uploadDir . $nombre_unico;
            
            if (move_uploaded_file($tmp_name, $destino)) {
                $imagenes[] = $nombre_unico;
                error_log("Imagen guardada correctamente: $nombre_unico");
            } else {
                error_log("Error al mover archivo a: $destino");
            }
        }
        
        return $imagenes;
    }
}
?>
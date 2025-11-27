<?php
class ProductoController {
    private $db;
    private $productoModel;

    /**
     * Inicializa la conexión a la base de datos y crea una instancia del modelo ProductoModel.
     */
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->productoModel = new ProductoModel($dbConnection);
    }

    /**
     * Determina la URL de redirección según el rol del usuario autenticado
     * Verifica el rol del usuario en la sesión (administrador, coordinador, alumno)
     * y retorna la URL correspondiente a su vista principal. Si no hay sesión activa,
     * redirige al login.
     * 
     * /Recibe: $_SESSION['user_role']
     * /Devuelve: String con la URL de redirección (ej: 'index.php?action=vistaAdmin')
     */
    private function getVistaSegunRol() {
        if (!isset($_SESSION['user_role'])) {
            return 'index.php?action=login';
        }
        switch ($_SESSION['user_role']) {
            case 'administrador':
                return 'index.php?action=vistaAdmin';
            case 'coordinador':
                return 'index.php?action=vistaCoordinador';
            case 'alumno':
                return 'index.php?action=vistaAlumnos';
            default:
                return 'index.php?action=login';
        }
    }

    /**
     * Crea un nuevo producto y lo sube al sistema
     * Valida que sea una petición POST y que el usuario esté autenticado.
     * Procesa los datos del formulario (nombre, marca, estado, descripción, categoría).
     * Valida y procesa la imagen del producto. Si todo es correcto, delega la creación
     * al modelo ProductoModel. Redirige con mensaje de éxito o error según el resultado.
     * 
     * /Recibe: POST[nombre], POST[marca], POST[estado], POST[descripcion], POST[categoria], FILES[imagen]
     * /Devuelve: Redirige a la vista del alumno
     */
    public function subirProducto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=agregarProducto");
            exit();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit();
        }
        $nombre = trim($_POST['nombre'] ?? '');
        $marca = trim($_POST['marca'] ?? '');
        $estado = $_POST['estado'] ?? '';
        $descripcion = trim($_POST['descripcion'] ?? '');
        $categoria = $_POST['categoria'] ?? 'Otro';
        $id_alumno = $_SESSION['user_id'];
        $resultadoImagen = $this->procesarImagen($_FILES['imagen']);

        if (isset($resultadoImagen['error'])) {
            header("Location: index.php?action=agregarProducto&error=" . urlencode($resultadoImagen['error']));
            exit();
        }

        $nombreImagen = $resultadoImagen['exito'];
        $resultado = $this->productoModel->crearProducto($nombre, $marca, $estado, $descripcion, $categoria, $nombreImagen, $id_alumno);
        $vistaDestino = $this->getVistaSegunRol();
        
        if ($resultado['exito']) {
            header("Location: {$vistaDestino}&exito=1");
        } else {
            header("Location: index.php?action=agregarProducto&error=" . urlencode($resultado['error']));
        }
        exit();
    }

    /**
     * Actualiza los datos de un producto existente
     * Valida que sea una petición POST y que el usuario esté autenticado.
     * Actualiza los campos del producto (nombre, marca, estado, descripción, categoría).
     * Si se proporciona una nueva imagen, la procesa y actualiza. Si no, mantiene la imagen actual.
     * Redirige con mensaje de éxito o error según el resultado.
     * 
     * /Recibe: POST[id_producto], POST[nombre], POST[marca], POST[estado], POST[descripcion], POST[categoria], FILES[imagen] (opcional)
     * /Devuelve:  Redirige a la vista del alumno
     */
    public function actualizarProducto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $vistaDestino = $this->getVistaSegunRol();
            header("Location: {$vistaDestino}");
            exit();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit();
        }
        $id_producto = $_POST['id_producto'];
        $nombre = trim($_POST['nombre'] ?? '');
        $marca = trim($_POST['marca'] ?? '');
        $estado = $_POST['estado'] ?? '';
        $descripcion = trim($_POST['descripcion'] ?? '');
        $categoria = $_POST['categoria'] ?? 'Otro';
        
        $nombreImagen = null;
        if (!empty($_FILES['imagen']['name'])) {
            $resultadoImagen = $this->procesarImagen($_FILES['imagen']);
            if (isset($resultadoImagen['error'])) {
                header("Location: index.php?action=editar_producto&id=$id_producto&error=" . urlencode($resultadoImagen['error']));
                exit();
            }
            $nombreImagen = $resultadoImagen['exito'];
        }
        $exito = $this->productoModel->actualizarProducto(
            $id_producto, 
            $nombre, 
            $marca, 
            $estado, 
            $descripcion, 
            $categoria, 
            $nombreImagen
        );
        $vistaDestino = $this->getVistaSegunRol();
        
        if ($exito) {
            header("Location: {$vistaDestino}&exito=editado");
        } else {
            header("Location: index.php?action=editar_producto&id=$id_producto&error=db_error");
        }
        exit();
    }

    /**
     * Elimina un producto del sistema
     * Obtiene el ID del producto desde GET, valida su existencia y lo elimina.
     * Delega la eliminación al modelo ProductoModel. Redirige a la vista principal
     * del usuario con mensaje de confirmación.
     * 
     * /Recibe: GET[id]
     * /Devuelve: Redirige a la vista del alumno
     */
    public function eliminarProducto() {
        if (!isset($_GET['id'])) {
            $vistaDestino = $this->getVistaSegunRol();
            header("Location: {$vistaDestino}");
            exit();
        }
        $id_producto = $_GET['id'];
        $this->productoModel->eliminarProducto($id_producto, $_SESSION['user_id']);
       
        $vistaDestino = $this->getVistaSegunRol();
        header("Location: {$vistaDestino}&mensaje=eliminado");
        exit();
    }

    /**
     * Agrega un comentario a un producto específico
     * Valida que sea una petición POST con datos de comentario.
     * Obtiene el ID del objeto (producto), el texto del comentario y el ID del alumno.
     * Delega la inserción al modelo ProductoModel. Redirige a la vista principal
     * con ancla al producto comentado.
     * 
     * /Recibe: POST[id_objeto], POST[comentario_texto], $_SESSION[user_id]
     * /Devuelve: Redirige a la vista del alumno
     */
    public function agregarComentario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentar'])) {
            $id_objeto = $_POST['id_objeto'];
            $comentario = $_POST['comentario_texto'];
            $id_alumno = $_SESSION['user_id'];
            $this->productoModel->agregarComentario($id_objeto, $id_alumno, $comentario);
            
            $vistaDestino = $this->getVistaSegunRol();
            header("Location: {$vistaDestino}#post-" . $id_objeto);
            exit();
        }
    }

    /**
     * Elimina un comentario de un producto
     * Obtiene el ID del comentario y el ID del objeto (producto) desde GET.
     * Delega la eliminación al modelo ProductoModel. Redirige a la vista principal
     * con ancla al producto si se especificó, de lo contrario redirige a la vista principal.
     * 
     * /Recibe: GET[id_comentario], GET[id_objeto]
     * /Devuelve: Redirige a la vista del alumno
     */
    public function eliminarComentario() {
        $id_comentario = $_GET['id_comentario'] ?? null;
        $id_objeto = $_GET['id_objeto'] ?? null;

        if ($id_comentario) {
            $this->productoModel->eliminarComentario($id_comentario);
        }
        
        // Redirigir según el rol del usuario
        $vistaDestino = $this->getVistaSegunRol();
        
        if ($id_objeto) {
            header("Location: {$vistaDestino}#post-" . $id_objeto);
        } else {
            header("Location: {$vistaDestino}");
        }
        exit();
    }

    /**
     * Actualiza el texto de un comentario existente
     * Valida que sea una petición POST. Obtiene el ID del comentario, el ID del objeto
     * (producto) y el nuevo texto del comentario. Delega la actualización al modelo
     * ProductoModel. Redirige a la vista principal con ancla al producto modificado.
     * 
     * /Recibe: POST[id_comentario], POST[id_objeto], POST[comentario_texto]
     * /Devuelve: Redirige a la vista del alumno
     */
    public function actualizarComentario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_comentario = $_POST['id_comentario'];
            $id_objeto = $_POST['id_objeto'];
            $texto = $_POST['comentario_texto'];
            
            $this->productoModel->actualizarComentario($id_comentario, $texto);
            
            // Redirigir según el rol del usuario
            $vistaDestino = $this->getVistaSegunRol();
            header("Location: {$vistaDestino}#post-" . $id_objeto);
            exit();
        }
    }

    /**
     *  Procesa y valida la imagen del producto antes de guardarla
     * Valida el tipo de archivo (JPG, JPEG, PNG) y el tamaño máximo (5MB).
     * Crea el directorio de uploads si no existe. Genera un nombre único usando MD5
     * y mueve el archivo temporal a la ruta final. 
     * 
     * Tipos permitidos: image/jpeg, image/png, image/jpg
     * Tamaño máximo: 5MB
     * 
     * /Recibe: $archivo (Array $_FILES['imagen'])
     * /Devuelve: Array con ['exito' => nombreArchivo] o ['error' => mensajeError]
     */
    private function procesarImagen($archivo) {
        $directorio_subida = 'uploads/productos/'; 
        
        if (!is_dir($directorio_subida)) {
            mkdir($directorio_subida, 0755, true);
        }

        $nombre_archivo = $archivo['name'];
        $tipo_archivo = $archivo['type'];
        $tamano_archivo = $archivo['size'];
        $temp_archivo = $archivo['tmp_name'];

        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($tipo_archivo, $tipos_permitidos)) {
            return ['error' => 'Error: Solo se permiten archivos JPG, JPEG o PNG.'];
        }

        if ($tamano_archivo > 5000000) { 
            return ['error' => 'Error: El archivo es demasiado grande (máx 5MB).'];
        }
        $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
        $nombre_unico = md5(uniqid(rand(), true)) . '.' . $extension;
        $ruta_completa = $directorio_subida . $nombre_unico;

        if (move_uploaded_file($temp_archivo, $ruta_completa)) {
            return ['exito' => $nombre_unico];
        } else {
            return ['error' => 'Error al mover el archivo al servidor.'];
        }
    }
}
?>
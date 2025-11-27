<?php
class chatController {

    private $db;
    private $chatModel;
    private $uploadDir = 'uploads/chat/';

    /**
     * Inicializa la conexión a la base de datos y crea una instancia del modelo chatModel.
     */
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        if (!class_exists('ChatModel')) {
            include_once 'app/models/ChatModel.php';
        }
        $this->chatModel = new ChatModel($dbConnection);

        // Crear directorio si no existe
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    /**
     * Maneja todas las peticiones AJAX del chat
     *  Procesa diferentes acciones del chat como enviar mensajes, actualizar
     * conversaciones, cargar lista de usuarios, eliminar/editar mensajes,
     * actualizar estado de escritura y subir imágenes. Verifica autenticación
     * del usuario antes de procesar cualquier acción.
     * 
     * Acciones de cada caso:
     * - update_user_list: Obtiene lista de usuarios del chat
     * - insert_chat: Envía un nuevo mensaje con imágenes opcionales
     * - show_chat: Carga la conversación con un usuario específico
     * - update_user_chat: Actualiza la conversación actual
     * - update_unread_message: Obtiene cantidad de mensajes no leídos
     * - show_typing_status: Muestra si el usuario está escribiendo
     * - update_typing_status: Actualiza el estado de escritura
     * - delete_message: Elimina un mensaje específico
     * - edit_message: Edita un mensaje existente con imágenes
     * - upload_temp_images: Sube imágenes temporales para previsualización
     * 
     * /Recibe: POST/GET[action], POST[to_user_id], POST[chat_message], FILES[chat_images]
     * /Devuelve: JSON con datos de la acción o error
     */
    public function handleAjaxRequest() {
        header('Content-Type: application/json; charset=utf-8');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'No autorizado']);
            exit();
        }
        $from_user_id = (int) $_SESSION['user_id'];
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        $output = ['error' => 'Acción no válida'];
        try {
            switch ($action) {
                case 'update_user_list':
                    $usuarios = $this->chatModel->getChatUsers($from_user_id);
                    $output = ['profileHTML' => $usuarios];
                    break;

                case 'insert_chat':
                    $to_user_id = isset($_POST['to_user_id']) ? (int)$_POST['to_user_id'] : 0;
                    $message = trim($_POST['chat_message'] ?? '');
                    $imagenes = [];
                    if (!empty($_FILES['chat_images']['name'][0])) {
                        $imagenes = $this->uploadChatImages($_FILES['chat_images']);
                        if (count($imagenes) > 10) {
                            $output = ['error' => 'Máximo 10 imágenes permitidas'];
                            break;
                        }
                    }
                    if ($to_user_id > 0 && (!empty($message) || !empty($imagenes))) {
                        $this->chatModel->insertChat($from_user_id, $to_user_id, $message, $imagenes);
                        $conversation = $this->chatModel->getUserChat($from_user_id, $to_user_id);
                        $output = ['conversation' => $conversation];
                    } else {
                        $output = ['error' => 'Datos incompletos'];
                    }
                    break;

                case 'show_chat':
                    $to_user_id = isset($_POST['to_user_id']) ? (int)$_POST['to_user_id'] : 0;
                    if ($to_user_id > 0) {
                        $conversation = $this->chatModel->getUserChat($from_user_id, $to_user_id);
                        $userDetails = $this->chatModel->getUserDetails($to_user_id);
                        $userHeaderHtml = $this->generarCabeceraUsuario($userDetails);
                        $output = [
                            'conversation' => $conversation,
                            'userSection' => $userHeaderHtml
                        ];
                    }
                    break;

                case 'update_user_chat':
                    $to_user_id = isset($_POST['to_user_id']) ? (int)$_POST['to_user_id'] : 0;
                    if ($to_user_id > 0) {
                        $conversation = $this->chatModel->getUserChat($from_user_id, $to_user_id);
                        $output = ['conversation' => $conversation];
                    }
                    break;

                case 'update_unread_message':
                    $to_user_id = isset($_POST['to_user_id']) ? (int)$_POST['to_user_id'] : 0;
                    if ($to_user_id > 0) {
                        $count = $this->chatModel->getUnreadMessageCount($from_user_id, $to_user_id);
                        $output = ['count' => $count];
                    }
                    break;
                
                case 'show_typing_status':
                    $to_user_id = isset($_POST['to_user_id']) ? (int)$_POST['to_user_id'] : 0;
                    if ($to_user_id > 0) {
                        $message = $this->chatModel->fetchIsTypeStatus($to_user_id);
                        $output = ['message' => $message];
                    }
                    break;

                case 'update_typing_status':
                    $is_type = $_POST['is_type'] ?? 'no';
                    $status = ($is_type === 'yes') ? 'si' : 'no';
                    $this->chatModel->updateTypingStatus($from_user_id, $status);
                    $output = ['success' => true];
                    break;

                case 'delete_message':
                    $message_id = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
                    if ($message_id > 0) {
                        $success = $this->chatModel->deleteMessage($message_id, $from_user_id);
                        $output = ['success' => $success];
                    }
                    break;

                case 'edit_message':
                    $message_id = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
                    $new_message = trim($_POST['new_message'] ?? '');
                    $currentMsg = $this->chatModel->getMessageDetails($message_id);
                    if (!$currentMsg || $currentMsg['id_emisor'] != $from_user_id) {
                        $output = ['error' => 'Mensaje no encontrado o sin permisos'];
                        break;
                    }
                    $existingImages = isset($_POST['existing_images']) ? json_decode($_POST['existing_images'], true) : [];
                    $newImages = [];
                    if (!empty($_FILES['edit_images']['name'][0])) {
                        $newImages = $this->uploadChatImages($_FILES['edit_images']);
                    }
                    $allImages = array_merge($existingImages, $newImages);
                    if (count($allImages) > 10) {
                        $output = ['error' => 'Máximo 10 imágenes permitidas'];
                        break;
                    }
                    if (!empty($currentMsg['imagenes'])) {
                        $oldImages = json_decode($currentMsg['imagenes'], true);
                        foreach ($oldImages as $oldImg) {
                            if (!in_array($oldImg, $existingImages) && file_exists($oldImg)) {
                                unlink($oldImg);
                            }
                        }
                    }
                    if ($message_id > 0) {
                        $success = $this->chatModel->editMessage($message_id, $from_user_id, $new_message, $allImages);
                        $output = ['success' => $success];
                    }
                    break;

                case 'upload_temp_images':
                    if (!empty($_FILES['temp_images']['name'][0])) {
                        $images = $this->uploadChatImages($_FILES['temp_images']);
                        $output = ['success' => true, 'images' => $images];
                    }
                    break;
            }

        } catch (Exception $e) {
            $output = ['error' => 'Error interno: ' . $e->getMessage()];
        }
        echo json_encode($output);
        exit();
    }

    /**
     * Sube imágenes del chat al servidor
     * Valida y procesa múltiples imágenes con extensiones permitidas (jpg, jpeg, png, gif, webp).
     * Crea nombres únicos para cada archivo y los almacena en el directorio de uploads.
     * Máximo 10 imágenes por petición.
     * 
     * /Recibe: $files (Array $_FILES con múltiples imágenes)
     * /Devuelve: Array con rutas de imágenes subidas exitosamente
     */
    private function uploadChatImages($files): array {
        $uploadedImages = [];
        $maxImages = 10;
        $fileCount = is_array($files['name']) ? count($files['name']) : 1;

        for ($i = 0; $i < min($fileCount, $maxImages); $i++) {
            $fileName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
            $fileTmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $fileError = is_array($files['error']) ? $files['error'][$i] : $files['error'];

            if ($fileError === UPLOAD_ERR_OK) {
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($extension, $allowedExt)) {
                    $newFileName = uniqid() . '_' . time() . '.' . $extension;
                    $destination = $this->uploadDir . $newFileName;

                    if (move_uploaded_file($fileTmp, $destination)) {
                        $uploadedImages[] = $destination;
                    }
                }
            }
        }
        return $uploadedImages;
    }

    /**
     * Genera el HTML de la cabecera del usuario con su información
     * Crea un bloque HTML que muestra el nombre, correo y carrera
     * del usuario. Incluye icono de usuario y estilos Bootstrap.
     * Escapa caracteres especiales para evitar inyección XSS.
     * 
     * /Recibe: $userData (Array con datos del usuario)
     * /Devuelve: String HTML con la cabecera formateada del usuario
     */
    private function generarCabeceraUsuario($userData) {
        if (!$userData) {
            return '<p class="text-muted">Usuario no encontrado</p>';
        }
        
        $nombre = htmlspecialchars($userData['nombre'] ?? 'Usuario');
        $correo = htmlspecialchars($userData['correo'] ?? '');
        $carrera = htmlspecialchars($userData['carrera'] ?? '');
        
        return '
            <div class="d-flex align-items-center">
                <i class="fas fa-user-circle fa-2x me-3"></i>
                <div>
                    <h5 class="mb-0">' . $nombre . '</h5>
                    <small class="text-muted">' . $correo . '</small><br>
                    <small class="badge bg-primary">' . $carrera . '</small>
                </div>
            </div>
        ';
    }
}
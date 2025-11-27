<?php
class ChatModel {
    private $db;
    private $chatTable = 'chat';
    private $chatSesionesTable = 'chat_sesiones';
    private $alumnosTable = 'alumnos';

    /**
     * Inicializa la conexión PDO que usará el modelo para las consultas.
     */
    public function __construct(PDO $dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Obtener lista de usuarios para el chat (excluye al usuario actual)
     * Recupera alumnos (ID, nombre, correo, carrera) y añade estado online.
     *
     * /Recibe: int $currentUserId
     * /Devuelve: array de usuarios con keys: userid, username, correo, carrera, online
     */
    public function getChatUsers(int $currentUserId): array {
        $sql = "SELECT ID AS userid, nombre, correo, carrera
                FROM {$this->alumnosTable}
                WHERE ID != :id
                ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $currentUserId, PDO::PARAM_INT);
        $stmt->execute();
        $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $usuarios = [];
        foreach ($alumnos as $alumno) {
            $online = $this->isUserOnline((int)$alumno['userid']);
            $usuarios[] = [
                'userid'   => (int)$alumno['userid'],
                'username' => $alumno['nombre'],
                'correo'   => $alumno['correo'],
                'carrera'  => $alumno['carrera'],
                'online'   => $online ? 1 : 0
            ];
        }
        return $usuarios;
    }

    /**
     * Obtener detalles de un usuario
     * Recoge información del alumno por su ID.
     *
     * /Recibe: int $userId
     * /Devuelve: array|null (datos del usuario) o null si no existe
     */
    public function getUserDetails(int $userId): ?array {
        $sql = "SELECT ID AS userid, nombre, correo, carrera, cuatrimestre, turno
                FROM {$this->alumnosTable}
                WHERE ID = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Comprueba si un usuario está online
     * Basado en la última actividad registrada en chat_sesiones.
     * Considera online si la última actividad fue hace menos de 60 segundos.
     *
     * /Recibe: int $userId
     * /Devuelve: bool
     */
    private function isUserOnline(int $userId): bool {
        $sql = "SELECT ultima_actividad 
                FROM {$this->chatSesionesTable}
                WHERE id_usuario = :id
                ORDER BY ultima_actividad DESC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false;

        $ultima = strtotime($row['ultima_actividad']);
        return (time() - $ultima) <= 60;
    }

    /**
     * Obtener conversaciones activas del usuario (contactos con los que ha chateado)
     * /Recibe: int $currentUserId
     * /Devuelve: array similar a getChatUsers() con usuarios activos en conversaciones
     */
    public function getActiveConversations(int $currentUserId): array {
        $sql = "SELECT DISTINCT 
                    CASE 
                        WHEN c.id_emisor = :user_id THEN c.id_receptor
                        ELSE c.id_emisor
                    END AS userid,
                    a.nombre AS username,
                    a.correo,
                    a.carrera
                FROM {$this->chatTable} c
                JOIN {$this->alumnosTable} a ON (
                    (c.id_emisor = :user_id AND a.ID = c.id_receptor) OR
                    (c.id_receptor = :user_id AND a.ID = c.id_emisor)
                )
                WHERE c.id_emisor = :user_id OR c.id_receptor = :user_id
                ORDER BY a.nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $currentUserId, PDO::PARAM_INT);
        $stmt->execute();
        $conversaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $usuarios = [];
        foreach ($conversaciones as $conv) {
            $online = $this->isUserOnline((int)$conv['userid']);
            $usuarios[] = [
                'userid'   => (int)$conv['userid'],
                'username' => $conv['username'],
                'correo'   => $conv['correo'],
                'carrera'  => $conv['carrera'],
                'online'   => $online ? 1 : 0
            ];
        }
        return $usuarios;
    }

    /**
     * Inserta un nuevo mensaje con imágenes opcionales
     * /Recibe: int $fromUserId, int $toUserId, string $message, array|null $imagenes (rutas)
     * /Devuelve: bool (éxito de la inserción)
     */
    public function insertChat(int $fromUserId, int $toUserId, string $message, ?array $imagenes = null): bool {
        $imagenesJson = $imagenes ? json_encode($imagenes) : null;
        
        $sql = "INSERT INTO {$this->chatTable}
                    (id_emisor, id_receptor, mensaje, fecha_hora, estado, imagenes)
                VALUES (:from, :to, :mensaje, NOW(), 1, :imagenes)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':from', $fromUserId, PDO::PARAM_INT);
            $stmt->bindValue(':to', $toUserId, PDO::PARAM_INT);
            $stmt->bindValue(':mensaje', $message, PDO::PARAM_STR);
            $stmt->bindValue(':imagenes', $imagenesJson, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("ChatModel::insertChat error: " . $e->getMessage());
            return false;
        }
    }

     /**
     * Devuelve el HTML de la conversación entre dos usuarios (con imágenes)
     * Marca mensajes como leídos antes de recuperar la conversación.
     *
     * /Recibe: int $from_user_id, int $to_user_id
     * /Devuelve: string (HTML de la conversación)
     */
    public function getUserChat(int $from_user_id, int $to_user_id): string {
        $this->markMessagesAsRead($from_user_id, $to_user_id);
        
        $sql = "SELECT id_chat, mensaje, id_emisor, id_receptor, fecha_hora, editado, imagenes, fecha_edicion
                FROM {$this->chatTable}
                WHERE (id_emisor = :from_id AND id_receptor = :to_id)
                   OR (id_emisor = :to_id AND id_receptor = :from_id)
                ORDER BY fecha_hora ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':from_id', $from_user_id, PDO::PARAM_INT);
        $stmt->bindValue(':to_id', $to_user_id, PDO::PARAM_INT);
        $stmt->execute();

        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($messages)) {
            return '<div class="text-center p-5 text-muted">No hay mensajes todavía.</div>';
        }

        $output = '<ul class="chat-bubbles">';

        foreach ($messages as $row) {
            $class = ($row['id_emisor'] == $from_user_id) ? 'sent' : 'replies';
            $hora = date('h:i A', strtotime($row['fecha_hora']));
            $editado = (!empty($row['editado']) && $row['editado'] == 1) ? ' (editado)' : '';

            $imagenes = !empty($row['imagenes']) ? json_decode($row['imagenes'], true) : [];

            $output .= '<li class="'.$class.'" data-msg-id="'.$row['id_chat'].'">';
            $output .= '<div class="message-content">';

            $output .= '<div class="message-bubble" id="bubble-'.$row['id_chat'].'" data-message-id="'.$row['id_chat'].'">';
            
            if (!empty(trim($row['mensaje']))) {
                $output .= '<p class="message-text">'.nl2br(htmlspecialchars($row['mensaje'])).'</p>';
            }

            if (!empty($imagenes)) {
                $output .= '<div class="message-images">';
                foreach ($imagenes as $imagen) {
                    $output .= '<img src="'.htmlspecialchars($imagen).'" alt="Imagen" class="chat-image" onclick="openImageModal(this.src)">';
                }
                $output .= '</div>';
            }

            $output .= '</div>'; 

            if ($class === 'sent') {
                $output .= '
                    <div class="message-actions" style="display:none;">
                        <button class="btn btn-sm btn-edit-msg" data-message-id="'.$row['id_chat'].'">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-delete-msg" data-message-id="'.$row['id_chat'].'">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                ';
            }

            if ($class === 'sent') {
                $output .= '<div class="message-edit-form" style="display:none;" data-message-id="'.$row['id_chat'].'">';
                $output .= '<textarea class="edit-message-input form-control mb-2" rows="2">'.htmlspecialchars($row['mensaje']).'</textarea>';
                
                $output .= '<div class="edit-images-preview" id="edit-preview-'.$row['id_chat'].'">';
                if (!empty($imagenes)) {
                    foreach ($imagenes as $idx => $imagen) {
                        $output .= '
                            <div class="edit-image-item" data-image-url="'.htmlspecialchars($imagen).'">
                                <img src="'.htmlspecialchars($imagen).'" alt="Imagen">
                                <button type="button" class="btn-remove-edit-image" onclick="removeEditImage(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        ';
                    }
                }
                $output .= '</div>';
                
                $output .= '
                    <div class="d-flex gap-2 align-items-center mt-2">
                        <label class="btn btn-sm btn-secondary" style="cursor:pointer;">
                            <i class="fas fa-image"></i> Agregar fotos
                            <input type="file" class="edit-image-input" accept="image/*" multiple style="display:none;" data-message-id="'.$row['id_chat'].'">
                        </label>
                        <button class="btn btn-sm btn-primary btn-save-edit" data-message-id="'.$row['id_chat'].'">Guardar</button>
                        <button class="btn btn-sm btn-secondary btn-cancel-edit">Cancelar</button>
                    </div>
                ';
                $output .= '</div>';
            }

            // HORA
            $output .= '<div class="message-time">'.$hora.$editado.'</div>';
            $output .= '</div>'; 
            $output .= '</li>';
        }

        $output .= '</ul>';
        return $output;
    }

    /**
     * Obtiene la cantidad de mensajes no leídos desde toUser hacia fromUser
     * /Recibe: int $fromUserId, int $toUserId
     * /Devuelve: int (cantidad)
     */
    public function getUnreadMessageCount(int $fromUserId, int $toUserId): int {
        $sql = "SELECT COUNT(*) AS total
                FROM {$this->chatTable}
                WHERE id_emisor = :to
                  AND id_receptor = :from
                  AND estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':to', $toUserId, PDO::PARAM_INT);
        $stmt->bindValue(':from', $fromUserId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (int)$row['total'] : 0;
    }

    /**
     * Alias para getUnreadMessageCount
     * /Recibe: int $fromUserId, int $toUserId
     * /Devuelve: int
     */
    public function countUnreadMessage(int $fromUserId, int $toUserId): int {
        return $this->getUnreadMessageCount($fromUserId, $toUserId);
    }

    /**
     * Edita un mensaje con opción de actualizar imágenes
     * Si $newImages === null conserva las imágenes actuales; si se pasa array reemplaza por las nuevas.
     *
     * /Recibe: int $messageId, int $userId, string $newContent, array|null $newImages
     * /Devuelve: bool
     */
    public function editMessage(int $messageId, int $userId, string $newContent, ?array $newImages = null): bool {
        // Obtener imágenes actuales si no se proporcionan nuevas
        if ($newImages === null) {
            $sql = "SELECT imagenes FROM {$this->chatTable} WHERE id_chat = :msg_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':msg_id', $messageId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $imagenesJson = $row ? $row['imagenes'] : null;
        } else {
            $imagenesJson = !empty($newImages) ? json_encode($newImages) : null;
        }

        $sql = "UPDATE {$this->chatTable}
                SET mensaje = :content, 
                    editado = 1,
                    fecha_edicion = NOW(),
                    imagenes = :imagenes
                WHERE id_chat = :msg_id AND id_emisor = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':content', $newContent, PDO::PARAM_STR);
        $stmt->bindValue(':imagenes', $imagenesJson, PDO::PARAM_STR);
        $stmt->bindValue(':msg_id', $messageId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Elimina un mensaje y sus imágenes asociadas (si existen en disco)
     * /Recibe: int $messageId, int $userId
     * /Devuelve: bool
     */
    public function deleteMessage(int $messageId, int $userId): bool {
        // Primero obtener las imágenes para eliminarlas del servidor
        $sql = "SELECT imagenes FROM {$this->chatTable} WHERE id_chat = :msg_id AND id_emisor = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':msg_id', $messageId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row['imagenes'])) {
            $imagenes = json_decode($row['imagenes'], true);
            foreach ($imagenes as $imagen) {
                if (file_exists($imagen)) {
                    unlink($imagen);
                }
            }
        }

        $sql = "DELETE FROM {$this->chatTable}
                WHERE id_chat = :msg_id AND id_emisor = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':msg_id', $messageId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Obtiene los detalles de un mensaje específico
     * 
     * /Recibe: int $messageId
     * /Devuelve: array|null
     */
    public function getMessageDetails(int $messageId): ?array {
        $sql = "SELECT * FROM {$this->chatTable} WHERE id_chat = :msg_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':msg_id', $messageId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Actualiza el estado "esta_escribiendo" en la tabla de sesiones
     * Si existe sesión previa la actualiza; si no, inserta una nueva.
     *
     * /Recibe: int $userId, string $status ('si'|'no')
     * /Devuelve: void
     */
    public function updateTypingStatus(int $userId, string $status): void {
        $sqlSelect = "SELECT id_sesion FROM {$this->chatSesionesTable}
                      WHERE id_usuario = :id
                      ORDER BY id_sesion DESC
                      LIMIT 1";
        $stmtSel = $this->db->prepare($sqlSelect);
        $stmtSel->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmtSel->execute();
        $row = $stmtSel->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $sqlUpd = "UPDATE {$this->chatSesionesTable}
                       SET esta_escribiendo = :estado,
                           ultima_actividad = NOW()
                       WHERE id_sesion = :sesion";
            $stmtUpd = $this->db->prepare($sqlUpd);
            $stmtUpd->bindValue(':estado', $status, PDO::PARAM_STR);
            $stmtUpd->bindValue(':sesion', $row['id_sesion'], PDO::PARAM_INT);
            $stmtUpd->execute();
        } else {
            $sqlIns = "INSERT INTO {$this->chatSesionesTable}
                       (id_usuario, ultima_actividad, esta_escribiendo)
                       VALUES (:id, NOW(), :estado)";
            $stmtIns = $this->db->prepare($sqlIns);
            $stmtIns->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmtIns->bindValue(':estado', $status, PDO::PARAM_STR);
            $stmtIns->execute();
        }
    }

    /**
     * Recupera si un usuario está escribiendo (para mostrar "Escribiendo...")
     * /Recibe: int $userId
     * /Devuelve: string (cadena HTML corta o vacía)
     */
    public function fetchIsTypeStatus(int $userId): string {
        $sql = "SELECT esta_escribiendo
                FROM {$this->chatSesionesTable}
                WHERE id_usuario = :id
                ORDER BY ultima_actividad DESC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['esta_escribiendo'] === 'si') {
            return ' - <small><em>Escribiendo...</em></small>';
        }

        return '';
    }

    /**
     * Marca mensajes como leídos (estado = 0)
     * 
     * /Recibe: int $from_user_id, int $to_user_id
     * /Devuelve: void
     */
    private function markMessagesAsRead(int $from_user_id, int $to_user_id): void {
        $sql = "UPDATE {$this->chatTable}
                SET estado = 0
                WHERE id_emisor = :to_id 
                  AND id_receptor = :from_id 
                  AND estado = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':from_id', $from_user_id, PDO::PARAM_INT);
        $stmt->bindValue(':to_id', $to_user_id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
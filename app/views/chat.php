<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header("Location: index.php?action=login");
    exit();
}

include_once 'config/dbConnection.php';
include_once 'app/models/ChatModel.php';

$db = conectar();
$chatModel = new ChatModel($db);

$start_with_user = isset($_GET['start_with']) ? (int)$_GET['start_with'] : null;
$usuarios = $chatModel->getActiveConversations((int)$_SESSION['user_id']);

if ($start_with_user && $start_with_user != $_SESSION['user_id']) {
    $existe = false;
    foreach ($usuarios as $u) {
        if ($u['userid'] == $start_with_user) {
            $existe = true;
            break;
        }
    }
    
    if (!$existe) {
        $nuevoUsuario = $chatModel->getUserDetails($start_with_user);
        if ($nuevoUsuario) {
            $usuarios[] = [
                'userid' => $start_with_user,
                'username' => $nuevoUsuario['nombre'],
                'correo' => $nuevoUsuario['correo'],
                'carrera' => $nuevoUsuario['carrera'],
                'online' => 0
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat - Trueques UPEMOR</title>
    <link rel="stylesheet" href="bootstrap/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        .message-images {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        
        .chat-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            cursor: pointer;
            object-fit: cover;
            transition: transform 0.2s;
        }
        
        .chat-image:hover {
            transform: scale(1.05);
        }
        
       
        .attach-btn {
            background: none;
            border: none;
            color: #6c757d;
            font-size: 1.3rem;
            cursor: pointer;
            padding: 0 10px;
        }
        
        .attach-btn:hover {
            color: #495057;
        }
        
     
        .image-preview-container {
            display: flex;
            gap: 8px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
            flex-wrap: wrap;
            max-height: 150px;
            overflow-y: auto;
        }
        
        .preview-image-item {
            position: relative;
            width: 80px;
            height: 80px;
        }
        
        .preview-image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .preview-image-item .remove-preview {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        
        .edit-images-preview {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        
        .edit-image-item {
            position: relative;
            width: 80px;
            height: 80px;
        }
        
        .edit-image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .btn-remove-edit-image {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
        }
        
        
        .image-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
        }
        
        .image-modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        .image-modal-close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
        }

        .contact-info small {
            font-size: 0.75rem;
            color: #000000ff;
        }

        .badge-carrera {
            font-size: 0.7rem;
            padding: 2px 6px;
        }
    </style>
</head>

<body class="dashboard-body">

   
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Trueques Escolares UPEMOR</h3>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php?action=vistaAlumnos"><i class="fas fa-home me-2"></i> Inicio</a>
            <a href="index.php?action=catalogo"><i class="fas fa-book-open me-2"></i> Catálogo</a>
            <a href="index.php?action=agregarProducto"><i class="fas fa-plus-circle me-2"></i> Agregar Producto</a>
            <a href="index.php?action=reuniones"><i class="fas fa-users me-2"></i> Reuniones</a>
            <a href="index.php?action=chat" class="active"><i class="fas fa-comments me-2"></i> Chat</a>
            <a href="index.php?action=resenas"><i class="fas fa-star me-2"></i> Reseñas</a>
        </nav>
        <div class="sidebar-footer">
            <a href="index.php?action=logout" class="logout-btn">
                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
            </a>
        </div>
    </aside>

   
    <div class="main-wrapper">

        <header class="dashboard-header">
            <img src="img/upemor.png" alt="Logo Upemor" class="header-logo">
            <h1 class="header-title">Universidad Politécnica del Estado de Morelos</h1>
            <div class="header-user">
                <span><?php echo htmlspecialchars($_SESSION['user_nombre'] ?? $_SESSION['nombre'] ?? 'Usuario'); ?></span>
                <i class="fas fa-user-circle ms-2"></i>
            </div>
        </header>

        <main class="dashboard-content">

            <div class="chat-wrapper">

                
                <div class="chat-contacts">
                    <h5>Contactos</h5>
                    
                    <?php if (empty($usuarios)): ?>
                        <div class="text-center text-muted p-3">
                            <p>No tienes conversaciones activas.</p>
                            <small>Inicia una conversación desde el perfil de un usuario en las publicaciones.</small>
                        </div>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($usuarios as $u): ?>
                            <li class="contact <?php echo ($start_with_user == $u['userid']) ? 'active' : ''; ?>"
                                data-touserid="<?= $u['userid'] ?>"
                                id="user_<?= $u['userid'] ?>">

                                <div class="contact-info">
                                    <strong><?= htmlspecialchars($u['username']) ?></strong>
                                    <small><?= htmlspecialchars($u['correo']) ?></small>
                                    <span class="badge badge-carrera bg-primary"><?= htmlspecialchars($u['carrera']) ?></span>
                                    <small id="isTyping_<?= $u['userid'] ?>"></small>
                                </div>

                                <span class="badge bg-danger ms-auto" id="unread_<?= $u['userid'] ?>"></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                
                <div class="chat-area">

                    <!-- Cabecera del usuario -->
                    <div id="userSection" class="chat-header">
                        <p class="text-muted mb-0">Selecciona un contacto para chatear...</p>
                    </div>

                    
                    <div id="conversation" class="chat-bubbles">
                        
                    </div>

                   
                    <div id="imagePreviewContainer" class="image-preview-container" style="display:none;"></div>

                   
                    <div class="chat-input message-input">
                        <input type="file" id="chatImageInput" accept="image/*" multiple style="display:none;" max="10">
                        
                        <button type="button" class="attach-btn" onclick="document.getElementById('chatImageInput').click()">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        
                        <input type="text" class="form-control chatMessage" placeholder="Escribe un mensaje...">
                        
                        <button class="submit"><i class="fa fa-paper-plane"></i></button>
                    </div>

                </div>

            </div>

        </main>

        <footer class="dashboard-footer">
            <p>Trueques UPEMOR — Chat</p>
        </footer>

    </div>

    
    <div id="imageModal" class="image-modal" onclick="closeImageModal()">
        <span class="image-modal-close" onclick="closeImageModal()">&times;</span>
        <img class="image-modal-content" id="modalImage">
    </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="bootstrap/js/chat.js"></script>

<?php if ($start_with_user): ?>
<script>
$(document).ready(function() {
    setTimeout(function() {
        var targetUser = <?= $start_with_user ?>;
        $('#user_' + targetUser).click();
    }, 500);
});
</script>
<?php endif; ?>

<script>

function openImageModal(src) {
    document.getElementById('imageModal').style.display = 'block';
    document.getElementById('modalImage').src = src;
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}

// Función para eliminar imagen durante la edición
function removeEditImage(btn) {
    $(btn).closest('.edit-image-item').remove();
}
</script>

</body>
</html>

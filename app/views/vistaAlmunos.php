<?php
// Evitar acceso directo (Seguridad)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    // Si se intenta abrir el archivo directo sin pasar por index, redirigir
    header("Location: ../../index.php?action=login&error=4"); 
    exit();
}

$nombre_usuario = $_SESSION['user_nombre'];
$id_usuario_actual = $_SESSION['user_id']; 
// --- FIN DE BLOQUE DE SEGURIDAD ---

// --- CARGAR PUBLICACIONES Y COMENTARIOS ---

// NOTA: Como este archivo se carga desde index.php, las rutas deben ser desde la raíz
// Usamos include_once para evitar error si index.php ya los cargó
include_once 'config/dbConnection.php'; 
include_once 'app/models/ProductoModel.php'; 

$productos = []; // Inicializamos
try {
    // Si $db ya existe (porque viene del index), lo usamos. Si no, conectamos.
    if (!isset($db)) {
        $db = conectar();
    }

    $productoModel = new ProductoModel($db);
    $productos = $productoModel->obtenerTodos(); 

    foreach ($productos as &$producto) {
        $producto['comentarios'] = $productoModel->obtenerComentariosPorObjeto($producto['id_objetos']);
    }
    unset($producto); // Romper referencia del último elemento
    
} catch (Exception $e) {
    error_log("Error al cargar productos o comentarios: " . $e->getMessage());
    echo "Error fatal al cargar publicaciones o comentarios. Revise los logs.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Alumno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap/css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="dashboard-body">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Trueques Escolares UPEMOR</h3>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php?action=vistaAlumnos" class="active"><i class="fas fa-home me-2"></i> Inicio</a>
            <a href="index.php?action=catalogo"><i class="fas fa-book-open me-2"></i> Catálogo</a>
            <a href="index.php?action=agregarProducto" ><i class="fas fa-plus-circle me-2"></i> Agregar Producto</a>
            <a href="index.php?action=reuniones"><i class="fas fa-users me-2"></i> Reuniones</a>
            <a href="index.php?action=chat"><i class="fas fa-comments me-2"></i> Chat</a>
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
                <span><?php echo htmlspecialchars($nombre_usuario); ?></span>
                <i class="fas fa-user-circle ms-2"></i>
            </div>
        </header>

        <main class="dashboard-content">
            <div class="feed">
                
                <?php if (empty($productos)): ?>
                    <div class="post" style="text-align: center; padding: 2rem;">
                        <p>No hay publicaciones todavía. ¡Sé el primero en <a href="index.php?action=agregarProducto">publicar un objeto</a>!</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($productos as $producto): ?>
                <article class="post" id="post-<?php echo $producto['id_objetos']; ?>"> 
                    
                    <div class="post-header" onclick="mostrarModal(this)"
                        data-nombre="<?php echo htmlspecialchars($producto['nombre_alumno'] ?? 'Usuario'); ?>"
                        data-carrera="<?php echo htmlspecialchars($producto['carrera'] ?? 'No especificada'); ?>"
                        data-turno="<?php echo htmlspecialchars($producto['turno'] ?? 'No especificado'); ?>"
                        data-userid="<?php echo $producto['ID']; ?>"
                        style="cursor: pointer;">
                        
                        
                        <div class="post-info">
                            <span class="username"><?php echo htmlspecialchars($producto['nombre_alumno'] ?? 'Usuario'); ?></span>
                            <span class="post-timestamp"><?php echo date('d M Y, H:i', strtotime($producto['fecha_publicacion'])); ?> h</span>
                        </div>
                    </div>

                    <div class="post-body">
                        <h4 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                        <p>
                            <strong>Estado:</strong> <?php echo htmlspecialchars($producto['estado']); ?><br>
                            <?php if (!empty($producto['marca'])): ?>
                                <strong>Marca:</strong> <?php echo htmlspecialchars($producto['marca']); ?><br>
                            <?php endif; ?>
                        </p>
                        <p><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
                    </div>

                    <?php if (!empty($producto['imagen'])): ?>
                        <img src="uploads/productos/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Imagen de <?php echo htmlspecialchars($producto['nombre']); ?>" class="post-image">
                    <?php endif; ?>
                    
                    <div class="post-actions">
                        <a href="#form-comentario-<?php echo $producto['id_objetos']; ?>" class="btn-comment" onclick="document.getElementById('input-comentario-<?php echo $producto['id_objetos']; ?>').focus(); return false;">
                            <i class="far fa-comment me-1"></i> Comentar
                        </a>
                    </div>
                    
                    <?php if ($producto['ID'] == $id_usuario_actual): ?>
                    <div class="post-owner-actions">
                        <a href="index.php?action=editar_producto&id=<?php echo $producto['id_objetos']; ?>" class="btn-accion-post btn-editar-post">
                            <i class="fas fa-edit me-1"></i> Editar
                        </a>
                        <a href="index.php?action=eliminar_producto&id=<?php echo $producto['id_objetos']; ?>" 
                           class="btn-accion-post btn-eliminar-post"
                           onclick="return confirm('¿Estás seguro de que quieres eliminar esta publicación?');">
                            <i class="fas fa-trash me-1"></i> Eliminar
                        </a>
                    </div>
                    <?php endif; ?>

                    
                    <div class="post-comments">
                        
                        <?php if (!empty($producto['comentarios'])): ?>
                            <h5>Comentarios (<?php echo count($producto['comentarios']); ?>)</h5>
                            <?php foreach ($producto['comentarios'] as $comentario): ?>
                                <div class="comment-item">
                                    <div class="comment-content">
                                        <span class="comment-author"
                                            onclick="mostrarModal(this)"
                                            data-nombre="<?php echo htmlspecialchars($comentario['nombre_alumno'] ?? 'Usuario'); ?>"
                                            data-carrera="<?php echo htmlspecialchars($comentario['carrera'] ?? 'No especificada'); ?>"
                                            data-turno="<?php echo htmlspecialchars($comentario['turno'] ?? 'No especificado'); ?>"
                                            data-userid="<?php echo $comentario['id_alumno']; ?>">
                                            <?php echo htmlspecialchars($comentario['nombre_alumno'] ?? 'Usuario'); ?>
                                        </span>
                                        
                                        <p class="comment-text"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                                        <span class="comment-timestamp"><?php echo date('d M Y, H:i', strtotime($comentario['fecha_comentario'])); ?></span>
                                        
                                        <?php if ($comentario['id_alumno'] == $id_usuario_actual): ?>
                                            <div class="comment-actions">
                                                <a href="index.php?action=editar_comentario&id_comentario=<?php echo $comentario['id_comentario']; ?>" class="comment-action-link">Editar</a>
                                                &middot;
                                                <a href="index.php?action=eliminar_comentario&id_comentario=<?php echo $comentario['id_comentario']; ?>&id_objeto=<?php echo $producto['id_objetos']; ?>" 
                                                   class="comment-action-link" 
                                                   onclick="return confirm('¿Estás seguro de que quieres eliminar este comentario?');">Eliminar</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="font-size: 0.9em; color: #777; text-align: center; margin-bottom: 15px;">Sé el primero en comentar.</p>
                        <?php endif; ?>

                        <form action="index.php?action=comentar" method="POST" class="comment-form-container" id="form-comentario-<?php echo $producto['id_objetos']; ?>">
                            <input type="hidden" name="comentar" value="1">
                            <input type="hidden" name="id_objeto" value="<?php echo htmlspecialchars($producto['id_objetos']); ?>">
                            <input type="text" name="comentario_texto" placeholder="Escribe un comentario..." required id="input-comentario-<?php echo $producto['id_objetos']; ?>">
                            <button type="submit">Enviar</button>
                        </form>
                    </div>

                </article>
                <?php endforeach; ?>
            </div>
        </main>

        <footer class="dashboard-footer">
            <p>© 2025 Trueque Escolar UPEMOR | Desarrollado como proyecto de Estancia II</p>
        </footer>
    </div>

    
    <div class="user-modal-overlay" id="userModal">
        <div class="user-modal-content">
            <button class="close-modal-btn" onclick="cerrarModal()">&times;</button>
            <div class="modal-profile-header">
                <img src="img/user_default.png" alt="Perfil" id="modal-img">
                <h2 id="modal-nombre">Nombre del Alumno</h2>
                <span id="modal-carrera-top">Carrera</span>
            </div>
            <div class="modal-profile-body">
                <p><strong><i class="fas fa-graduation-cap me-2"></i>Carrera:</strong> <span id="modal-carrera-body"></span></p>
                <p><strong><i class="fas fa-clock me-2"></i>Turno:</strong> <span id="modal-turno-body"></span></p>
            </div>
            <div class="modal-profile-actions">
                <button onclick="iniciarChat()" class="btn-role btn-alumno w-100">
                    <i class="fas fa-comments me-2"></i> Iniciar Chat
                </button>
            </div>
        </div>
    </div>
    <script>
        const modal = document.getElementById('userModal');
        const modalNombre = document.getElementById('modal-nombre');
        const modalCarreraTop = document.getElementById('modal-carrera-top');
        const modalCarreraBody = document.getElementById('modal-carrera-body');
        const modalTurnoBody = document.getElementById('modal-turno-body');

        function mostrarModal(elemento) {
            const nombre = elemento.getAttribute('data-nombre');
            const carrera = elemento.getAttribute('data-carrera');
            const turno = elemento.getAttribute('data-turno');
            const userId = elemento.getAttribute('data-userid');
            
            modalNombre.textContent = nombre;
            modalCarreraTop.textContent = carrera; 
            modalCarreraBody.textContent = carrera;
            modalTurnoBody.textContent = turno;
            
            modal.setAttribute('data-current-userid', userId);
            
            modal.style.display = 'flex';
        }

        function cerrarModal() {
            modal.style.display = 'none';
        }

        function iniciarChat() {
            const userId = modal.getAttribute('data-current-userid');
            
            if (userId) {
                window.location.href = `index.php?action=chat&start_with=${userId}`;
            } else {
                alert('Error al iniciar el chat');
            }
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                cerrarModal();
            }
        }

        window.addEventListener('DOMContentLoaded', (event) => {
            if (window.location.hash && window.location.hash.startsWith('#post-')) {
                const element = document.querySelector(window.location.hash);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    </script>
</body>
</html>
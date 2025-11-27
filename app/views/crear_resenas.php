<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header("Location: index.php?action=login"); 
    exit();
}

$nombre_usuario = $_SESSION['user_nombre'];
$correo_usuario = $_SESSION['user_correo'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Reseña - Trueques UPEMOR</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="bootstrap/css/style.css">
    <link rel="stylesheet" href="bootstrap/css/resenas.css">
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
            <a href="index.php?action=chat"><i class="fas fa-comments me-2"></i> Chat</a>
            <a href="index.php?action=resenas" class="active"><i class="fas fa-star me-2"></i> Reseñas</a>
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

        <main class="dashboard-content p-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        
                        <a href="index.php?action=resenas" class="btn btn-secondary mb-3">
                            <i class="fas fa-arrow-left me-2"></i> Volver
                        </a>

                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-star me-2"></i>Crear Nueva Reseña
                                </h4>
                            </div>
                            
                            <div class="card-body p-4">
                                
                                <?php if (isset($_GET['error'])): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <?php 
                                        $errores = [
                                            'campos_vacios' => 'Por favor completa todos los campos obligatorios.',
                                            'calificacion_invalida' => 'La calificación debe ser entre 1 y 5 estrellas.',
                                            'mismo_correo' => 'No puedes crear una reseña sobre ti mismo.',
                                            'alumno_no_encontrado' => 'El correo del alumno no está registrado.',
                                            'error_db' => 'Error al guardar la reseña. Intenta nuevamente.'
                                        ];
                                        echo $errores[$_GET['error']] ?? 'Error desconocido.';
                                        ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Nota:</strong> Comparte tu experiencia sobre un producto o trueque realizado con otro alumno.
                                </div>

                                <form action="index.php?action=procesar_resena" method="POST" enctype="multipart/form-data" id="formResena">
                                    
                                   
                                    <div class="mb-4">
                                        <h5 class="text-primary mb-3">
                                            <i class="fas fa-user me-2"></i>Tu Información
                                        </h5>
                                        <div class="form-group mb-3">
                                            <label class="form-label">Tu Correo:</label>
                                            <input type="email" class="form-control" 
                                                   value="<?= htmlspecialchars($correo_usuario) ?>" readonly>
                                        </div>
                                    </div>

                                    <hr>

                              
                                    <div class="mb-4">
                                        <h5 class="text-primary mb-3">
                                            <i class="fas fa-user-check me-2"></i>Alumno a Evaluar
                                        </h5>
                                        <div class="form-group mb-3">
                                            <label for="correo_evaluado" class="form-label">
                                                <i class="fas fa-envelope me-1"></i>Correo del Alumno: <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" 
                                                   class="form-control" 
                                                   id="correo_evaluado" 
                                                   name="correo_evaluado" 
                                                   required 
                                                   placeholder="ejemplo@upemor.edu.mx">
                                            <small class="form-text text-muted">
                                                Ingresa el correo del alumno que quieres evaluar.
                                            </small>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="objeto_evaluado" class="form-label">
                                                <i class="fas fa-box me-1"></i>Producto/Objeto Recibido: <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="objeto_evaluado" 
                                                   name="objeto_evaluado" 
                                                   required 
                                                   placeholder="Ej: Mouse Gamer Logitech">
                                        </div>
                                    </div>

                                    <hr>

                                  
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-star me-2"></i>Calificación <span class="text-danger">*</span>
                                        </label>
                                        <div class="text-center py-3">
                                            <div class="star-rating">
                                                <input type="radio" name="calificacion" id="star5" value="5" required>
                                                <label for="star5"><i class="fas fa-star"></i></label>
                                                
                                                <input type="radio" name="calificacion" id="star4" value="4">
                                                <label for="star4"><i class="fas fa-star"></i></label>
                                                
                                                <input type="radio" name="calificacion" id="star3" value="3">
                                                <label for="star3"><i class="fas fa-star"></i></label>
                                                
                                                <input type="radio" name="calificacion" id="star2" value="2">
                                                <label for="star2"><i class="fas fa-star"></i></label>
                                                
                                                <input type="radio" name="calificacion" id="star1" value="1">
                                                <label for="star1"><i class="fas fa-star"></i></label>
                                            </div>
                                            <p class="text-muted mt-2" id="rating-text">Selecciona una calificación</p>
                                        </div>
                                    </div>

                                   
                                    <div class="mb-4">
                                        <label for="comentario" class="form-label fw-bold">
                                            <i class="fas fa-comment me-2"></i>Tu Opinión <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control" 
                                                  id="comentario" 
                                                  name="comentario" 
                                                  rows="5" 
                                                  required
                                                  placeholder="Comparte tu experiencia sobre el producto recibido, estado, funcionalidad, etc..."></textarea>
                                        <small class="form-text text-muted">
                                            Describe el estado del producto y tu experiencia general.
                                        </small>
                                    </div>

                                    
                                    <div class="mb-4">
                                        <label for="imagenes" class="form-label fw-bold">
                                            <i class="fas fa-images me-2"></i>Fotos del Producto (Opcional)
                                        </label>
                                        <input type="file" 
                                               class="form-control" 
                                               id="imagenes" 
                                               name="imagenes[]" 
                                               accept="image/*" 
                                               multiple>
                                        <small class="form-text text-muted">
                                            Puedes subir hasta 5 imágenes (JPG, PNG, GIF)
                                        </small>
                                        
                                       
                                        <div class="preview-images" id="imagePreview"></div>
                                    </div>

                                    <hr class="my-4">

                                  
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-paper-plane me-2"></i>Publicar Reseña
                                        </button>
                                        <a href="index.php?action=resenas" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-2"></i>Cancelar
                                        </a>
                                    </div>
                                </form>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>

        <footer class="dashboard-footer">
            <p>© 2025 Trueque Escolar UPEMOR | Desarrollado como proyecto de Estancia II</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="bootstrap/js/resenas.js"></script>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['user_id']) || 
    !in_array($_SESSION['user_role'], ['alumno', 'coordinador'])) {
    header("Location: index.php?action=login"); 
    exit();
}


function mostrarEstrellas($calificacion) {
    $estrellas = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $calificacion) {
            $estrellas .= '<i class="fas fa-star text-warning"></i>';
        } else {
            $estrellas .= '<i class="far fa-star text-warning"></i>';
        }
    }
    return $estrellas;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Reseñas - Trueques UPEMOR</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="bootstrap/css/style.css">
</head>
<body class="dashboard-body">

    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Trueques Escolares UPEMOR</h3>
        </div>
        <nav class="sidebar-nav">
            <?php if ($user_role === 'alumno'): ?>
                <a href="index.php?action=vistaAlumnos"><i class="fas fa-home me-2"></i> Inicio</a>
                <a href="index.php?action=catalogo"><i class="fas fa-book-open me-2"></i> Catálogo</a>
                <a href="index.php?action=agregarProducto"><i class="fas fa-plus-circle me-2"></i> Agregar Producto</a>
                <a href="index.php?action=reuniones"><i class="fas fa-users me-2"></i> Reuniones</a>
                <a href="index.php?action=chat"><i class="fas fa-comments me-2"></i> Chat</a>
                <a href="index.php?action=resenas" class="active"><i class="fas fa-star me-2"></i> Reseñas</a>
            <?php elseif ($user_role === 'coordinador'): ?>
                <a href="index.php?action=vistaCoordinador"><i class="fas fa-home me-2"></i> Inicio</a>
                <a href="index.php?action=gestionar_reuniones"><i class="fas fa-calendar-check me-2"></i> Reuniones</a>
                <a href="index.php?action=ver_resenas" class="active"><i class="fas fa-star me-2"></i> Reseñas</a>
            <?php endif; ?>
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
            <div class="container-fluid">
                
                <div class="row mb-4">
                    <div class="col-12">
                        <?php if ($user_role === 'alumno'): ?>
                            <a href="index.php?action=resenas" class="btn btn-secondary mb-3">
                                <i class="fas fa-arrow-left me-2"></i> Volver
                            </a>
                        <?php elseif ($user_role === 'coordinador'): ?>
                        <?php endif; ?>
                        
                        <h2>
                            <i class="fas fa-list me-2"></i>Reseñas de la Comunidad
                            <?php if ($user_role === 'coordinador'): ?>
                                <span class="badge bg-primary ms-2">Modo Coordinador</span>
                            <?php endif; ?>
                        </h2>
                    </div>
                </div>

                
                <?php if (isset($_GET['mensaje'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        $mensajes = [
                            'resena_creada' => '¡Reseña publicada exitosamente! Gracias por compartir tu experiencia.',
                            'resena_actualizada' => '¡Reseña actualizada correctamente!',
                            'resena_eliminada' => 'Reseña eliminada exitosamente.'
                        ];
                        echo $mensajes[$_GET['mensaje']] ?? 'Operación exitosa.';
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        $errores = [
                            'sin_permiso' => 'No tienes permiso para realizar esta acción.',
                            'error_eliminar' => 'Error al eliminar la reseña.'
                        ];
                        echo $errores[$_GET['error']] ?? 'Ocurrió un error.';
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <?php if (empty($resenas)): ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle fa-3x mb-3"></i>
                                <h5>Aún no hay reseñas</h5>
                                <?php if ($user_role === 'alumno'): ?>
                                    <p>Sé el primero en compartir tu experiencia sobre un trueque.</p>
                                    <a href="index.php?action=crear_resena" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus-circle me-2"></i>Publicar Reseña
                                    </a>
                                <?php else: ?>
                                    <p>No hay reseñas para mostrar en este momento.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($resenas as $resena): ?>
                            <?php 
                                $imagenes = json_decode($resena['imagenes'] ?? '[]', true);
                                $es_propia = ($resena['id_alumno_resena'] == $user_id);
                            ?>

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 resena-card shadow-sm border-0">
                                    <div class="card-header bg-white border-bottom">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 fw-bold text-primary">
                                                    <i class="fas fa-user-circle me-1"></i>
                                                    <?= htmlspecialchars($resena['nombre_resena']) ?>
                                                </h6>
                                                <small class="text-muted"><?= htmlspecialchars($resena['carrera_resena']) ?></small>
                                            </div>
                                            <div class="text-end">
                                                <div class="mb-1">
                                                    <?= mostrarEstrellas($resena['calificacion']) ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($resena['fecha_creacion'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                       
                                        <div class="mb-3 p-2 bg-light rounded">
                                            <small class="text-muted d-block mb-1">
                                                <i class="fas fa-box me-1"></i>Producto Evaluado:
                                            </small>
                                            <strong class="text-dark"><?= htmlspecialchars($resena['objeto_evaluado']) ?></strong>
                                        </div>

                                        
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="fas fa-arrow-right me-1"></i>Evaluado:
                                            </small>
                                            <span class="fw-medium"><?= htmlspecialchars($resena['nombre_evaluado']) ?></span>
                                        </div>

                                        
                                        <div class="mb-3">
                                            <p class="card-text"><?= nl2br(htmlspecialchars($resena['comentario'])) ?></p>
                                        </div>

                                       
                                        <?php if (!empty($imagenes)): ?>
                                            <div class="resena-images">
                                                <?php foreach ($imagenes as $imagen): ?>
                                                    <img src="uploads/resenas/<?= htmlspecialchars($imagen) ?>" 
                                                         alt="Imagen del producto"
                                                         data-bs-toggle="modal" 
                                                         data-bs-target="#imageModal"
                                                         onclick="showImage('uploads/resenas/<?= htmlspecialchars($imagen) ?>')"
                                                         onerror="this.src='img/no-image.png'">
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        
                                        <?php if ($user_role === 'alumno' && $es_propia): ?>
                                            <!-- Solo el dueño puede editar/eliminar -->
                                            <div class="action-buttons">
                                                <a href="index.php?action=editar_resena&id=<?= $resena['id_resena'] ?>" 
                                                   class="btn btn-sm btn-warning btn-edit-resena">
                                                    <i class="fas fa-edit me-1"></i>Editar
                                                </a>
                                                <a href="index.php?action=eliminar_resena&id=<?= $resena['id_resena'] ?>" 
                                                   class="btn btn-sm btn-danger btn-delete-resena"
                                                   onclick="return confirm('¿Estás seguro de eliminar esta reseña?')">
                                                    <i class="fas fa-trash me-1"></i>Eliminar
                                                </a>
                                            </div>
                                        <?php elseif ($user_role === 'coordinador'): ?>
                                           
                                            <div class="action-buttons">
                                                <a href="index.php?action=eliminar_resena&id=<?= $resena['id_resena'] ?>" 
                                                   class="btn btn-sm btn-danger btn-delete-resena w-100"
                                                   onclick="return confirm('¿Estás seguro de eliminar esta reseña? Esta acción no se puede deshacer.')">
                                                    <i class="fas fa-trash-alt me-1"></i>Eliminar Reseña
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <footer class="dashboard-footer">
            <p>© 2025 Trueque Escolar UPEMOR | Desarrollado como proyecto de Estancia II</p>
        </footer>
    </div>

    
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Imagen del Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="modalImage" class="modal-img" alt="Imagen ampliada">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function showImage(src) {
            document.getElementById('modalImage').src = src;
        }
    </script>
</body>
</html>
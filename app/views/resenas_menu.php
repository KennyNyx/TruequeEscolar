<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header("Location: index.php?action=login"); 
    exit();
}

$nombre_usuario = $_SESSION['user_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseñas - Trueques UPEMOR</title>
    
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
                <div class="row mb-4">
                    <div class="col-12">
                        <h2>
                            <i class="fas fa-star me-2"></i>Reseñas
                        </h2>
                    </div>
                </div>

                <div class="row g-4">
                    
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm border-0 hover-card">
                            <div class="card-body text-center p-5">
                                <div class="mb-4">
                                    <i class="fas fa-pen-to-square fa-5x text-primary"></i>
                                </div>
                                <h3 class="card-title mb-3">Crear Reseña</h3>
                                <p class="card-text text-muted mb-4">
                                    Comparte tu experiencia sobre productos o trueques realizados. 
                                    Califica y evalúa a otros usuarios de la comunidad.
                                </p>
                                <a href="index.php?action=crear_resena" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus-circle me-2"></i>Nueva Reseña
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm border-0 hover-card">
                            <div class="card-body text-center p-5">
                                <div class="mb-4">
                                    <i class="fas fa-list fa-5x text-success"></i>
                                </div>
                                <h3 class="card-title mb-3">Ver Reseñas</h3>
                                <p class="card-text text-muted mb-4">
                                    Explora las reseñas de la comunidad y conoce 
                                    las experiencias de otros alumnos sobre sus trueques.
                                </p>
                                <a href="index.php?action=ver_resenas" class="btn btn-success btn-lg">
                                    <i class="fas fa-eye me-2"></i>Explorar Reseñas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h5 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>¿Cómo funcionan las reseñas?
                            </h5>
                            <hr>
                            <ul class="mb-0">
                                <li>Puedes crear reseñas sobre cualquier producto o trueque realizado</li>
                                <li>Califica de 1 a 5 estrellas el producto recibido y la experiencia</li>
                                <li>Agrega hasta 5 fotos del producto</li>
                                <li>Todas las reseñas son públicas y visibles para la comunidad</li>
                                <li>Solo puedes eliminar tus propias reseñas</li>
                            </ul>
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
    
    <style>
        .hover-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
    </style>
</body>
</html>
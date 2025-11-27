<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header("Location: ../../index.php?action=login&error=4"); 
    exit();
}

$nombre_usuario = $_SESSION['user_nombre'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reuniones - Trueques UPEMOR</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="bootstrap/css/style.css?v=2.0"> 
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
            <a href="index.php?action=reuniones" class="active"><i class="fas fa-users me-2"></i> Reuniones</a>
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
            
            <div class="reunion-section">
                <h2 class="reunion-header-title">Gestión de Reuniones</h2>
                <p class="reunion-header-subtitle">Coordina tus intercambios de manera segura</p>

                <div class="reunion-options-container">
                    
                    <a href="index.php?action=crear_reunion" class="reunion-card card-create">
                        <div class="reunion-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="reunion-info">
                            <h3>Crear Reunión</h3>
                            <p>Agenda una nueva cita con otro alumno para realizar el intercambio.</p>
                            <span class="btn-reunion">Agendar Ahora</span>
                        </div>
                    </a>

                    <a href="index.php?action=gestionar_reuniones" class="reunion-card card-manage">
                        <div class="reunion-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="reunion-info">
                            <h3>Mis Reuniones</h3>
                            <p>Revisa el estado de tus citas, confirma asistencia o cancela.</p>
                            <span class="btn-reunion">Ver Estado</span>
                        </div>
                    </a>

                </div>
            </div>

        </main>

        <footer class="dashboard-footer">
            <p>© 2025 Trueque Escolar UPEMOR | Desarrollado como proyecto de Estancia II</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
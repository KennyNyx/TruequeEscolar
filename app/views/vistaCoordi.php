<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
    header("Location: index.php?action=login&error=4"); 
    exit();
}
$nombre_usuario = $_SESSION['user_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Coordinador</title>
    <link rel="stylesheet" href="bootstrap/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="dashboard-body">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Trueques Escolares UPEMOR</h3>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php?action=vistaCoordinador" class="active">
                <i class="fas fa-home me-2"></i> Inicio
            </a>
            <a href="index.php?action=gestionar_reuniones">
                <i class="fas fa-calendar-check me-2"></i> Gestionar Reuniones
            </a>
           
            <a href="index.php?action=ver_resenas">
                <i class="fas fa-star me-2"></i> Gestionar Reseñas
            </a>
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
                        <h2 class="mb-3">
                            <i class="fas fa-user-tie me-2"></i>
                            Panel de Coordinador
                        </h2>
                        <p class="text-muted">Gestiona y supervisa las reuniones de trueque entre estudiantes</p>
                    </div>
                </div>

                <!-- Tarjetas de Estadísticas -->
                <?php if (isset($total_reuniones)): ?>
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card text-white bg-info h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Total</h6>
                                        <h2 class="mb-0"><?= $total_reuniones ?></h2>
                                    </div>
                                    <div>
                                        <i class="fas fa-calendar fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card text-white bg-warning h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Pendientes</h6>
                                        <h2 class="mb-0"><?= $pendientes ?></h2>
                                    </div>
                                    <div>
                                        <i class="fas fa-clock fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card text-white bg-success h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Confirmadas</h6>
                                        <h2 class="mb-0"><?= $confirmadas ?></h2>
                                    </div>
                                    <div>
                                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card text-white bg-danger h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-uppercase mb-1">Por Revisar</h6>
                                        <h2 class="mb-0"><?= $requieren_atencion ?></h2>
                                    </div>
                                    <div>
                                        <i class="fas fa-exclamation-circle fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tarjetas de Funciones -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Reuniones de Trueque
                                </h5>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <p class="text-muted mb-4">
                                    Revisa, confirma o cancela las reuniones programadas entre estudiantes. 
                                    Tu confirmación es necesaria para que las reuniones se realicen.
                                </p>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Tu rol:</strong> Debes revisar cada reunión y confirmar que cumple 
                                    con las políticas de la universidad antes de que se lleve a cabo.
                                </div>

                                <div class="mt-auto">
                                    <a href="index.php?action=gestionar_reuniones" class="btn btn-primary w-100">
                                        <i class="fas fa-tasks me-2"></i>
                                        Ver Todas las Reuniones
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--   Gestión de Reseñas -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">
                                    <i class="fas fa-star me-2"></i>
                                    Gestión de Reseñas
                                </h5>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <p class="text-muted mb-4">
                                    Supervisa las reseñas publicadas por los estudiantes. 
                                    Puedes eliminar reseñas que no cumplan con las políticas de convivencia.
                                </p>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <strong>Moderación:</strong> Elimina reseñas con contenido inapropiado, 
                                    ofensivo o que violen las normas de la comunidad.
                                </div>

                                <div class="mt-auto">
                                    <a href="index.php?action=ver_resenas" class="btn btn-warning w-100">
                                        <i class="fas fa-eye me-2"></i>
                                        Ver Todas las Reseñas
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Funciones del Coordinador -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    Funciones del Coordinador
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Aprobar reuniones programadas
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fas fa-eye text-primary me-2"></i>
                                        Supervisar intercambios entre alumnos
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fas fa-ban text-danger me-2"></i>
                                        Cancelar reuniones inapropiadas
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fas fa-clipboard-check text-info me-2"></i>
                                        Verificar cumplimiento de políticas
                                    </li>
                                    <li class="list-group-item">
                                        <i class="fas fa-trash-alt text-warning me-2"></i>
                                        Moderar reseñas de la comunidad
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instrucciones -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Importante
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>Antes de confirmar una reunión, verifica:</strong></p>
                                <ul class="mb-3">
                                    <li>Que el lugar sea apropiado dentro de las instalaciones</li>
                                    <li>Que el horario no interfiera con actividades académicas</li>
                                    <li>Que los objetos a intercambiar sean adecuados</li>
                                    <li>Que ambos estudiantes estén de acuerdo</li>
                                </ul>
                                
                                <p class="mb-2"><strong>Al moderar reseñas, considera eliminar:</strong></p>
                                <ul class="mb-0">
                                    <li>Contenido ofensivo o discriminatorio</li>
                                    <li>Información personal sensible</li>
                                    <li>Reseñas falsas o spam</li>
                                    <li>Violaciones a las políticas de la universidad</li>
                                </ul>
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
</body>
</html>
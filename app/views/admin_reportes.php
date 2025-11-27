<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header("Location: index.php?action=login");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Reportes - Admin</title>
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
            <a href="index.php?action=vistaAdmin"><i class="fas fa-home me-2"></i> Inicio</a>
            <a href="index.php?action=mostrarRegistro"><i class="fas fa-user-plus me-2"></i> Crear cuenta</a>
            <a href="index.php?action=user_consultar"><i class="fas fa-users me-2"></i> Consultar Alumnos</a>
            <a href="index.php?action=admin_consultar"><i class="fas fa-user-shield me-2"></i> Consultar Administradores</a>
            <a href="index.php?action=coord_consultar"><i class="fas fa-user-tie me-2"></i> Consultar Coordinadores</a>
            <a href="index.php?action=generar_reportes" class="active"><i class="fas fa-file-alt me-2"></i> Generar Reportes</a>
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
            <div class="container-fluid">
            
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-3">
                            <i class="fas fa-chart-bar me-2"></i>
                            Sistema de Reportes Estadísticos
                        </h2>
                        <p class="text-muted">Genera reportes en PDF sobre las actividades del sistema de trueques</p>
                    </div>
                </div>

               
                <?php
                $reporteModel = new ReporteModel($this->db ?? conectar());
                $stats = $reporteModel->obtenerEstadisticasGenerales();
                ?>
                <div class="stats-card">
                    <div class="row">
                        <div class="col-md-3 stat-item">
                            <div class="stat-number"><?php echo $stats['total_usuarios'] ?? 0; ?></div>
                            <div class="stat-label">Usuarios Registrados</div>
                        </div>
                        <div class="col-md-3 stat-item">
                            <div class="stat-number"><?php echo $stats['total_objetos'] ?? 0; ?></div>
                            <div class="stat-label">Objetos Publicados</div>
                        </div>
                        <div class="col-md-3 stat-item">
                            <div class="stat-number"><?php echo $stats['total_reuniones'] ?? 0; ?></div>
                            <div class="stat-label">Reuniones Agendadas</div>
                        </div>
                        <div class="col-md-3 stat-item">
                            <div class="stat-number"><?php echo $stats['total_resenas'] ?? 0; ?></div>
                            <div class="stat-label">Reseñas Publicadas</div>
                        </div>
                    </div>
                </div>

               
                <div class="row g-4">
                    
                    <div class="col-md-4">
                        <div class="card report-card">
                            <div class="card-body text-center">
                                <div class="report-icon" style="color: #3498db;">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <div class="report-title">
                                    Objetos por Categoría
                                </div>
                                <div class="report-description">
                                    Estadísticas de los objetos más publicados, agrupados por categoría. 
                                    Incluye gráficos y análisis detallado.
                                </div>
                                <a href="index.php?action=reporte_categorias" 
                                   class="btn btn-primary btn-generate" 
                                   target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i>
                                    Generar PDF
                                </a>
                            </div>
                        </div>
                    </div>

                   
                    <div class="col-md-4">
                        <div class="card report-card">
                            <div class="card-body text-center">
                                <div class="report-icon" style="color: #e74c3c;">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="report-title">
                                    Lugares de Reunión
                                </div>
                                <div class="report-description">
                                    Análisis de los lugares más frecuentados para realizar intercambios. 
                                    Incluye estadísticas de confirmación.
                                </div>
                                <a href="index.php?action=reporte_lugares" 
                                   class="btn btn-danger btn-generate" 
                                   target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i>
                                    Generar PDF
                                </a>
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-4">
                        <div class="card report-card">
                            <div class="card-body text-center">
                                <div class="report-icon" style="color: #27ae60;">
                                    <i class="fas fa-handshake"></i>
                                </div>
                                <div class="report-title">
                                    Estado de Reuniones
                                </div>
                                <div class="report-description">
                                    Reporte completo de reuniones: confirmadas, canceladas y pendientes. 
                                    Con porcentajes y detalles.
                                </div>
                                <a href="index.php?action=reporte_reuniones" 
                                   class="btn btn-success btn-generate" 
                                   target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i>
                                    Generar PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

               
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Información sobre los Reportes
                                </h5>
                                <ul class="mb-0">
                                    <li class="mb-2">
                                        <strong>Formato:</strong> Todos los reportes se generan en formato PDF para fácil visualización e impresión.
                                    </li>
                                    <li class="mb-2">
                                        <strong>Datos en Tiempo Real:</strong> Los reportes se generan con información actualizada al momento de la consulta.
                                    </li>
                                    <li class="mb-2">
                                        <strong>Gráficos Incluidos:</strong> Cada reporte incluye visualizaciones gráficas para mejor comprensión de los datos.
                                    </li>
                                    <li class="mb-2">
                                        <strong>Descarga Automática:</strong> Los PDFs se abrirán en una nueva pestaña y podrás descargarlos directamente.
                                    </li>
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
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


$lugares = [
    'LT1' => 'Laboratorios Tecnicos 1 (LT1)',
    'LT2' => 'Laboratorios Tecnicos 2 (LT2)',
    'UD1' => 'Unidad Didáctica 1 (UD1)',
    'UD2' => 'Unidad Didáctica 2 (UD2)',
    'UD3' => 'Unidad Didáctica 3 (UD3)',
    'CUM1' => 'Centro Universitario de Medios 1 (CUM1)',
    'CUM2' => 'Centro Universitario de Medios 2 (CUM2)',
    'BIBLIOTECA' => 'Biblioteca'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Reunión - Trueques UPEMOR</title>
    
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

        <main class="dashboard-content p-4">
            <div class="container">
                <div class="row mb-3">
                    <div class="col-12">
                        <a href="index.php?action=reuniones" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card shadow-lg">
                            <div class="card-header bg-primary text-white">
                                <h3 class="mb-0">
                                    <i class="fas fa-calendar-plus me-2"></i>
                                    Agendar Nueva Reunión de Trueque
                                </h3>
                            </div>
                            <div class="card-body p-4">
                                
                                <?php if (isset($_GET['error'])): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <?php 
                                        $error = $_GET['error'];
                                        $mensajes = [
                                            'campos_vacios' => 'Por favor completa todos los campos obligatorios.',
                                            'mismo_correo' => 'No puedes crear una reunión contigo mismo.',
                                            'alumno_no_encontrado' => 'El correo del alumno no está registrado en el sistema.',
                                            'error_db' => 'Error al crear la reunión. Inténtalo de nuevo.'
                                        ];
                                        echo $mensajes[$error] ?? 'Ha ocurrido un error.';
                                        ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>

                                <form action="index.php?action=procesar_reunion" method="POST" id="formReunion">
                                    
                                    
                                    <div class="mb-4">
                                        <h5 class="text-primary mb-3">
                                            <i class="fas fa-user me-2"></i>Tu Información
                                        </h5>
                                        <div class="form-group mb-3">
                                            <label for="correo_creador" class="form-label">Tu Correo:</label>
                                            <input type="email" class="form-control" id="correo_creador" 
                                                   value="<?= htmlspecialchars($correo_usuario) ?>" readonly>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="objeto_creador" class="form-label">
                                                <i class="fas fa-box me-1"></i>Objeto que Ofreces: <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="objeto_creador" 
                                                   name="objeto_creador" required 
                                                   placeholder="Ej: Libro de Cálculo Diferencial">
                                        </div>
                                    </div>

                                    <hr>

                                    
                                    <div class="mb-4">
                                        <h5 class="text-primary mb-3">
                                            <i class="fas fa-users me-2"></i>Información del Otro Alumno
                                        </h5>
                                        <div class="form-group mb-3">
                                            <label for="correo_participante" class="form-label">
                                                <i class="fas fa-envelope me-1"></i>Correo del Alumno: <span class="text-danger">*</span>
                                            </label>
                                            <input type="email" class="form-control" id="correo_participante" 
                                                   name="correo_participante" required 
                                                   placeholder="ejemplo@upemor.edu.mx">
                                            <small class="form-text text-muted">
                                                Ingresa el correo institucional del alumno con quien harás el trueque.
                                            </small>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="objeto_participante" class="form-label">
                                                <i class="fas fa-box-open me-1"></i>Objeto que Recibirás: <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="objeto_participante" 
                                                   name="objeto_participante" required 
                                                   placeholder="Ej: Mouse Gamer">
                                        </div>
                                    </div>

                                    <hr>

                                  
                                    <div class="mb-4">
                                        <h5 class="text-primary mb-3">
                                            <i class="fas fa-map-marker-alt me-2"></i>Detalles de la Reunión
                                        </h5>
                                        
                                        <div class="form-group mb-3">
                                            <label for="lugar" class="form-label">
                                                <i class="fas fa-building me-1"></i>Lugar (dentro de UPEMOR): <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="lugar" name="lugar" required>
                                                <option value="">-- Selecciona un lugar --</option>
                                                <?php foreach ($lugares as $key => $nombre): ?>
                                                    <option value="<?= $key ?>"><?= $nombre ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="fecha" class="form-label">
                                                        <i class="fas fa-calendar me-1"></i>Fecha: <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="date" class="form-control" id="fecha" 
                                                           name="fecha" required min="<?= date('Y-m-d') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="hora" class="form-label">
                                                        <i class="fas fa-clock me-1"></i>Hora: <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="time" class="form-control" id="hora" 
                                                           name="hora" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="notas" class="form-label">
                                                <i class="fas fa-sticky-note me-1"></i>Notas Adicionales (Opcional):
                                            </label>
                                            <textarea class="form-control" id="notas" name="notas" 
                                                      rows="3" placeholder="Agrega cualquier información adicional..."></textarea>
                                        </div>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Nota:</strong> Una vez creada la reunión, tanto tú como el otro alumno 
                                        y el coordinador deberán confirmar su asistencia.
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-check-circle me-2"></i>Crear Reunión
                                        </button>
                                        <a href="index.php?action=reuniones" class="btn btn-outline-secondary">
                                            Cancelar
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
</body>
</html>
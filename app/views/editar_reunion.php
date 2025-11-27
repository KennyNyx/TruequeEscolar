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
    'LT1' => 'Laboratorio de Tecnología 1',
    'LT2' => 'Laboratorio de Tecnología 2',
    'UD1' => 'Unidad Didáctica 1',
    'UD2' => 'Unidad Didáctica 2',
    'UD3' => 'Unidad Didáctica 3',
    'CUM1' => 'Centro Universitario de Medios 1',
    'CUM2' => 'Centro Universitario de Medios 2',
    'BIBLIOTECA' => 'Biblioteca'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Reunión - Trueques UPEMOR</title>
    
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
            <a href="#"><i class="fas fa-file-alt me-2"></i> Reseñas</a>
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
                        
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-edit me-2"></i>Editar Reunión
                                </h4>
                            </div>
                            
                            <div class="card-body p-4">
                                
                                <a href="index.php?action=gestionar_reuniones" class="btn btn-secondary mb-3">
                                    <i class="fas fa-arrow-left me-2"></i> Volver a Mis Reuniones
                                </a>

                                <?php if (isset($_GET['error'])): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <?php 
                                        $errores = [
                                            'campos_vacios' => 'Por favor completa todos los campos obligatorios.',
                                            'mismo_correo' => 'No puedes crear una reunión contigo mismo.',
                                            'alumno_no_encontrado' => 'No se encontró ningún alumno con ese correo.',
                                            'error_db' => 'Error al actualizar la reunión. Intenta nuevamente.',
                                            'reunion_no_encontrada' => 'La reunión no existe.',
                                            'sin_permiso' => 'No tienes permiso para editar esta reunión.',
                                            'no_editable' => 'Esta reunión ya no puede ser editada.'
                                        ];
                                        echo $errores[$_GET['error']] ?? 'Error desconocido.';
                                        ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>

                                <form action="index.php?action=actualizar_reunion" method="POST">
                                    <input type="hidden" name="id_reunion" value="<?= htmlspecialchars($reunion['id_reunion']) ?>">

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Nota:</strong> Solo puedes editar reuniones que estén en estado "Pendiente".
                                    </div>

                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
                                            <i class="fas fa-user me-2"></i>Tú ofreces:
                                        </label>
                                        <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($correo_usuario) ?>" disabled>
                                        <small class="text-muted">Este es tu correo (no se puede cambiar)</small>
                                    </div>

                                   
                                    <div class="mb-3">
                                        <label for="objeto_creador" class="form-label fw-bold">
                                            <i class="fas fa-box me-2"></i>Objeto que tú ofreces <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="objeto_creador" 
                                               name="objeto_creador" 
                                               value="<?= htmlspecialchars($reunion['objeto_creador']) ?>"
                                               placeholder="Ej: Libro de Cálculo, Laptop HP, etc." 
                                               required>
                                    </div>

                                    <hr class="my-4">

                                   
                                    <div class="mb-3">
                                        <label for="correo_participante" class="form-label fw-bold">
                                            <i class="fas fa-user-plus me-2"></i>Correo del otro participante <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="correo_participante" 
                                               name="correo_participante"
                                               value="<?= htmlspecialchars($reunion['correo_participante']) ?>" 
                                               placeholder="ejemplo@upemor.edu.mx" 
                                               required>
                                        <small class="text-muted">Ingresa el correo institucional del alumno</small>
                                    </div>

                                    
                                    <div class="mb-3">
                                        <label for="objeto_participante" class="form-label fw-bold">
                                            <i class="fas fa-box-open me-2"></i>Objeto que ofrece el participante <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="objeto_participante" 
                                               name="objeto_participante"
                                               value="<?= htmlspecialchars($reunion['objeto_participante']) ?>" 
                                               placeholder="Ej: Calculadora científica, Mouse gaming, etc." 
                                               required>
                                    </div>

                                    <hr class="my-4">

                                    
                                    <div class="mb-3">
                                        <label for="lugar" class="form-label fw-bold">
                                            <i class="fas fa-map-marker-alt me-2"></i>Lugar de encuentro <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="lugar" name="lugar" required>
                                            <option value="">Selecciona un lugar...</option>
                                            <?php foreach($lugares as $key => $nombre): ?>
                                                <option value="<?= $key ?>" <?= $reunion['lugar'] === $key ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($nombre) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    
                                    <div class="mb-3">
                                        <label for="fecha" class="form-label fw-bold">
                                            <i class="fas fa-calendar-alt me-2"></i>Fecha de la reunión <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="fecha" 
                                               name="fecha"
                                               value="<?= htmlspecialchars($reunion['fecha_reunion']) ?>" 
                                               min="<?= date('Y-m-d') ?>" 
                                               required>
                                    </div>

                                   
                                    <div class="mb-3">
                                        <label for="hora" class="form-label fw-bold">
                                            <i class="fas fa-clock me-2"></i>Hora de la reunión <span class="text-danger">*</span>
                                        </label>
                                        <input type="time" 
                                               class="form-control" 
                                               id="hora" 
                                               name="hora"
                                               value="<?= htmlspecialchars($reunion['hora_reunion']) ?>" 
                                               required>
                                    </div>

                                  
                                    <div class="mb-3">
                                        <label for="notas" class="form-label fw-bold">
                                            <i class="fas fa-sticky-note me-2"></i>Notas adicionales (opcional)
                                        </label>
                                        <textarea class="form-control" 
                                                  id="notas" 
                                                  name="notas" 
                                                  rows="3" 
                                                  placeholder="Cualquier información adicional sobre el trueque..."><?= htmlspecialchars($reunion['notas'] ?? '') ?></textarea>
                                    </div>

                                    <hr class="my-4">

                                 
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save me-2"></i>Guardar Cambios
                                        </button>
                                        <a href="index.php?action=gestionar_reuniones" class="btn btn-outline-secondary">
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
</body>
</html>
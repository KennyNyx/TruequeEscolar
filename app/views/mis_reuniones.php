<?php
date_default_timezone_set('America/Mexico_City');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?action=login"); 
    exit();
}

$nombre_usuario = $_SESSION['user_nombre'];
$rol = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];


$lista_reuniones = $reuniones ?? $mis_reuniones ?? [];

$lugares_nombres = [
    'LT1' => 'Laboratorio de Tecnología 1',
    'LT2' => 'Laboratorio de Tecnología 2',
    'UD1' => 'Unidad Didáctica 1',
    'UD2' => 'Unidad Didáctica 2',
    'UD3' => 'Unidad Didáctica 3',
    'CUM1' => 'Centro Universitario de Medios 1',
    'CUM2' => 'Centro Universitario de Medios 2',
    'BIBLIOTECA' => 'Biblioteca'
];

function getBadgeEstado($estado) {
    $badges = [
        'pendiente' => '<span class="badge bg-warning text-dark">Pendiente</span>',
        'confirmado' => '<span class="badge bg-success">Confirmado</span>',
        'cancelado' => '<span class="badge bg-danger">Cancelado</span>',
        'confirmada' => '<span class="badge bg-success">Confirmada</span>',
        'cancelada' => '<span class="badge bg-danger">Cancelada</span>',
        'completada' => '<span class="badge bg-info">Completada</span>'
    ];
    return $badges[$estado] ?? '<span class="badge bg-secondary">Desconocido</span>';
}

function getBadgeEstadoMini($estado) {
    $class = match($estado) {
        'confirmado', 'confirmada' => 'bg-success',
        'cancelado', 'cancelada' => 'bg-danger',
        default => 'bg-warning text-dark'
    };
    $texto = ucfirst($estado);
    return "<span class='badge $class' style='font-size: 0.7em;'>$texto</span>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reuniones - Trueques UPEMOR</title>
    
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
            <?php if ($rol === 'alumno'): ?>
                <a href="index.php?action=vistaAlumnos"><i class="fas fa-home me-2"></i> Inicio</a>
                <a href="index.php?action=catalogo"><i class="fas fa-book-open me-2"></i> Catálogo</a>
                <a href="index.php?action=agregarProducto"><i class="fas fa-plus-circle me-2"></i> Agregar Producto</a>
                <a href="index.php?action=reuniones" class="active"><i class="fas fa-users me-2"></i> Reuniones</a>
                <a href="index.php?action=chat"><i class="fas fa-comments me-2"></i> Chat</a>
                <a href="index.php?action=resenas"><i class="fas fa-star me-2"></i> Reseñas</a>
            <?php elseif ($rol === 'coordinador'): ?>
                <a href="index.php?action=vistaCoordinador"><i class="fas fa-home me-2"></i> Inicio</a>
                <a href="index.php?action=gestionar_reuniones" class="active"><i class="fas fa-calendar-check me-2"></i> Reuniones</a>
                <a href="index.php?action=ver_resenas"><i class="fas fa-star me-2"></i> Gestionar Reseñas</a>
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
                        <?php if ($rol === 'coordinador'): ?>
                        <?php else: ?>
                            <a href="index.php?action=reuniones" class="btn btn-secondary mb-3">
                                <i class="fas fa-arrow-left me-2"></i> Volver
                            </a>
                        <?php endif; ?>
                        <h2>
                            <i class="fas fa-calendar-check me-2"></i>
                            <?= $rol === 'coordinador' ? 'Todas las Reuniones' : 'Mis Reuniones' ?>
                        </h2>
                    </div>
                </div>

                <?php if (isset($_GET['mensaje'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        $mensajes = [
                            'reunion_creada' => 'Reunión creada exitosamente.',
                            'reunion_actualizada' => 'Reunión actualizada exitosamente.',
                            'confirmado' => 'Has confirmado la reunión.',
                            'cancelado' => 'Has cancelado la reunión.',
                            'reunion_eliminada' => 'Reunión eliminada del historial.'
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
                            'reunion_no_encontrada' => 'La reunión no existe.',
                            'sin_permiso' => 'No tienes permiso para realizar esta acción.',
                            'no_editable' => 'Esta reunión ya no puede ser editada.',
                            'error_confirmar' => 'Error al confirmar la reunión.',
                            'error_cancelar' => 'Error al cancelar la reunión.',
                            'error_eliminar' => 'Error al eliminar la reunión.'
                        ];
                        echo $errores[$_GET['error']] ?? 'Error desconocido.';
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <?php if (empty($lista_reuniones)): ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle fa-3x mb-3"></i>
                                <h5>No tienes reuniones agendadas</h5>
                                <?php if ($rol === 'alumno'): ?>
                                    <a href="index.php?action=crear_reunion" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus me-2"></i>Crear Nueva Reunión
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($lista_reuniones as $reunion): ?>
                            <?php 
                                
                                $fechaHoraReunionStr = $reunion['fecha_reunion'] . ' ' . $reunion['hora_reunion'];
                                $timestampReunion = strtotime($fechaHoraReunionStr);
                                $timestampAhora = time();
                                $yaPasoLaFecha = ($timestampAhora > $timestampReunion);

                               
                                $mi_estado = 'pendiente';

                                if ($rol === 'coordinador') {
                                    $mi_estado = $reunion['estado_coordinador'] ?? 'pendiente';
                                } elseif ($user_id == $reunion['id_alumno_creador']) {
                                    $mi_estado = $reunion['estado_creador'];
                                } else {
                                    $mi_estado = $reunion['estado_participante'];
                                }

                                $puedeEliminar = false;

                                if ($reunion['estado_general'] === 'cancelada') {
                                    $puedeEliminar = true;
                                }
                                elseif ($reunion['estado_general'] === 'confirmada' && $yaPasoLaFecha) {
                                    $puedeEliminar = true;
                                }

                                $nombre1 = $reunion['nombre_creador'] ?? 'Alumno 1';
                                $prod1   = $reunion['objeto_creador'] ?? 'Sin especificar';
                                $estado1 = $reunion['estado_creador'] ?? 'pendiente';

                                $nombre2 = $reunion['nombre_participante'] ?? 'Alumno 2';
                                $prod2   = $reunion['objeto_participante'] ?? 'Sin especificar';
                                $estado2 = $reunion['estado_participante'] ?? 'pendiente';

                                $notas = $reunion['notas'] ?? '';
                            ?>

                            <div class="col-md-6 mb-4">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                        <h6 class="mb-0 text-primary fw-bold">
                                            <i class="fas fa-handshake me-2"></i>Reunión #<?= $reunion['id_reunion'] ?>
                                        </h6>
                                        <?= getBadgeEstado($reunion['estado_general']) ?>
                                    </div>
                                    
                                    <div class="card-body">
                                        <div class="row g-3 mb-3">
                                            <div class="col-6">
                                                <small class="text-muted d-block mb-1">Lugar</small>
                                                <span class="fw-medium">
                                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                                    <?= $lugares_nombres[$reunion['lugar']] ?? $reunion['lugar'] ?>
                                                </span>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block mb-1">Fecha y Hora</small>
                                                <span class="fw-medium <?= $yaPasoLaFecha ? 'text-muted' : '' ?>"> 
                                                    <i class="far fa-clock text-primary me-1"></i>
                                                    <?= date('d/m/Y', strtotime($reunion['fecha_reunion'])) ?> - 
                                                    <?= date('H:i', strtotime($reunion['hora_reunion'])) ?>
                                                </span>
                                                <?php if($yaPasoLaFecha): ?>
                                                    <br><small class="badge bg-secondary mt-1">Finalizada</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <hr class="text-muted opacity-25">

                                        <div class="mb-3">
                                            <small class="text-primary fw-bold"><i class="fas fa-users"></i> Participantes:</small>
                                            <div class="mt-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <span class="fw-bold text-dark d-block"><?= htmlspecialchars($nombre1) ?></span>
                                                        <small class="text-muted fst-italic">Ofrece: <?= htmlspecialchars($prod1) ?></small>
                                                    </div>
                                                    <?= getBadgeEstadoMini($estado1) ?>
                                                </div>
                                            </div>
                                            <div class="mt-2 pt-2 border-top border-light">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <span class="fw-bold text-dark d-block"><?= htmlspecialchars($nombre2) ?></span>
                                                        <small class="text-muted fst-italic">Ofrece: <?= htmlspecialchars($prod2) ?></small>
                                                    </div>
                                                    <?= getBadgeEstadoMini($estado2) ?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if(!empty($notas)): ?>
                                            <div class="mb-3 p-2 bg-light rounded border border-light">
                                                <small class="text-primary fw-bold d-block"><i class="fas fa-sticky-note"></i> Notas:</small>
                                                <small class="text-dark"><?= htmlspecialchars($notas) ?></small>
                                            </div>
                                        <?php endif; ?>

                                        <div class="mt-3 pt-2 border-top">
                                            
                                            <?php if ($mi_estado === 'pendiente'): ?>

                                                <?php if ($rol === 'alumno' && $user_id == $reunion['id_alumno_creador'] && $reunion['estado_general'] === 'pendiente'): ?>
                                                    <div class="mb-2">
                                                        <a href="index.php?action=editar_reunion&id=<?= $reunion['id_reunion'] ?>" 
                                                           class="btn btn-outline-primary btn-sm w-100">
                                                            <i class="fas fa-edit me-1"></i> Editar Reunión
                                                        </a>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="d-flex gap-2">
                                                    <a href="index.php?action=confirmar_reunion&id=<?= $reunion['id_reunion'] ?>" 
                                                       class="btn btn-success btn-sm flex-grow-1">
                                                        <i class="fas fa-check me-1"></i> Confirmar
                                                    </a>
                                                    <a href="index.php?action=cancelar_reunion&id=<?= $reunion['id_reunion'] ?>" 
                                                       class="btn btn-outline-danger btn-sm flex-grow-1"
                                                       onclick="return confirm('¿Estás seguro de cancelar esta reunión?');">
                                                        <i class="fas fa-times me-1"></i> Cancelar
                                                    </a>
                                                </div>

                                            <?php elseif ($mi_estado === 'confirmado'): ?>
                                                <div class="alert alert-success mb-0 text-center py-2">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Has confirmado la reunión
                                                </div>

                                                <?php if ($puedeEliminar): ?>
                                                    <div class="mt-2 text-center">
                                                        <a href="index.php?action=eliminar_reunion&id=<?= $reunion['id_reunion'] ?>" 
                                                           class="btn btn-outline-danger btn-sm w-100"
                                                           onclick="return confirm('La reunión ya pasó. ¿Deseas eliminarla de tu historial?');">
                                                            <i class="fas fa-trash-alt me-1"></i> Eliminar del historial
                                                        </a>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center mt-2">
                                                        <small class="text-muted fst-italic">
                                                            <i class="fas fa-hourglass-half me-1"></i> Esperando confirmación final...
                                                        </small>
                                                    </div>
                                                <?php endif; ?>

                                            <?php elseif ($mi_estado === 'cancelado' || $reunion['estado_general'] === 'cancelada'): ?>
                                                <div class="alert alert-danger mb-0 text-center py-2">
                                                    <i class="fas fa-times-circle me-1"></i>
                                                    Reunión cancelada
                                                </div>
                                                <?php if ($puedeEliminar): ?>
                                                    <div class="mt-2 text-center">
                                                        <a href="index.php?action=eliminar_reunion&id=<?= $reunion['id_reunion'] ?>" 
                                                           class="btn btn-outline-danger btn-sm w-100"
                                                           onclick="return confirm('¿Deseas eliminar esta reunión cancelada de tu historial?');">
                                                            <i class="fas fa-trash-alt me-1"></i> Eliminar del historial
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
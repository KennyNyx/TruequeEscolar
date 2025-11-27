<?php

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header("Location: index.php?action=login&error=4"); 
    exit();
}
$id_usuario_actual = $_SESSION['user_id'];
$nombre_usuario = $_SESSION['user_nombre'];


if (!isset($_GET['id_comentario']) || empty($_GET['id_comentario'])) {
    header("Location: index.php?action=vistaAlumnos&error=id_no_encontrado");
    exit();
}
$id_comentario = $_GET['id_comentario'];


if (!isset($productoModel)) {
    $productoModel = new ProductoModel($db);
}


if (!$productoModel->verificarPropietarioComentario($id_comentario, $id_usuario_actual)) {
    header("Location: index.php?action=vistaAlumnos&error=no_autorizado");
    exit();
}


$comentario = $productoModel->obtenerComentarioPorId($id_comentario);

if (!$comentario) {
    header("Location: index.php?action=vistaAlumnos&error=comentario_no_existe");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Comentario</title>
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
            <a href="index.php?action=agregarProducto"><i class="fas fa-plus-circle me-2"></i> Agregar Producto</a>
            <a href="#"><i class="fas fa-users me-2"></i> Reuniones</a>
            <a href="index.php?action=chat"><i class="fas fa-comments me-2"></i> Chat</a>
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
            <section class="form-container" style="max-width: 600px;">
                <h2>Editar mi comentario</h2>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <strong>¡Error!</strong> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?action=actualizar_comentario" method="POST">
                    <input type="hidden" name="id_comentario" value="<?php echo htmlspecialchars($comentario['id_comentario']); ?>">
                    <input type="hidden" name="id_objeto" value="<?php echo htmlspecialchars($comentario['id_objeto']); ?>">

                    <div class="form-group">
                        <label for="comentario_texto">Comentario:</label>
                        <textarea id="comentario_texto" name="comentario_texto" rows="4" class="form-control" required><?php echo htmlspecialchars($comentario['comentario']); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-register w-100 mt-3">
                        <i class="fas fa-save me-2"></i>
                        Actualizar Comentario
                    </button>
                    <a href="index.php?action=vistaAlumnos#post-<?php echo htmlspecialchars($comentario['id_objeto']); ?>" class="btn btn-secondary w-100 mt-2">
                        Cancelar
                    </a>
                </form>
            </section>
        </main>

        <footer class="dashboard-footer">
            <p>© 2025 Trueque Escolar UPEMOR | Desarrollado como proyecto de Estancia II</p>
        </footer>
    </div>
</body>
</html>
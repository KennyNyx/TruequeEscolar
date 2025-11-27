<?php
include_once 'config/dbConnection.php'; 
include_once 'app/models/ProductoModel.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header("Location: login.php?error=4"); 
    exit();
}
$nombre_usuario = $_SESSION['user_nombre'];


$categorias = [];
try {
    $db = conectar();
    $productoModel = new ProductoModel($db);
    $categorias = $productoModel->obtenerCategoriasUnicas(); 
} catch (Exception $e) {
    error_log("Error al cargar categorías: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos</title>
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
            <a href="index.php?action=vistaAlumnos"><i class="fas fa-home me-2"></i> Inicio</a>
            <a href="index.php?action=catalogo" class="active"><i class="fas fa-book-open me-2"></i> Catálogo</a>
            <a href="index.php?action=agregarProducto"><i class="fas fa-plus-circle me-2"></i> Agregar Producto</a>
            <a href="index.php?action=reuniones" ><i class="fas fa-users me-2"></i> Reuniones</a>
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
            <h2>Catálogo de Productos</h2>
            <p>Explora los artículos disponibles para trueque, organizados por categoría.</p>

            <?php if (empty($categorias)): ?>
                <div class="alert alert-info">
                    Aún no hay categorías con productos. ¡Sé el primero en <a href="formularioObjetos.php">publicar un objeto</a>!
                </div>
            <?php else: ?>
                <div class="category-grid">
                    <?php foreach ($categorias as $categoria): ?>
                        <a href="index.php?action=ver_categoria&categoria=<?php echo urlencode($categoria['categoria']); ?>" class="category-block">
                            <div class="category-icon">
                                <?php 
                                    
                                    $icono = 'fas fa-box'; 
                                    if ($categoria['categoria'] == 'Libros') $icono = 'fas fa-book';
                                    if ($categoria['categoria'] == 'Calculadoras') $icono = 'fas fa-calculator';
                                    if ($categoria['categoria'] == 'Ropa') $icono = 'fas fa-tshirt';
                                    if ($categoria['categoria'] == 'Electronica') $icono = 'fas fa-laptop';
                                    if ($categoria['categoria'] == 'Utiles') $icono = 'fas fa-pencil-ruler';
                                ?>
                                <i class="<?php echo $icono; ?>"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($categoria['categoria']); ?></h3>
                            <span class="item-count"><?php echo $categoria['total']; ?> artículos</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>

        <footer class="dashboard-footer">
            <p>© 2025 Trueque Escolar UPEMOR | Desarrollado como proyecto de Estancia II</p>
        </footer>
    </div>
</body>
</html>
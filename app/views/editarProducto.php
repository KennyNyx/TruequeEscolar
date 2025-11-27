<?php

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header("Location: index.php?action=login&error=4"); 
    exit();
}
$id_usuario_actual = $_SESSION['user_id'];
$nombre_usuario = $_SESSION['user_nombre'];


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php?action=vistaAlumnos&error=id_no_encontrado");
    exit();
}
$id_producto = $_GET['id'];


if (!isset($productoModel)) {
    $productoModel = new ProductoModel($db);
}


$producto = $productoModel->obtenerPorId($id_producto);

if (!$producto) {
    header("Location: index.php?action=vistaAlumnos&error=producto_no_existe");
    exit();
}

if ($producto['ID'] != $id_usuario_actual) {
    header("Location: index.php?action=vistaAlumnos&error=no_autorizado");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
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
            <a href="index.php?action=catalogo"><i class="fas fa-book-open me-2"></i> Catálogo</a>
            <a href="index.php?action=agregarProducto" class="active"><i class="fas fa-plus-circle me-2"></i> Agregar Producto</a>
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
            <section class="form-container">
                <h2>Editar mi publicación</h2>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <strong>¡Error!</strong> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?action=actualizar_producto" method="POST" enctype="multipart/form-data">
                    
                    <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($producto['id_objetos']); ?>">

                    <div class="form-group">
                        <label for="nombre">Nombre del Artículo:</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="marca">Marca (Opcional):</label>
                        <input type="text" id="marca" name="marca" value="<?php echo htmlspecialchars($producto['marca']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado del Artículo:</label>
                        <select id="estado" name="estado" required>
                            <option value="nuevo" <?php if($producto['estado'] == 'nuevo') echo 'selected'; ?>>Nuevo (Empaquetado)</option>
                            <option value="seminuevo" <?php if($producto['estado'] == 'seminuevo') echo 'selected'; ?>>Seminuevo (Como nuevo)</option>
                            <option value="usado_bueno" <?php if($producto['estado'] == 'usado_bueno') echo 'selected'; ?>>Usado (En buen estado)</option>
                            <option value="usado_detalles" <?php if($producto['estado'] == 'usado_detalles') echo 'selected'; ?>>Usado (Con detalles estéticos)</option>
                            <option value="reparacion" <?php if($producto['estado'] == 'reparacion') echo 'selected'; ?>>Ocupa reparaciones</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="categoria">Categoría:</label>
                        <select id="categoria" name="categoria" required>
                            <option value="Libros" <?php if($producto['categoria'] == 'Libros') echo 'selected'; ?>>Libros</option>
                            <option value="Calculadoras" <?php if($producto['categoria'] == 'Calculadoras') echo 'selected'; ?>>Calculadoras</option>
                            <option value="Ropa" <?php if($producto['categoria'] == 'Ropa') echo 'selected'; ?>>Ropa (Batas, uniformes, etc.)</option>
                            <option value="Electronica" <?php if($producto['categoria'] == 'Electronica') echo 'selected'; ?>>Electrónica (Laptop, USB, etc.)</option>
                            <option value="Utiles" <?php if($producto['categoria'] == 'Utiles') echo 'selected'; ?>>Útiles Escolares (Plumas, libretas, etc.)</option>
                            <option value="Otro" <?php if($producto['categoria'] == 'Otro') echo 'selected'; ?>>Otro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea id="descripcion" name="descripcion" rows="6" style="width: 100%; resize: both;"required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="imagen">Cambiar foto (Opcional):</label>
                        <p style="font-size: 0.8rem; opacity: 0.7; margin: 0;">Imagen actual:</p>
                        <img src="uploads/productos/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Imagen actual" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                        
                        <input type="file" id="imagen" name="imagen" accept="image/png, image/jpeg">
                        <small>Si no seleccionas un archivo nuevo, se conservará la imagen actual.</small>
                    </div>

                    <button type="submit" class="btn btn-register w-100">
                        <i class="fas fa-save me-2"></i>
                        Actualizar Publicación
                    </button>
                    
                    <a href="index.php?action=vistaAlumnos" class="btn btn-secondary w-100 mt-2">
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
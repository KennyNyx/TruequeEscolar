<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'alumno') {
    header("Location: login.php?error=4"); 
    exit();
}
$nombre_usuario = $_SESSION['user_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Producto</title>
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
            <a href="index.php?action=reuniones"><i class="fas fa-users me-2"></i> Reuniones</a>
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
            <section class="form-container">
                <h2>Publicar un nuevo objeto para trueque</h2>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <strong>¡Error!</strong> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?action=subir_producto" method="POST" enctype="multipart/form-data">
                    
                    <input type="hidden" name="publicar" value="1">

                    <div class="form-group">
                        <label for="nombre">Nombre del Artículo:</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej: Libro de Cálculo, Bata de Lab." required>
                    </div>

                    <div class="form-group">
                        <label for="marca">Marca (Opcional):</label>
                        <input type="text" id="marca" name="marca" placeholder="Ej: Pearson, Adidas, LENOVO">
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado del Artículo:</label>
                        <select id="estado" name="estado" required>
                            <option value="" disabled selected>-- Selecciona una opción --</option>
                            <option value="nuevo">Nuevo (Empaquetado)</option>
                            <option value="seminuevo">Seminuevo (Como nuevo)</option>
                            <option value="usado_bueno">Usado (En buen estado)</option>
                            <option value="usado_detalles">Usado (Con detalles estéticos)</option>
                            <option value="reparacion">Ocupa reparaciones</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="categoria">Categoría:</label>
                        <select id="categoria" name="categoria" required>
                            <option value="" disabled selected>-- Selecciona una categoría --</option>
                            <option value="Libros">Libros</option>
                            <option value="Calculadoras">Calculadoras</option>
                            <option value="Ropa">Ropa (Batas, uniformes, etc.)</option>
                            <option value="Electronica">Electrónica (Laptop, USB, etc.)</option>
                            <option value="Utiles">Útiles Escolares (Plumas, libretas, etc.)</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea id="descripcion" name="descripcion" rows="6" placeholder="Describe tu artículo, qué buscas a cambio, etc." style="width: 100%; resize: both;"
                        required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="imagen">Subir una foto:</label>
                        <input type="file" id="imagen" name="imagen" accept="image/png, image/jpeg" required>
                    </div>

                    <button type="submit" class="btn btn-register w-100">
                        <i class="fas fa-upload me-2"></i>
                        Publicar Objeto
                    </button>
                </form>
            </section>
        </main>

        <footer class="dashboard-footer">
            <p>© 2025 Trueque Escolar UPEMOR | Desarrollado como proyecto de Estancia II</p>
        </footer>
    </div>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header("Location: index.php?action=login"); 
    exit();
}
$nombre_usuario = $_SESSION['user_nombre'] ?? 'Admin';
$modo_edicion = (isset($_GET['edit']) && $_GET['edit'] === 'true') || (isset($activar_edicion) && $activar_edicion === true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Administradores</title>
    <link rel="stylesheet" href="bootstrap/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>Trueques Escolares UPEMOR</h3>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php?action=vistaAdmin"><i class="fas fa-home me-2"></i> Inicio</a>
            <a href="index.php?action=mostrarRegistro"><i class="fas fa-user-plus me-2"></i> Crear cuenta</a>
            <a href="index.php?action=user_consultar"><i class="fas fa-users me-2"></i> Consultar Alumnos</a>
            <a href="index.php?action=admin_consultar" class="active"><i class="fas fa-user-shield me-2"></i> Consultar Administradores</a>
            <a href="index.php?action=coord_consultar"><i class="fas fa-user-tie me-2"></i> Consultar Coordinadores</a>
            <a href="index.php?action=generar_reportes"><i class="fas fa-file-alt me-2"></i> Generar Reportes</a>
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
                <div class="row">
                    <div class="col-12">
                        <h2 class="mb-4">
                            <i class="fas fa-user-shield"></i> 
                            <?php echo $modo_edicion ? 'Editar Administrador' : 'Consulta de Administradores'; ?>
                        </h2>
                    </div>
                </div>

                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($_GET['type'] ?? 'info') === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($modo_edicion): ?>
                    <div class="d-flex justify-content-center my-4">
                        <div class="card register-card shadow-lg p-4">
                            <h3 class="login-title text-center mb-2">Editar Administrador</h3>
                            <p class="text-center text-muted mb-4">Modifica los datos del administrador</p>
                            <form action="index.php?action=admin_actualizar" method="POST">
                                

                                <input type="hidden" name="id" value="<?= htmlspecialchars($adminEditar['id_admin']) ?>">


                                <div class="form-group">
                                    <label for="nombre">Nombre Completo:</label>
                                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($adminEditar['nombre']) ?>" required placeholder="Nombre Completo">
                                </div>

                                <div class="form-group">
                                    <label for="correo">Correo Electrónico:</label>
                                    <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($adminEditar['correo']) ?>" required placeholder="correo@upemor.edu.mx">
                                </div>

                                <div class="form-group">
                                    <label for="contrasena">Contraseña:</label>
                                    <input type="password" id="contrasena" name="contrasena"  placeholder="Dejar en blanco para mantener la actual">
                                    <small class="form-text text-muted">Solo llena este campo si deseas cambiar la contraseña.</small>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" name="enviar" value="actualizar" class="btn-register mb-3">
                                        <i class="fas fa-save me-2"></i> Guardar Cambios
                                    </button>
                                    
                                    <a href="index.php?action=admin_consultar" class="btn-back w-100 text-center d-block">
                                        Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($admins)): ?>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($admin['id_admin']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['correo']); ?></td>
                                    <td>
                                        <a class="btn-accion-post btn-editar-post" 
                                           href="index.php?action=admin_editar&id=<?php echo htmlspecialchars($admin['id_admin']); ?>">
                                           <i class="fas fa-edit"></i></a>

                                        <a class="btn-accion-post btn-eliminar-post"
                                           onclick="return confirm('¿Estás seguro de eliminar al administrador?')"
                                           href="index.php?action=admin_eliminar&id=<?php echo htmlspecialchars($admin['id_admin']); ?>">
                                           <i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">No hay administradores registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <footer class="dashboard-footer">
            <p>© 2025 Trueque Escolar UPEMOR | Proyecto de Estancia II</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
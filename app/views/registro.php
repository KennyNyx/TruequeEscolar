<?php 
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrador') {
    header("Location: login.php?error=4"); 
    exit();
}
$nombre_usuario = $_SESSION['user_nombre'];


$mensaje = $_GET['message'] ?? null;
$tipo = $_GET['type'] ?? 'error'; 
$nombre = $_GET['nombre'] ?? '';
$rol_previo = $_GET['rol'] ?? 'alumnos'; 
$carrera = $_GET['carrera'] ?? '';
$cuatrimestre = $_GET['cuatrimestre'] ?? '';
$turno = $_GET['turno'] ?? '';
$correo = $_GET['correo'] ?? '';

?>
<!DOCTYPE html>
<html lang="es"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Estudiantes</title>
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
                <a href="index.php?action=vistaAdmin" ><i class="fas fa-home me-2"></i> Inicio</a>
                <a href="index.php?action=mostrarRegistro" class="active"><i class="fas fa-user-plus me-2"></i> Crear cuenta</a>
                <a href="index.php?action=user_consultar"><i class="fas fa-users me-2"></i> Consultar Alumnos</a>
                <a href="index.php?action=admin_consultar"><i class="fas fa-user-shield me-2"></i> Consultar Administradores</a>
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
            <div class="d-flex justify-content-center my-4">
                <div class="card register-card shadow-lg p-4">
                        <h2 class="login-title text-center mb-2">Crear cuenta</h2>
                        <p class="text-center text-muted mb-4">Ingresa tus datos para registrarte</p>
                    <?php if (isset($mensaje)): ?>
                        <div class="alert-message <?php echo ($tipo == 'success') ? 'alert-success' : 'alert-error'; ?>">
                            <?php echo htmlspecialchars($mensaje); ?>
                        </div>
                    <?php endif; ?>
                    <form action="index.php?action=registrar" method="POST">
                        
                        <div class="form-group mb-3">
                            <label for="nombre">Nombre:</label>
                            <input type="text" id="nombre" name="nombre" placeholder="Nombre Completo" required 
                                value="<?php echo htmlspecialchars($nombre); ?>">
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="rol">Tipo de Usuario:</label>
                            <select id="rol" name="rol" required>
                                <option value="alumnos" <?php echo ($rol_previo == 'alumnos') ? 'selected' : ''; ?>>Alumno</option>
                                <option value="administrador" <?php echo ($rol_previo == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                                <option value="coordinador" <?php echo ($rol_previo == 'coordinador') ? 'selected' : ''; ?>>Coordinador</option>
                            </select>
                        </div>

                        <div id="campos-alumno">
                            <div class="form-group">
                                <label for="carrera">Carrera:</label>
                                <select id="carrera" name="carrera" required> 
                                    <option value="IIN" <?php echo ($carrera == 'IIN') ? 'selected' : ''; ?>>Ingeniería Industrial (IIN)</option>
                                    <option value="IFI" <?php echo ($carrera == 'IFI') ? 'selected' : ''; ?>>Ingeniería Financiera (IFI)</option>
                                    <option value="LAE" <?php echo ($carrera == 'LAE') ? 'selected' : ''; ?>>Licenciatura en Administración y Gestión Empresarial (LAE)</option>
                                    <option value="ITI" <?php echo ($carrera == 'ITI') ? 'selected' : ''; ?>>Ingeniería en Tecnologías de la información (ITI)</option>
                                    <option value="IET" <?php echo ($carrera == 'IET') ? 'selected' : ''; ?>>Ingeniería en Electrónica y Telecomunicaciones (IET)</option>
                                    <option value="IBT" <?php echo ($carrera == 'IBT') ? 'selected' : ''; ?>>Ingeniería en Biotecnologia (IBT)</option>
                                    <option value="ITA" <?php echo ($carrera == 'ITA') ? 'selected' : ''; ?>>Ingeniería en Tecnología Ambiental (ITA)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="cuatrimestre">Cuatrimestre:</label>
                                <input type="number" id="cuatrimestre" name="cuatrimestre" placeholder="Ej: 5" 
                                    required min="1" max="13" step="1"
                                    value="<?php echo htmlspecialchars($cuatrimestre); ?>">
                            </div>

                            <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const cuatrimestreInput = document.getElementById('cuatrimestre');
                                
                                cuatrimestreInput.addEventListener('keypress', function(e) {
                                    if (!/[0-9]/.test(String.fromCharCode(e.which))) {
                                        e.preventDefault();
                                    }
                                });
                                
                                cuatrimestreInput.addEventListener('paste', function(e) {
                                    e.preventDefault();
                                    const pasted = (e.clipboardData || window.clipboardData).getData('text');
                                    if (/^\d+$/.test(pasted)) {
                                        this.value = pasted;
                                    }
                                });
                                
                                cuatrimestreInput.addEventListener('change', function() {
                                    this.value = Math.floor(this.value) || '';
                                });
                            });
                            </script>

                            <div class="form-group">
                                <label for="turno">Turno:</label>
                                <select id="turno" name="turno" required> 
                                    <option value="matutino" <?php echo ($turno == 'matutino') ? 'selected' : ''; ?>>Matutino</option>
                                    <option value="vespertino" <?php echo ($turno == 'vespertino') ? 'selected' : ''; ?>>Vespertino</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="correo">Correo electrónico:</label>
                            <input type="email" id="correo" name="correo" placeholder="tucorreo@upemor.edu.mx" required
                                value="<?php echo htmlspecialchars($correo); ?>">
                        </div>

                        <div class="form-group">
                            <label for="contrasena">Contraseña:</label>
                            <input type="password" id="contrasena" name="contrasena" placeholder="Crea una contraseña segura" required>
                        </div>

                        <button type="submit" name="enviar" class="btn btn-register">
                            <i class="fas fa-user-plus me-2"></i>
                            Registrarse
                        </button>
                    </form>

                    <hr>
                </div>
            </div>
        </main>

        <footer class="dashboard-footer">
            <p>© 2025 Trueque Escolar UPEMOR | Desarrollado como proyecto de Estancia II</p>
        </footer> 
    </div>    
        

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const rolSelect = document.getElementById('rol');
                const camposAlumnoDiv = document.getElementById('campos-alumno');
                const camposAlumnoInputs = camposAlumnoDiv.querySelectorAll('input, select');

                function toggleCamposAlumno() {
                    const rolSeleccionado = rolSelect.value;

                    if (rolSeleccionado === 'alumnos') {
                        camposAlumnoDiv.style.display = 'block';
                        camposAlumnoInputs.forEach(input => {
                            input.disabled = false; 
                            input.required = true;  
                        });
                    } else {
                        camposAlumnoDiv.style.display = 'none';
                        camposAlumnoInputs.forEach(input => {
                            input.disabled = true;  
                            input.required = false; 
                        });
                    }
                }
                rolSelect.addEventListener('change', toggleCamposAlumno);
                toggleCamposAlumno();
            });
        </script>
</body>
</html>
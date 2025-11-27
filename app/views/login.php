<?php
    $message = '';
    $message_type = 'alert-error'; 
    $error = $_GET['error'] ?? 0;
    $correo_previo = isset($_GET['correo']) ? htmlspecialchars($_GET['correo']) : '';

    if ($error == '1') {
        $message = 'Error: Correo, contraseña o rol incorrectos. Verifica tus datos.';
    } elseif ($error == '2') {
        $message = 'Error: Todos los campos son obligatorios.';
    } elseif ($error == '3') {
        $message = 'Error: No se pudo conectar al sistema. Intenta más tarde.';
    } elseif ($error == '4') {
        $message = 'Acceso denegado. Por favor, inicia sesión.';
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <header class="header">
        <h1 class="display-4">Universidad Politécnica del Estado de Morelos</h1>
        <img src="img/upemor.png" alt="Logo Upemor" class="logoUpemor">
    </header>

    <main class="main">
        <section class="card register-card">
            <h1 class="login-title">Iniciar Sesión</h1>
            <p class="register-subtitle">Ingresa con tu cuenta</p>


            <?php if(!empty($message)): ?>
                <div class="alert-message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>


           <form action="index.php?action=checkLogin" method="POST">

                <div class="form-group">
                    <label for="correo">Correo electrónico:</label>
                    <input type="email" id="correo" name="correo" required value="<?php echo $correo_previo; ?>">
                </div>

                <div class="form-group">
                    <label for="contrasena">Contraseña:</label>
                    <input type="password" id="contrasena" name="contrasena" required>
                </div>

                <div class="form-group">
                    <label for="role">Selecciona tu rol...</label>

                    <select id="role" name="role" required>
                        <option value="alumno">Alumno</option>
                        <option value="administrador">Administrador</option>
                        <option value="coordinador">Coordinador</option>
                    </select>
                </div>
                
                <button type="submit" name="enviar" class="btn btn-register">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Ingresar
                </button>
            </form>
        </section>
    </main>
    <script src="bootstrap/js/script.js"></script>
</body>
</html>
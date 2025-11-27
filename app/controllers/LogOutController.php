<?php
    // Iniciar la sesión para poder destruirla
    session_start();

    // 1. Eliminar todas las variables de sesión
    $_SESSION = array();

    // 2. Destruir la sesión
    session_destroy();

    // 3. Redirigir al inicio (o al login)
    header("Location: index.php");
    exit();
?>
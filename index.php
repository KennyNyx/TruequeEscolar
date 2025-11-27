<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


include_once "config/dbConnection.php";


include_once "app/models/LoginModel.php";
include_once "app/models/EstudianteModel.php";
include_once "app/models/AdminModel.php";
include_once "app/models/CoordinadorModel.php";
include_once "app/models/ProductoModel.php";
include_once "app/models/ReunionModel.php";
include_once "app/models/ResenaModel.php";
include_once "app/models/ChatModel.php";
include_once "app/models/ReportesModel.php";



include_once "app/controllers/LoginController.php";
include_once "app/controllers/UserController.php";
include_once "app/controllers/AdminController.php";
include_once "app/controllers/CoordinadorController.php";
include_once "app/controllers/ProductoController.php";
include_once "app/controllers/ReunionController.php";
include_once "app/controllers/ResenaController.php";
include_once "app/controllers/ChatController.php";
include_once "app/controllers/ReportesController.php";


$db = conectar();

$loginController       = new LoginController($db);
$userController        = new UserController($db);
$adminController       = new AdminController();
$coordinadorController = new CoordinadorController();
$productoController    = new ProductoController($db);
$reunionController     = new ReunionController($db);
$resenaController = new ResenaController($db);
$chatController = new ChatController($db);
$reportesController    = new ReportesController($db);



$action = $_GET['action'] ?? 'login'; 


$accionesPublicas = ['login', 'checkLogin', 'mostrarRegistro', 'registrar']; 

if (!in_array($action, $accionesPublicas) && !isset($_SESSION['user_role'])) {
    header("Location: index.php?action=login&error=4");
    exit;
}


switch($action){


    case 'login':
        $loginController->showLogin();
        break;

    case 'checkLogin':
        $loginController->doLogin();
        break;

    case 'logout':
        $_SESSION = [];
        session_destroy();
        header("Location: index.php?action=login");
        exit;
        break;

  
    case 'mostrarRegistro':
        include "app/views/registro.php";
        break;

    case 'registrar':
        $userController->registrar();
        break;


    case 'vistaAdmin':
        include "app/views/vistaAdmin.php";
        break;

    case 'vistaCoordinador':
        $coordinadorController->vistaCoordinador();
        break;

    case 'vistaAlumnos':
        include "app/views/vistaAlmunos.php";
        break;

   
    case 'catalogo':
        include "app/views/Catalogo.php";
        break;

    case 'ver_categoria':
        include "app/views/ver_categoria.php";
        break;

    case 'chat':
        include "app/views/chat.php";
        break;

    case 'chat_ajax':
        $chatController->handleAjaxRequest();
        break;  

    
    case 'agregarProducto':
        include "app/views/formularioObjetos.php";
        break;

    case 'subir_producto':
        $productoController->subirProducto();
        break;

    case 'editar_producto':
        include "app/views/editarProducto.php";
        break;

    case 'actualizar_producto':
        $productoController->actualizarProducto();
        break;

    case 'eliminar_producto':
        $productoController->eliminarProducto();
        break;

    
    case 'comentar':
        $productoController->agregarComentario();
        break;

    case 'editar_comentario':
        include "app/views/editarComentario.php";
        break;

    case 'actualizar_comentario':
        $productoController->actualizarComentario();
        break;

    case 'eliminar_comentario':
        $productoController->eliminarComentario();
        break;

   
    case 'admin_consultar':
        $adminController->consultar();
        break;

    case 'admin_editar':
        $adminController->editar();
        break;    

    case 'admin_registrar':
        $adminController->registrar();
        break;

    case 'admin_actualizar':
        $adminController->actualizar();
        break;

    case 'admin_eliminar':
        $adminController->eliminar();
        break;

    
    case 'coord_consultar':
        $coordinadorController->consultar();
        break;

    case 'coord_editar':
        $coordinadorController->editar();
        break;    

    case 'coord_registrar':
        $coordinadorController->registrar();
        break;

    case 'coord_actualizar':
        $coordinadorController->actualizar();
        break;

    case 'coord_eliminar':
        $coordinadorController->eliminar();
        break;

   
    case 'user_consultar':
        $userController->consultar();
        break;

    case 'user_editar':
        $userController->editar();
        break;

    case 'user_actualizar':
        $userController->actualizar();
        break;

    case 'user_eliminar':
        $userController->eliminar();
        break;

    
    case 'reuniones':
        include 'app/views/reunion.php';
        break;

    case 'crear_reunion':
        $reunionController->mostrarFormularioCrear();
        break;

    case 'procesar_reunion':
        $reunionController->crearReunion();
        break;

    case 'gestionar_reuniones':
        $reunionController->gestionarReuniones();
        break;

    case 'confirmar_reunion':
        $reunionController->confirmarReunion();
        break;

    case 'cancelar_reunion':
        $reunionController->cancelarReunion();
        break;
    
    case 'eliminar_reunion':
        $reunionController->eliminarReunion();
        break;
        
    case 'editar_reunion':
        $reunionController->mostrarFormularioEditar();
        break;

    case 'actualizar_reunion':
        $reunionController->actualizarReunion();
        break;
        


    case 'resenas':
        $resenaController->mostrarMenuResenas();
        break;

    case 'crear_resena':
        $resenaController->mostrarFormularioResena();
        break;

    case 'procesar_resena':
        $resenaController->procesarResena();
        break;

   case 'ver_resenas':
        
        $resenaController->verListadoResenas();
        break;
    
    case 'editar_resena':
        $resenaController->mostrarFormularioEditar();
        break;    

    case 'eliminar_resena':
        $resenaController->eliminarResena();
        break;

    case 'ver_resenas':
        $resenaController->verListadoResenas();
        break;
    
    case 'editar_resena':
        $resenaController->mostrarFormularioEditar();
        break;

    case 'actualizar_resena':
        $resenaController->actualizarResena();
        break;

    case 'eliminar_resena':
        $resenaController->eliminarResena();
        break;

    
    case 'generar_reportes':
        $reportesController->mostrarReportes();
        break;
        
    case 'ver_pdf':
        $reportesController->verPDF();
        break;    

    case 'descargar_pdf':
        $reportesController->generarPDF();
        break;    
        

    
    default:
        header("Location: index.php?action=login");
        break;
}
?>


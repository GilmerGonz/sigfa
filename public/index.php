<?php
/**
 * =====================================================
 * SIGFA - Front Controller
 * =====================================================
 * Punto de entrada único de la aplicación.
 * Enruta todas las solicitudes al controlador adecuado.
 * =====================================================
 */

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir constante de ruta base
define('BASE_PATH', dirname(__DIR__));

// Definir rutas absolutas del proyecto
define('APPROOT', BASE_PATH);
define('MODELROOT', BASE_PATH . '/app/models');
define('CONTROLLERROOT', BASE_PATH . '/app/controllers');
define('MIDDLEWAREROOT', BASE_PATH . '/app/middleware');
define('CONFIGROOT', BASE_PATH . '/config');

// Cargar configuración
require_once CONFIGROOT . '/db.php';

// Cargar autoload de Composer
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Obtener la URL solicitada
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);

// Enrutamiento completo del SIGFA
switch ($url) {
    // =====================================================
    // AUTENTICACIÓN
    // =====================================================
    case '':
    case 'login':
        require_once BASE_PATH . '/app/controllers/AuthController.php';
        $controlador = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controlador->procesarLogin();
        } else {
            $controlador->mostrarLogin();
        }
        break;

    case 'logout':
        require_once BASE_PATH . '/app/controllers/AuthController.php';
        $controlador = new AuthController();
        $controlador->cerrarSesion();
        break;

    // =====================================================
    // DASHBOARD
    // =====================================================
    case 'dashboard':
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php');
            exit;
        }
        require_once BASE_PATH . '/app/controllers/DashboardController.php';
        $controlador = new DashboardController();
        $controlador->mostrar();
        break;

    // =====================================================
    // DESPACHOS
    // =====================================================
    case 'despachos/crear':
        require_once BASE_PATH . '/app/controllers/DespachoController.php';
        $controlador = new DespachoController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controlador->procesarDespacho();
        } else {
            header('Location: index.php?url=despachos');
        }
        break;

    case 'despachos':
    case 'despachos/lista':
    case 'despachos/nuevo':
        require_once BASE_PATH . '/app/controllers/DespachoController.php';
        $controlador = new DespachoController();
        $controlador->listarHoy();
        break;

    case 'despachos/anular':
        require_once BASE_PATH . '/app/controllers/DespachoController.php';
        $controlador = new DespachoController();
        $controlador->anularDespacho();
        break;

    // =====================================================
    // INVENTARIO
    // =====================================================
    case 'inventario/entrada':
        require_once BASE_PATH . '/app/controllers/InventarioController.php';
        $controlador = new InventarioController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controlador->procesarEntrada();
        } else {
            $controlador->mostrarFormularioEntrada();
        }
        break;

    case 'inventario/alertas':
        require_once BASE_PATH . '/app/controllers/InventarioController.php';
        $controlador = new InventarioController();
        $controlador->mostrarAlertas();
        break;

    case 'inventario/kardex':
        require_once BASE_PATH . '/app/controllers/InventarioController.php';
        $controlador = new InventarioController();
        $controlador->mostrarKardex();
        break;

    case 'inventario/ajustar':
        require_once BASE_PATH . '/app/controllers/InventarioController.php';
        $controlador = new InventarioController();
        $controlador->ajustarLote();
        break;

    case 'inventario/eliminar-lote':
        require_once BASE_PATH . '/app/controllers/InventarioController.php';
        $controlador = new InventarioController();
        $controlador->anularEntrada();
        break;

    // =====================================================
    // MÓDULOS CRUD (Asegurados, Medicamentos, Médicos...)
    // =====================================================
    case 'asegurados':
        require_once BASE_PATH . '/app/controllers/ModuloController.php';
        $controlador = new ModuloController();
        $controlador->listarAsegurados();
        break;
    case 'asegurados/crear':
        require_once BASE_PATH . '/app/controllers/ModuloController.php';
        $controlador = new ModuloController();
        $controlador->crearAsegurado();
        break;

    case 'medicamentos':
        require_once BASE_PATH . '/app/controllers/ModuloController.php';
        $controlador = new ModuloController();
        $controlador->listarMedicamentos();
        break;
    case 'medicamentos/crear':
        require_once BASE_PATH . '/app/controllers/ModuloController.php';
        $controlador = new ModuloController();
        $controlador->crearMedicamento();
        break;

    case 'medicos':
        require_once BASE_PATH . '/app/controllers/ModuloController.php';
        $controlador = new ModuloController();
        $controlador->listarMedicos();
        break;
    case 'medicos/crear':
        require_once BASE_PATH . '/app/controllers/ModuloController.php';
        $controlador = new ModuloController();
        $controlador->crearMedico();
        break;

    case 'proveedores':
        require_once BASE_PATH . '/app/controllers/ModuloController.php';
        $controlador = new ModuloController();
        $controlador->listarProveedores();
        break;
    case 'proveedores/crear':
        require_once BASE_PATH . '/app/controllers/ModuloController.php';
        $controlador = new ModuloController();
        $controlador->crearProveedor();
        break;

    case 'usuarios':
        require_once BASE_PATH . '/app/controllers/ModuloController.php';
        $controlador = new ModuloController();
        $controlador->listarUsuarios();
        break;
    case 'usuarios/crear':
        require_once BASE_PATH . '/app/controllers/ModuloController.php';
        $controlador = new ModuloController();
        $controlador->crearUsuario();
        break;

    // =====================================================
    // TRANSFERENCIAS (Almacenista/Admin)
    // =====================================================
    case 'transferencias':
        require_once BASE_PATH . '/app/controllers/TransferenciaController.php';
        $controlador = new TransferenciaController();
        $controlador->index();
        break;
    case 'transferencias/crear':
        require_once BASE_PATH . '/app/controllers/TransferenciaController.php';
        $controlador = new TransferenciaController();
        $controlador->crear();
        break;
    case 'transferencias/anular':
        require_once BASE_PATH . '/app/controllers/TransferenciaController.php';
        $controlador = new TransferenciaController();
        $controlador->anular();
        break;
    case 'transferencias/ajaxLotes':
        require_once BASE_PATH . '/app/controllers/TransferenciaController.php';
        $controlador = new TransferenciaController();
        $controlador->ajaxBuscarLotes();
        break;

    // =====================================================
    // DEVOLUCIONES A PROVEEDORES (Admin/Almacenista/Farmacéutico)
    // =====================================================
    case 'devoluciones':
        require_once BASE_PATH . '/app/controllers/DevolucionController.php';
        $controlador = new DevolucionController();
        $controlador->index();
        break;
    case 'devoluciones/crear':
        require_once BASE_PATH . '/app/controllers/DevolucionController.php';
        $controlador = new DevolucionController();
        $controlador->crear();
        break;
    case 'devoluciones/ajaxLotes':
        require_once BASE_PATH . '/app/controllers/DevolucionController.php';
        $controlador = new DevolucionController();
        $controlador->ajaxLotes();
        break;

    case 'devoluciones/comprobante':
        require_once BASE_PATH . '/app/controllers/DevolucionController.php';
        $controlador = new DevolucionController();
        $controlador->generarComprobante();
        break;

    case 'devoluciones/anular':
        require_once BASE_PATH . '/app/controllers/DevolucionController.php';
        $controlador = new DevolucionController();
        $controlador->anular();
        break;

    // =====================================================
    // SERVICIOS MÉDICOS (Admin/Auxiliar)
    // =====================================================
    case 'servicios':
        require_once BASE_PATH . '/app/controllers/ServicioController.php';
        $controlador = new ServicioController();
        $controlador->index();
        break;
    case 'servicios/crear':
        require_once BASE_PATH . '/app/controllers/ServicioController.php';
        $controlador = new ServicioController();
        $controlador->crear();
        break;
    case 'servicios/eliminar':
        require_once BASE_PATH . '/app/controllers/ServicioController.php';
        $controlador = new ServicioController();
        $controlador->eliminar();
        break;
    case 'servicios/ajax':
        require_once BASE_PATH . '/app/controllers/ServicioController.php';
        $controlador = new ServicioController();
        $controlador->ajaxBuscar();
        break;

    // =====================================================
    // GRUPOS DE MEDICAMENTOS
    // =====================================================
    case 'grupos':
        require_once BASE_PATH . '/app/controllers/GrupoController.php';
        $controlador = new GrupoController();
        $controlador->index();
        break;
    case 'grupos/crear':
        require_once BASE_PATH . '/app/controllers/GrupoController.php';
        $controlador = new GrupoController();
        $controlador->crear();
        break;
    case 'grupos/editar':
        require_once BASE_PATH . '/app/controllers/GrupoController.php';
        $controlador = new GrupoController();
        $controlador->editar();
        break;
    case 'grupos/toggle':
        require_once BASE_PATH . '/app/controllers/GrupoController.php';
        $controlador = new GrupoController();
        $controlador->toggle();
        break;
    case 'grupos/ajax':
        require_once BASE_PATH . '/app/controllers/GrupoController.php';
        $controlador = new GrupoController();
        $controlador->ajaxBuscar();
        break;

    // =====================================================
    // ALMACENES
    // =====================================================
    case 'almacenes':
        require_once BASE_PATH . '/app/controllers/AlmacenController.php';
        $controlador = new AlmacenController();
        $controlador->index();
        break;
    case 'almacenes/crear':
        require_once BASE_PATH . '/app/controllers/AlmacenController.php';
        $controlador = new AlmacenController();
        $controlador->crear();
        break;
    case 'almacenes/editar':
        require_once BASE_PATH . '/app/controllers/AlmacenController.php';
        $controlador = new AlmacenController();
        $controlador->editar();
        break;
    case 'almacenes/toggle':
        require_once BASE_PATH . '/app/controllers/AlmacenController.php';
        $controlador = new AlmacenController();
        $controlador->toggle();
        break;
    case 'almacenes/ajax':
        require_once BASE_PATH . '/app/controllers/AlmacenController.php';
        $controlador = new AlmacenController();
        $controlador->ajaxBuscar();
        break;

    // =====================================================
    // PATOLOGÍAS (Admin/Auxiliar)
    // =====================================================
    case 'patologias':
        require_once BASE_PATH . '/app/controllers/PatologiaController.php';
        $controlador = new PatologiaController();
        $controlador->index();
        break;
    case 'patologias/crear':
        require_once BASE_PATH . '/app/controllers/PatologiaController.php';
        $controlador = new PatologiaController();
        $controlador->crear();
        break;
    case 'patologias/eliminar':
        require_once BASE_PATH . '/app/controllers/PatologiaController.php';
        $controlador = new PatologiaController();
        $controlador->eliminar();
        break;

    // =====================================================
    // REPORTES
    // =====================================================
    case 'reportes/servicio':
        require_once BASE_PATH . '/app/controllers/ReporteController.php';
        $controlador = new ReporteController();
        $controlador->porServicio();
        break;
    case 'reportes/medicamento':
        require_once BASE_PATH . '/app/controllers/ReporteController.php';
        $controlador = new ReporteController();
        $controlador->porMedicamento();
        break;
    case 'reportes/periodo':
        require_once BASE_PATH . '/app/controllers/ReporteController.php';
        $controlador = new ReporteController();
        $controlador->porPeriodo();
        break;
    case 'reportes/consumo':
        require_once BASE_PATH . '/app/controllers/ReporteController.php';
        $controlador = new ReporteController();
        $controlador->consumo();
        break;
    case 'reportes/inventario':
        require_once BASE_PATH . '/app/controllers/ReporteController.php';
        $controlador = new ReporteController();
        $controlador->inventario();
        break;
    case 'reportes/kardex':
        require_once BASE_PATH . '/app/controllers/ReporteController.php';
        $controlador = new ReporteController();
        $controlador->kardex();
        break;
    case 'reportes/auditoria':
        require_once BASE_PATH . '/app/controllers/ReporteController.php';
        $controlador = new ReporteController();
        $controlador->auditoria();
        break;
    case 'reportes/patologia':
        require_once BASE_PATH . '/app/controllers/ReporteController.php';
        $controlador = new ReporteController();
        $controlador->porPatologia();
        break;

    case 'reportes/paciente':
        require_once BASE_PATH . '/app/controllers/ReporteController.php';
        $controlador = new ReporteController();
        $controlador->prescripcionPaciente();
        break;

    case 'reportes/recetas':
        require_once BASE_PATH . '/app/controllers/ReporteController.php';
        $controlador = new ReporteController();
        $controlador->recetasDiarias();
        break;

    case 'reportes/consumo_masivo':
        require_once BASE_PATH . '/app/controllers/ReporteController.php';
        $controlador = new ReporteController();
        $controlador->consumoMasivo();
        break;

    case 'reportes/costo_promedio':
        require_once BASE_PATH . '/app/controllers/ReporteController.php';
        $controlador = new ReporteController();
        $controlador->costoPromedio();
        break;

    case 'reportes/alertas':
        require_once BASE_PATH . '/app/controllers/ReporteController.php';
        $controlador = new ReporteController();
        $controlador->alertasCalidad();
        break;

    // =====================================================
    // AJAX - ComboBox Dinámicos
    // =====================================================
    case 'ajax/medicamentos':
        require_once BASE_PATH . '/app/controllers/AjaxController.php';
        $ctrl = new AjaxController();
        $ctrl->buscarMedicamentos();
        break;
    case 'ajax/pacientes':
        require_once BASE_PATH . '/app/controllers/AjaxController.php';
        $ctrl = new AjaxController();
        $ctrl->buscarPacientes();
        break;
    case 'ajax/medicos':
        require_once BASE_PATH . '/app/controllers/AjaxController.php';
        $ctrl = new AjaxController();
        $ctrl->buscarMedicos();
        break;
    case 'ajax/proveedores':
        require_once BASE_PATH . '/app/controllers/AjaxController.php';
        $ctrl = new AjaxController();
        $ctrl->buscarProveedores();
        break;
    case 'ajax/grupos':
        require_once BASE_PATH . '/app/controllers/AjaxController.php';
        $ctrl = new AjaxController();
        $ctrl->buscarGrupos();
        break;
    case 'ajax/almacenes':
        require_once BASE_PATH . '/app/controllers/AjaxController.php';
        $ctrl = new AjaxController();
        $ctrl->buscarAlmacenes();
        break;
    case 'ajax/servicios':
        require_once BASE_PATH . '/app/controllers/AjaxController.php';
        $ctrl = new AjaxController();
        $ctrl->buscarServicios();
        break;
    case 'ajax/patologias':
        require_once BASE_PATH . '/app/controllers/AjaxController.php';
        $ctrl = new AjaxController();
        $ctrl->buscarPatologias();
        break;

    case 'inventario/ajuste':
        require_once BASE_PATH . '/app/controllers/InventarioController.php';
        $controlador = new InventarioController();
        $controlador->mostrarAjuste();
        break;

    case 'inventario/ajustar-post':
        require_once BASE_PATH . '/app/controllers/InventarioController.php';
        $controlador = new InventarioController();
        $controlador->ajustarLote();
        break;

    // =====================================================
    // BACKUP (Solo Admin)
    // =====================================================
    case 'backup':
        require_once BASE_PATH . '/app/controllers/BackupController.php';
        $controlador = new BackupController();
        $controlador->index();
        break;
    case 'backup/crear':
        require_once BASE_PATH . '/app/controllers/BackupController.php';
        $controlador = new BackupController();
        $controlador->crear();
        break;
    case 'backup/descargar':
        require_once BASE_PATH . '/app/controllers/BackupController.php';
        $controlador = new BackupController();
        $controlador->descargar();
        break;
    case 'backup/eliminar':
        require_once BASE_PATH . '/app/controllers/BackupController.php';
        $controlador = new BackupController();
        $controlador->eliminar();
        break;

    case 'api/verificar-ciclo-dosis':
        require_once BASE_PATH . '/app/controllers/DespachoController.php';
        $controlador = new DespachoController();
        $controlador->verificarCicloDosisAjax();
        break;

    case 'api/verificar-duplicidad-item':
        require_once BASE_PATH . '/app/controllers/DespachoController.php';
        $controlador = new DespachoController();
        $controlador->verificarDuplicidadItemAjax();
        break;

    // =====================================================
    // 404 - PÁGINA NO ENCONTRADA
    // =====================================================
    default:
        http_response_code(404);
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>404 - SIGFA</title>';
        echo '<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap" rel="stylesheet">';
        echo '</head><body style="display:flex;align-items:center;justify-content:center;height:100vh;font-family:Outfit,sans-serif;background:#000e2e;color:#fff;margin:0;">';
        echo '<div style="text-align:center">';
        echo '<div style="font-size:8rem;font-weight:800;line-height:1;background:linear-gradient(135deg,#5b9cf5,#3b6fcc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">404</div>';
        echo '<p style="font-size:1.1rem;color:#94a3b8;margin:1rem 0 2rem;">La página que buscas no existe en el sistema</p>';
        echo '<a href="index.php" style="display:inline-block;padding:12px 28px;background:linear-gradient(135deg,#3b6fcc,#5b9cf5);color:#fff;text-decoration:none;border-radius:12px;font-weight:600;transition:transform 0.3s;" onmouseover="this.style.transform=\'translateY(-2px)\'" onmouseout="this.style.transform=\'translateY(0)\'">Volver al inicio</a>';
        echo '</div></body></html>';
        break;
}

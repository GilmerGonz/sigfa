<?php
/**
 * =====================================================
 * SIGFA - Controlador: Autenticación
 * =====================================================
 * Gestiona login, logout y validación de sesión.
 * =====================================================
 */

require_once __DIR__ . '/../models/Usuario.php';

class AuthController
{
    private Usuario $modeloUsuario;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->modeloUsuario = new Usuario();
    }

    /**
     * Mostrar la vista de login.
     */
    public function mostrarLogin(): void
    {
        // Si ya tiene sesión activa, redirigir al dashboard
        if ($this->estaAutenticado()) {
            header('Location: index.php?url=dashboard');
            exit;
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        require_once __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Procesar el formulario de login.
     */
    public function procesarLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        // Validar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['login_error'] = 'Solicitud inválida. Intente de nuevo.';
            header('Location: index.php');
            exit;
        }

        $cedulaInput = trim($_POST['cedula'] ?? '');
        $clave  = $_POST['clave'] ?? '';

        // --- MEJORA: Auto-formateo de Cédula ---
        // Si el usuario ingresó solo números, asumimos el prefijo 'V-' (Venezolano)
        $cedula = $cedulaInput;
        if (is_numeric($cedulaInput)) {
            $cedula = 'V-' . $cedulaInput;
        }

        // Validaciones básicas
        if (empty($cedulaInput) || empty($clave)) {
            $_SESSION['login_error'] = 'Todos los campos son obligatorios.';
            header('Location: index.php');
            exit;
        }

        // Intentar autenticar con el resultado detallado
        $resultado = $this->modeloUsuario->verificarCredenciales($cedula, $clave);

        if ($resultado['status'] === 'EXITO') {
            $usuario = $resultado['usuario'];
            
            // Regenerar ID de sesión para prevenir session fixation
            session_regenerate_id(true);

            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
            $_SESSION['usuario_rol']    = $usuario['rol'];
            $_SESSION['usuario_cedula'] = $usuario['cedula'];

            header('Location: index.php?url=dashboard');
            exit;
        }

        // Manejo de errores específicos para guiar al usuario
        switch($resultado['status']) {
            case 'DB_VACIA':
                $_SESSION['login_error'] = 'Base de datos sin usuarios. Ejecute /database/schema.sql o /fix_admin.php.';
                break;
            case 'INACTIVO':
                $_SESSION['login_error'] = 'Su cuenta está inactiva. Contacte al administrador.';
                break;
            case 'NO_EXISTE':
                $_SESSION['login_error'] = "El usuario '{$cedula}' no está registrado.";
                break;
            default: // CLAVE_INCORRECTA
                $_SESSION['login_error'] = 'Contraseña incorrecta. Verifique sus datos.';
        }
        header('Location: index.php');
        exit;
    }

    /**
     * Cerrar sesión del usuario.
     */
    public function cerrarSesion(): void
    {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }

    /**
     * Verificar si el usuario está autenticado.
     */
    public function estaAutenticado(): bool
    {
        return isset($_SESSION['usuario_id']);
    }

    /**
     * Generar token CSRF para formularios.
     */
    public static function generarTokenCSRF(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

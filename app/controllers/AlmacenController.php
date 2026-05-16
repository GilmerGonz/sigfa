<?php
/**
 * SIGFA - Controlador: Almacenes
 */
require_once __DIR__ . '/../models/Almacen.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AlmacenController
{
    private Almacen $modelo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit; }
        $this->modelo = new Almacen();
    }

    public function index(): void
    {
        $almacenes = $this->modelo->listar();
        $exito = $_SESSION['modulo_exito'] ?? null;
        $error = $_SESSION['modulo_error'] ?? null;
        unset($_SESSION['modulo_exito'], $_SESSION['modulo_error']);
        require_once __DIR__ . '/../views/almacenes/index.php';
    }

    public function crear(): void
    {
        AuthMiddleware::verificarRol('Administrador');
        $this->validarCSRF();
        
        try {
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $tipo = trim($_POST['tipo'] ?? 'General');
            $ubicacion = trim($_POST['ubicacion'] ?? '');
            
            if (empty($codigo) || empty($nombre)) {
                throw new \RuntimeException("Código y nombre son obligatorios.");
            }
            
            if ($this->modelo->buscarPorCodigo($codigo)) {
                throw new \RuntimeException("Ya existe un almacén con el código $codigo.");
            }
            
            $this->modelo->crear([
                'codigo' => $codigo,
                'nombre' => $nombre,
                'tipo' => $tipo,
                'ubicacion' => $ubicacion
            ]);
            $_SESSION['modulo_exito'] = '✅ Almacén creado exitosamente.';
        } catch (\RuntimeException $e) {
            $_SESSION['modulo_error'] = '⚠️ ' . $e->getMessage();
        }
        header('Location: index.php?url=almacenes');
        exit;
    }

    public function editar(): void
    {
        AuthMiddleware::verificarRol('Administrador');
        $this->validarCSRF();
        
        try {
            $id = (int) $_POST['id'];
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $tipo = trim($_POST['tipo'] ?? 'General');
            $ubicacion = trim($_POST['ubicacion'] ?? '');
            
            if (empty($codigo) || empty($nombre)) {
                throw new \RuntimeException("Código y nombre son obligatorios.");
            }
            
            $existente = $this->modelo->buscarPorCodigo($codigo);
            if ($existente && $existente['id'] != $id) {
                throw new \RuntimeException("Ya existe otro almacén con el código $codigo.");
            }
            
            $this->modelo->actualizar($id, [
                'codigo' => $codigo,
                'nombre' => $nombre,
                'tipo' => $tipo,
                'ubicacion' => $ubicacion
            ]);
            $_SESSION['modulo_exito'] = '✅ Almacén actualizado.';
        } catch (\RuntimeException $e) {
            $_SESSION['modulo_error'] = '⚠️ ' . $e->getMessage();
        }
        header('Location: index.php?url=almacenes');
        exit;
    }

    public function toggle(): void
    {
        AuthMiddleware::verificarRol('Administrador');
        
        try {
            $id = (int) ($_GET['id'] ?? 0);
            if ($id <= 0) throw new \RuntimeException("ID inválido.");
            
            $this->modelo->toggleActivo($id);
            $_SESSION['modulo_exito'] = '✅ Estado toggled.';
        } catch (\RuntimeException $e) {
            $_SESSION['modulo_error'] = '⚠️ ' . $e->getMessage();
        }
        header('Location: index.php?url=almacenes');
        exit;
    }

    public function ajaxBuscar(): void
    {
        header('Content-Type: application/json');
        echo json_encode($this->modelo->listarActivos());
    }

    private function validarCSRF(): void
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['modulo_error'] = 'Token de seguridad inválido.';
            header('Location: index.php?url=almacenes');
            exit;
        }
    }
}
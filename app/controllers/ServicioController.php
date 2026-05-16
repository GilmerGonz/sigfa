<?php
require_once __DIR__ . '/../models/ServicioMedico.php';

class ServicioController
{
    private $modelo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->modelo = new ServicioMedico();
    }

    private function verificarAcceso(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php');
            exit;
        }
        $rol = $_SESSION['usuario_rol'];
        if ($rol !== 'Administrador' && $rol !== 'Auxiliar') {
            $_SESSION['modulo_error'] = 'No tiene acceso a este módulo.';
            header('Location: index.php?url=dashboard');
            exit;
        }
    }

    public function index(): void
    {
        $this->verificarAcceso();

        $servicios = $this->modelo->listarActivos();
        $error = $_SESSION['modulo_error'] ?? null;
        $exito = $_SESSION['modulo_exito'] ?? null;
        unset($_SESSION['modulo_error'], $_SESSION['modulo_exito']);

        require_once __DIR__ . '/../views/servicios/index.php';
    }

    public function crear(): void
    {
        $this->verificarAcceso();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=servicios');
            exit;
        }

        try {
            $this->validarCSRF();

            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');

            if (empty($codigo) || empty($nombre)) {
                throw new \RuntimeException('El código y nombre son obligatorios.');
            }

            if ($this->modelo->buscarPorCodigo($codigo)) {
                throw new \RuntimeException("El código {$codigo} ya existe.");
            }

            $this->modelo->crear([
                'codigo' => $codigo,
                'nombre' => $nombre,
                'descripcion' => $_POST['descripcion'] ?? ''
            ]);

            $_SESSION['modulo_exito'] = '✅ Servicio médico registrado.';
        } catch (\Exception $e) {
            $_SESSION['modulo_error'] = $e->getMessage();
        }

        header('Location: index.php?url=servicios');
        exit;
    }

    public function eliminar(): void
    {
        $this->verificarAcceso();

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->modelo->eliminar($id);
            $_SESSION['modulo_exito'] = 'Servicio eliminado.';
        }

        header('Location: index.php?url=servicios');
        exit;
    }

    public function ajaxBuscar(): void
    {
        $this->verificarAcceso();
        header('Content-Type: application/json; charset=utf-8');

        $query = trim($_GET['q'] ?? '');
        if (empty($query)) {
            echo json_encode([]);
            return;
        }

        $resultados = $this->modelo->buscar($query);
        echo json_encode($resultados);
    }

    private function validarCSRF(): void
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            throw new \RuntimeException('Token de seguridad inválido.');
        }
    }
}
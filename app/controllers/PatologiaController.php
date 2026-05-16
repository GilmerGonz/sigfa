<?php
require_once __DIR__ . '/../models/Patologia.php';

class PatologiaController
{
    private $modelo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->modelo = new Patologia();
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

        $clasificacion = $_GET['clasificacion'] ?? null;
        if ($clasificacion) {
            $patologias = $this->modelo->listarPorClasificacion($clasificacion);
        } else {
            $patologias = $this->modelo->listarActivos();
        }

        $error = $_SESSION['modulo_error'] ?? null;
        $exito = $_SESSION['modulo_exito'] ?? null;
        unset($_SESSION['modulo_error'], $_SESSION['modulo_exito']);

        require_once __DIR__ . '/../views/patologias/index.php';
    }

    public function crear(): void
    {
        $this->verificarAcceso();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=patologias');
            exit;
        }

        try {
            $this->validarCSRF();

            $nombre = trim($_POST['nombre'] ?? '');
            $clasificacion = $_POST['clasificacion'] ?? '';

            if (empty($nombre) || empty($clasificacion)) {
                throw new \RuntimeException('El nombre y clasificación son obligatorios.');
            }

            if (!in_array($clasificacion, ['Alto_Costo', 'Comun'])) {
                throw new \RuntimeException('Clasificación inválida.');
            }

            if ($this->modelo->buscarPorNombre($nombre)) {
                throw new \RuntimeException("La patología '{$nombre}' ya existe.");
            }

            $this->modelo->crear([
                'nombre' => $nombre,
                'clasificacion' => $clasificacion,
                'grupo_etario' => $_POST['grupo_etario'] ?? null,
                'descripcion' => $_POST['descripcion'] ?? ''
            ]);

            $_SESSION['modulo_exito'] = '✅ Patología registrada.';
        } catch (\Exception $e) {
            $_SESSION['modulo_error'] = $e->getMessage();
        }

        header('Location: index.php?url=patologias');
        exit;
    }

    public function eliminar(): void
    {
        $this->verificarAcceso();

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->modelo->eliminar($id);
            $_SESSION['modulo_exito'] = 'Patología eliminada.';
        }

        header('Location: index.php?url=patologias');
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
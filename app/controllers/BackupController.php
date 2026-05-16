<?php
require_once __DIR__ . '/../models/Backup.php';

class BackupController
{
    private $modelo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php');
            exit;
        }
        if ($_SESSION['usuario_rol'] !== 'Administrador') {
            $_SESSION['modulo_error'] = 'Solo el administrador puede acceder a esta sección.';
            header('Location: index.php?url=dashboard');
            exit;
        }
        $this->modelo = new Backup();
    }

    public function index(): void
    {
        $backups = $this->modelo->listarBackups();
        $error = $_SESSION['modulo_error'] ?? null;
        $exito = $_SESSION['modulo_exito'] ?? null;
        unset($_SESSION['modulo_error'], $_SESSION['modulo_exito']);

        $tituloPagina = 'Backup y Mantenimiento';
        require_once __DIR__ . '/../views/backup/index.php';
    }

    public function crear(): void
    {
        $resultado = $this->modelo->generarBackup();
        
        if ($resultado['success']) {
            $_SESSION['modulo_exito'] = "✅ Backup creado: {$resultado['filename']} (" . number_format($resultado['tamano']/1024, 2) . " KB)";
        } else {
            $_SESSION['modulo_error'] = "Error al crear backup: {$resultado['error']}";
        }

        header('Location: index.php?url=backup');
        exit;
    }

    public function eliminar(): void
    {
        $nombre = $_GET['nombre'] ?? '';
        if ($nombre) {
            $this->modelo->eliminarBackup($nombre);
            $_SESSION['modulo_exito'] = 'Backup eliminado.';
        }
        header('Location: index.php?url=backup');
        exit;
    }

    public function descargar(): void
    {
        $nombre = $_GET['nombre'] ?? '';
        $ruta = dirname(__DIR__, 2) . '/backups/' . $nombre;
        
        if (file_exists($ruta)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($ruta) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($ruta));
            readfile($ruta);
            exit;
        }
    }
}
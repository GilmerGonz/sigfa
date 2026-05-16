<?php
require_once __DIR__ . '/../models/Devolucion.php';
require_once __DIR__ . '/../models/Medicamento.php';
require_once __DIR__ . '/../models/Proveedor.php';

class DevolucionController
{
    private $modelo;
    private $medicamento;
    private $proveedor;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->modelo = new Devolucion();
        $this->medicamento = new Medicamento();
        $this->proveedor = new Proveedor();
    }

    private function verificarAcceso(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php');
            exit;
        }
        $rol = $_SESSION['usuario_rol'];
        $rolesPermitidos = ['Administrador', 'Almacenista', 'Farmaceutico'];
        if (!in_array($rol, $rolesPermitidos)) {
            $_SESSION['modulo_error'] = 'No tiene acceso a este módulo.';
            header('Location: index.php?url=dashboard');
            exit;
        }
    }

    public function index(): void
    {
        $this->verificarAcceso();

        $devoluciones = $this->modelo->listar();
        $proveedores = $this->proveedor->listarActivos();
        $medicamentos = $this->medicamento->listarConStock();
        $error = $_SESSION['modulo_error'] ?? null;
        $exito = $_SESSION['modulo_exito'] ?? null;
        unset($_SESSION['modulo_error'], $_SESSION['modulo_exito']);

        require_once __DIR__ . '/../views/devoluciones/index.php';
    }

    public function crear(): void
    {
        $this->verificarAcceso();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=devoluciones');
            exit;
        }

        try {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                throw new \RuntimeException('Token de seguridad inválido.');
            }

            $datos = [
                'proveedor_id'      => (int) ($_POST['proveedor_id'] ?? 0),
                'medicamento_id'   => (int) ($_POST['medicamento_id'] ?? 0),
                'lote_id'          => (int) ($_POST['lote_id'] ?? 0),
                'cantidad'         => (int) ($_POST['cantidad'] ?? 0),
                'motivo'           => $_POST['motivo'] ?? '',
                'observaciones'   => $_POST['observaciones'] ?? '',
                'numero_comprobante' => $_POST['numero_comprobante'] ?? ''
            ];

            if ($datos['proveedor_id'] <= 0) throw new \RuntimeException('Seleccione un proveedor.');
            if ($datos['medicamento_id'] <= 0) throw new \RuntimeException('Seleccione un medicamento.');
            if ($datos['lote_id'] <= 0) throw new \RuntimeException('Seleccione un lote.');
            if ($datos['cantidad'] <= 0) throw new \RuntimeException('La cantidad debe ser mayor a cero.');
            if (empty($datos['motivo'])) throw new \RuntimeException('Seleccione el motivo de la devolución.');

            $this->modelo->crear($datos, $_SESSION['usuario_id']);
            $_SESSION['modulo_exito'] = '✅ Devolución registrada exitosamente.';

        } catch (\RuntimeException $e) {
            $_SESSION['modulo_error'] = '⚠️ ' . $e->getMessage();
        }

        header('Location: index.php?url=devoluciones');
        exit;
    }

    public function ajaxLotes(): void
    {
        $this->verificarAcceso();
        header('Content-Type: application/json; charset=utf-8');

        $medicamentoId = (int) ($_GET['medicamento_id'] ?? 0);
        if ($medicamentoId <= 0) {
            echo json_encode(['error' => 'Parámetro inválido']);
            return;
        }

        require_once __DIR__ . '/../models/Inventario.php';
        $inventario = new Inventario();
        $lotes = $inventario->listarLotesMedicamento($medicamentoId);

        echo json_encode(['lotes' => $lotes]);
    }

    /**
     * Generar comprobante PDF de devolución
     */
    public function generarComprobante(): void
    {
        $this->verificarAcceso();
        
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['modulo_error'] = 'ID inválido.';
            header('Location: index.php?url=devoluciones');
            exit;
        }

        $devolucion = $this->modelo->buscarPorId($id);
        if (!$devolucion) {
            $_SESSION['modulo_error'] = 'Devolución no encontrada.';
            header('Location: index.php?url=devoluciones');
            exit;
        }

        // Generar HTML del comprobante
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Comprobante Devolución ' . $devolucion['numero_comprobante'] . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .info { margin: 15px 0; }
        .info table { width: 100%; }
        .info td { padding: 5px; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SISTEMA DE GESTIÓN FARMACÉUTICA – SIGFA</h2>
        <h3>HOSPITAL GENERAL MUNICIPAL Dr. "JUAN DAZ" PEREYRA</h3>
        <h4>COMPROBANTE DE DEVOLUCIÓN A PROVEEDOR</h4>
    </div>
    <div class="info">
        <table>
            <tr><td><strong>Número:</strong></td><td>' . $devolucion['numero_comprobante'] . '</td></tr>
            <tr><td><strong>Fecha:</strong></td><td>' . date('d/m/Y H:i', strtotime($devolucion['fecha_devolucion'])) . '</td></tr>
            <tr><td><strong>Proveedor:</strong></td><td>' . $devolucion['proveedor_nombre'] . ' (' . $devolucion['rif'] . ')</td></tr>
            <tr><td><strong>Medicamento:</strong></td><td>' . $devolucion['nombre_generico'] . ' ' . $devolucion['concentracion'] . '</td></tr>
            <tr><td><strong>Lote:</strong></td><td>' . $devolucion['numero_lote'] . '</td></tr>
            <tr><td><strong>Cantidad:</strong></td><td>' . $devolucion['cantidad'] . '</td></tr>
            <tr><td><strong>Motivo:</strong></td><td>' . $devolucion['motivo'] . '</td></tr>
            <tr><td><strong>Observaciones:</strong></td><td>' . ($devolucion['observaciones'] ?? '—') . '</td></tr>
            <tr><td><strong>Registrado por:</strong></td><td>' . $devolucion['usuario_nombre'] . ' ' . $devolucion['usuario_apellido'] . '</td></tr>
        </table>
    </div>
    <div class="footer">
        <p>Sistema SIGFA - Hospital Dr. Juan Daza Pereyra</p>
    </div>
</body>
</html>';

        // Si hay dompdf, generar PDF; si no, mostrar HTML
        if (class_exists('Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A5', 'portrait');
            $dompdf->render();
            $dompdf->stream($devolucion['numero_comprobante'] . '.pdf');
        } else {
            echo $html;
        }
    }

    /**
     * Anular devolución (solo Admin)
     */
    public function anular(): void
    {
        // Verificar que es Admin
        $rol = $_SESSION['usuario_rol'] ?? '';
        if ($rol !== 'Administrador') {
            $_SESSION['modulo_error'] = 'Solo el Administrador puede anular devoluciones.';
            header('Location: index.php?url=devoluciones');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=devoluciones');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');

        if ($id <= 0) {
            $_SESSION['modulo_error'] = 'ID inválido.';
            header('Location: index.php?url=devoluciones');
            exit;
        }
        if (empty($motivo)) {
            $_SESSION['modulo_error'] = 'El motivo de anulación es obligatorio.';
            header('Location: index.php?url=devoluciones');
            exit;
        }

        try {
            $this->modelo->anular($id, $_SESSION['usuario_id'], $motivo);
            $_SESSION['modulo_exito'] = '✅ Devolución anulada. Stock restaurado.';
        } catch (\RuntimeException $e) {
            $_SESSION['modulo_error'] = $e->getMessage();
        }

        header('Location: index.php?url=devoluciones');
        exit;
    }
}
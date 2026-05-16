<?php
require_once __DIR__ . '/../models/Transferencia.php';
require_once __DIR__ . '/../models/Medicamento.php';
require_once __DIR__ . '/../models/Inventario.php';

class TransferenciaController
{
    private $modelo;
    private $medicamento;
    private $inventario;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->modelo = new Transferencia();
        $this->medicamento = new Medicamento();
        $this->inventario = new Inventario();
    }

    private function verificarAcceso(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php');
            exit;
        }
        $rol = $_SESSION['usuario_rol'];
        $rolesPermitidos = ['Administrador', 'Almacenista', 'Auxiliar_Alto_Costo', 'Farmaceutico'];
        if (!in_array($rol, $rolesPermitidos)) {
            $_SESSION['modulo_error'] = 'No tiene acceso a este módulo.';
            header('Location: index.php?url=dashboard');
            exit;
        }
    }

    public function index(): void
    {
        $this->verificarAcceso();

        $filtros = [
            'fecha_desde' => $_GET['fecha_desde'] ?? null,
            'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
            'tipo' => $_GET['tipo'] ?? null,
            'estatus' => $_GET['estatus'] ?? null
        ];

        $transferencias = $this->modelo->listar($filtros);
        $almacenes = $this->modelo->listarAlmacenes();
        $servicios = $this->modelo->listarServicios();
        $medicamentos = $this->medicamento->listarConStock();

        $error = $_SESSION['modulo_error'] ?? null;
        $exito = $_SESSION['modulo_exito'] ?? null;
        $csrf_token = $_SESSION['csrf_token'] ?? '';
        unset($_SESSION['modulo_error'], $_SESSION['modulo_exito']);

        require_once __DIR__ . '/../views/transferencias/index.php';
    }

    public function crear(): void
    {
        $this->verificarAcceso();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=transferencias');
            exit;
        }

        try {
            $this->validarCSRF();

            $tipo = $_POST['tipo'] ?? '';
            if (!in_array($tipo, ['Almacen_Almacen', 'Almacen_Servicio'])) {
                throw new \RuntimeException('Tipo de transferencia inválido.');
            }

            $almacenOrigen = (int)($_POST['almacen_origen'] ?? 0);
            $almacenDestino = (int)($_POST['almacen_destino'] ?? 0);
            $servicioDestino = !empty($_POST['servicio_destino']) ? (int)$_POST['servicio_destino'] : null;

            if ($almacenOrigen <= 0) {
                throw new \RuntimeException('Seleccione un almacén de origen válido.');
            }

            if ($tipo === 'Almacen_Almacen' && $almacenDestino <= 0) {
                throw new \RuntimeException('Para transferencias entre almacenes, debe seleccionar un destino.');
            }

            if ($tipo === 'Almacen_Servicio' && ($servicioDestino ?? 0) <= 0) {
                throw new \RuntimeException('Para transferencias a servicio, debe seleccionar un servicio médico.');
            }

            if ($almacenOrigen === $almacenDestino) {
                throw new \RuntimeException('El almacén de origen y destino no pueden ser iguales.');
            }

            $medicamentosIds = $_POST['medicamento_id'] ?? [];
            $cantidades = $_POST['cantidad'] ?? [];
            $lotesIds = $_POST['lote_id'] ?? [];

            $detalles = [];
            for ($i = 0; $i < count($medicamentosIds); $i++) {
                $medId = (int)$medicamentosIds[$i];
                $cant = (int)$cantidades[$i];
                $loteId = (int)($lotesIds[$i] ?? 0);

                if ($medId > 0 && $cant > 0 && $loteId > 0) {
                    $detalles[] = [
                        'medicamento_id' => $medId,
                        'lote_id' => $loteId,
                        'cantidad' => $cant
                    ];
                }
            }

            if (empty($detalles)) {
                throw new \RuntimeException('Debe agregar al menos un medicamento.');
            }

            $resultado = $this->modelo->crear(
                [
                    'tipo' => $tipo,
                    'almacen_origen' => $almacenOrigen,
                    'almacen_destino' => $almacenDestino,
                    'servicio_destino' => $servicioDestino,
                    'observaciones' => $_POST['observaciones'] ?? ''
                ],
                $detalles,
                $_SESSION['usuario_id']
            );

            $_SESSION['modulo_exito'] = "✅ Transferencia registrada. Código: {$resultado['codigo']}";
        } catch (\Exception $e) {
            $_SESSION['modulo_error'] = $e->getMessage();
        }

        header('Location: index.php?url=transferencias');
        exit;
    }

    public function ajaxBuscarLotes(): void
    {
        $this->verificarAcceso();
        header('Content-Type: application/json; charset=utf-8');

        $medicamentoId = (int)($_GET['medicamento_id'] ?? 0);
        if ($medicamentoId <= 0) {
            echo json_encode(['error' => 'ID de medicamento inválido']);
            return;
        }

        $lotes = $this->inventario->listarLotesMedicamento($medicamentoId);
        echo json_encode(['lotes' => $lotes]);
    }

    public function anular(): void
    {
        $this->verificarAcceso();
        $rol = $_SESSION['usuario_rol'];
        
        if ($rol !== 'Administrador') {
            $_SESSION['modulo_error'] = 'Solo el Administrador puede anular transferencias.';
            header('Location: index.php?url=transferencias');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=transferencias');
            exit;
        }

        try {
            $this->validarCSRF();
            $id = (int)($_POST['transferencia_id'] ?? 0);
            $motivo = trim($_POST['motivo_anulacion'] ?? '');

            if ($id <= 0) throw new \RuntimeException('ID de transferencia inválido.');
            if (empty($motivo)) throw new \RuntimeException('El motivo de anulación es obligatorio.');

            $this->modelo->anularTransferencia($id, $_SESSION['usuario_id'], $motivo);
            $_SESSION['modulo_exito'] = '✅ Transferencia anulada exitosamente y stock revertido.';
        } catch (\Exception $e) {
            $_SESSION['modulo_error'] = '⚠️ ' . $e->getMessage();
        }

        header('Location: index.php?url=transferencias');
        exit;
    }

    private function validarCSRF(): void
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            throw new \RuntimeException('Token de seguridad inválido.');
        }
    }
}
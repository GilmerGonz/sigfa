<?php
/**
 * =====================================================
 * SIGFA - Controlador: Inventario
 * =====================================================
 * Gestiona la entrada de lotes, consulta de stock
 * y alertas de vencimiento/stock bajo.
 * =====================================================
 */

require_once __DIR__ . '/../models/Inventario.php';
require_once __DIR__ . '/../models/Medicamento.php';

class InventarioController
{
    private Inventario $inventario;
    private Medicamento $medicamento;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->inventario  = new Inventario();
        $this->medicamento = new Medicamento();
    }

    /**
     * Verificar autenticación.
     */
    private function verificarAutenticacion(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php');
            exit;
        }
    }

    /**
     * Mostrar formulario de entrada de lotes.
     */
    public function mostrarFormularioEntrada(): void
    {
        $this->verificarAutenticacion();

        $medicamentos = $this->medicamento->listarConStock();
        $error = $_SESSION['inventario_error'] ?? null;
        $exito = $_SESSION['inventario_exito'] ?? null;
        unset($_SESSION['inventario_error'], $_SESSION['inventario_exito']);

        require_once __DIR__ . '/../views/inventario/entrada.php';
    }

    /**
     * Procesar la entrada de un lote al inventario.
     */
    public function procesarEntrada(): void
    {
        $this->verificarAutenticacion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=inventario/entrada');
            exit;
        }

        try {
            // Validar CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                throw new \RuntimeException('Token de seguridad inválido.');
            }

            // Validar que se recibieron arrays de medicamentos
            $medicamentosIds = $_POST['medicamento_id'] ?? [];
            if (!is_array($medicamentosIds) || empty($medicamentosIds)) {
                throw new \RuntimeException('Debe incluir al menos un medicamento.');
            }

            $almacenId = !empty($_POST['almacen_id']) ? (int) $_POST['almacen_id'] : null;
            $proveedorId = !empty($_POST['proveedor_id']) ? (int) $_POST['proveedor_id'] : null;
            $numeroGuia = trim($_POST['numero_guia'] ?? '');
            $choferNombre = trim($_POST['chofer_nombre'] ?? '');
            $choferCedula = trim($_POST['chofer_cedula'] ?? '');
            $choferTelefono = trim($_POST['chofer_telefono'] ?? '');
            $choferCorreo = trim($_POST['chofer_correo'] ?? '');
            $placaVehiculo = trim($_POST['placa_vehiculo'] ?? '');
            $observaciones = trim($_POST['observaciones'] ?? '');
            
            $lotesGenerados = [];

            // Iniciar transacción (opcional si la DB soportara, pero cada uno tiene su transacción interna o hacemos bucle)
            foreach ($medicamentosIds as $index => $medicamentoId) {
                $medicamentoId = (int) $medicamentoId;
                $numeroLote = trim($_POST['numero_lote'][$index] ?? '');
                $fechaVencimiento = trim($_POST['fecha_vencimiento'][$index] ?? '');
                $cantidad = (int) ($_POST['cantidad'][$index] ?? 0);
                $fechaFabricacion = !empty($_POST['fecha_fabricacion'][$index]) ? $_POST['fecha_fabricacion'][$index] : null;
                $precioUnitario = !empty($_POST['precio_unitario'][$index]) ? (float) $_POST['precio_unitario'][$index] : 0;

                if ($medicamentoId <= 0) throw new \RuntimeException("Fila " . ($index+1) . ": Seleccione un medicamento válido.");
                if (empty($numeroLote)) throw new \RuntimeException("Fila " . ($index+1) . ": El número de lote es obligatorio.");
                if (empty($fechaVencimiento)) throw new \RuntimeException("Fila " . ($index+1) . ": La fecha de vencimiento es obligatoria.");
                if ($cantidad <= 0) throw new \RuntimeException("Fila " . ($index+1) . ": La cantidad debe ser mayor a cero.");

                if (strtotime($fechaVencimiento) <= time()) {
                    throw new \RuntimeException("Fila " . ($index+1) . ": La fecha de vencimiento debe ser una fecha futura.");
                }

                $loteId = $this->inventario->registrarEntrada([
                    'medicamento_id'   => $medicamentoId,
                    'almacen_id'      => $almacenId,
                    'proveedor_id'     => $proveedorId,
                    'numero_lote'      => $numeroLote,
                    'fecha_fabricacion' => $fechaFabricacion,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'cantidad'         => $cantidad,
                    'precio_unitario'  => $precioUnitario,
                    'numero_guia'      => $numeroGuia,
                    'chofer_nombre'    => $choferNombre,
                    'chofer_cedula'    => $choferCedula,
                    'chofer_telefono'  => $choferTelefono,
                    'chofer_correo'    => $choferCorreo,
                    'placa_vehiculo'   => $placaVehiculo,
                    'observaciones'    => $observaciones,
                ], $_SESSION['usuario_id']);
                
                $lotesGenerados[] = $loteId;
            }

            $_SESSION['inventario_exito'] = "✅ " . count($lotesGenerados) . " lote(s) registrado(s) exitosamente.";
            header('Location: index.php?url=inventario/entrada');
            exit;

        } catch (\RuntimeException $e) {
            $_SESSION['inventario_error'] = $e->getMessage();
            header('Location: index.php?url=inventario/entrada');
            exit;
        }
    }

    /**
     * Mostrar alertas de vencimiento.
     */
    public function mostrarAlertas(): void
    {
        $this->verificarAutenticacion();

        $lotesPorVencer = $this->inventario->obtenerLotesPorVencer(30);
        $stockBajo      = $this->medicamento->obtenerStockBajo();

        require_once __DIR__ . '/../views/inventario/alertas.php';
    }

    /**
     * Mostrar Kardex de un medicamento.
     */
    public function mostrarKardex(): void
    {
        $this->verificarAutenticacion();

        $medicamentoId = (int) ($_GET['medicamento_id'] ?? 0);
        $medicamento = null;
        $kardex = [];
        $lotes = [];

        if ($medicamentoId > 0) {
            $medicamento = $this->medicamento->buscarPorId($medicamentoId);
            $kardex = $this->inventario->obtenerKardexMedicamento($medicamentoId);
            $lotes  = $this->inventario->listarLotesMedicamento($medicamentoId);
        }

        $medicamentos = $this->medicamento->listarConStock();

        require_once __DIR__ . '/../views/inventario/kardex.php';
    }

    /**
     * Mostrar formulario de ajuste manual de inventario (Solo Admin).
     */
    public function mostrarAjuste(): void
    {
        $this->verificarAutenticacion();
        $rol = $_SESSION['usuario_rol'] ?? '';

        if ($rol !== 'Administrador') {
            $_SESSION['inventario_error'] = 'Acceso denegado: Solo el Administrador puede realizar ajustes manuales de inventario.';
            header('Location: index.php?url=dashboard');
            exit;
        }

        $medicamentoId = (int) ($_GET['medicamento_id'] ?? 0);
        $medicamento = null;
        $lotes = [];

        if ($medicamentoId > 0) {
            $medicamento = $this->medicamento->buscarPorId($medicamentoId);
            $lotes = $this->inventario->listarLotesMedicamento($medicamentoId);
        }

        $medicamentos = $this->medicamento->listarTodos();
        $error = $_SESSION['inventario_error'] ?? null;
        $exito = $_SESSION['inventario_exito'] ?? null;
        unset($_SESSION['inventario_error'], $_SESSION['inventario_exito']);

        require_once __DIR__ . '/../views/inventario/ajuste.php';
    }

    // =====================================================
    // CORRECCIONES ADMINISTRATIVAS (Solo Administrador)
    // =====================================================

    /**
     * Ajustar stock de un lote (conteo físico).
     */
    public function ajustarLote(): void
    {
        $this->verificarAutenticacion();
        $rol = $_SESSION['usuario_rol'] ?? '';

        if ($rol === 'Farmaceutico') {
            $_SESSION['inventario_error'] = 'No tienes permiso para modificar inventario manualmente. Usa entrada de lotes o transferencias';
            header('Location: index.php?url=inventario/kardex');
            exit;
        }

        if ($rol !== 'Administrador') {
            $_SESSION['inventario_error'] = 'Acceso denegado: Solo el Administrador puede ajustar inventario.';
            header('Location: index.php?url=inventario/kardex');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=inventario/kardex');
            exit;
        }

        try {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                throw new \RuntimeException('Token de seguridad inválido.');
            }

            $loteId        = (int) ($_POST['lote_id'] ?? 0);
            $nuevaCantidad = (int) ($_POST['nueva_cantidad'] ?? -1);
            $nuevaFecha    = trim($_POST['nueva_fecha_vencimiento'] ?? '');
            $motivo        = trim($_POST['motivo'] ?? '');

            if ($loteId <= 0) throw new \RuntimeException('ID de lote inválido.');
            if ($nuevaCantidad < 0) throw new \RuntimeException('La cantidad debe ser 0 o mayor.');
            if (empty($motivo)) throw new \RuntimeException('El motivo del ajuste es obligatorio.');

            $this->inventario->ajustarStock($loteId, $nuevaCantidad, $nuevaFecha, $_SESSION['usuario_id'], $motivo);

            // Obtener medicamento_id para redirigir al Kardex correcto
            $lote = $this->inventario->obtenerLotePorId($loteId);
            $medId = $lote ? $lote['medicamento_id'] : 0;

            $_SESSION['inventario_exito'] = '✅ Stock ajustado exitosamente. Movimiento registrado en Kardex.';
            header("Location: index.php?url=inventario/kardex&medicamento_id=$medId");
            exit;

        } catch (\RuntimeException $e) {
            $_SESSION['inventario_error'] = '⚠️ ' . $e->getMessage();
            header('Location: index.php?url=inventario/kardex');
            exit;
        }
    }

    /**
     * Anular una entrada errónea (sin despachos asociados).
     */
    public function anularEntrada(): void
    {
        $this->verificarAutenticacion();
        $rol = $_SESSION['usuario_rol'] ?? '';

        if ($rol === 'Farmaceutico') {
            // Anular transacciones error
            $_SESSION['inventario_error'] = 'Esta acción es exclusiva del Administrador';
            header('Location: index.php?url=inventario/kardex');
            exit;
        }

        if ($rol !== 'Administrador') {
            $_SESSION['inventario_error'] = 'Acceso denegado: Solo el Administrador puede eliminar lotes.';
            header('Location: index.php?url=inventario/kardex');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=inventario/kardex');
            exit;
        }

        try {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                throw new \RuntimeException('Token de seguridad inválido.');
            }

            $loteId = (int) ($_POST['lote_id'] ?? 0);
            $motivo = trim($_POST['motivo'] ?? '');

            if ($loteId <= 0) throw new \RuntimeException('ID de lote inválido.');
            if (empty($motivo)) throw new \RuntimeException('El motivo de la eliminación es obligatorio.');

            // Obtener medicamento_id antes de eliminar
            $lote = $this->inventario->obtenerLotePorId($loteId);
            $medId = $lote ? $lote['medicamento_id'] : 0;

            $this->inventario->eliminarLote($loteId, $_SESSION['usuario_id'], $motivo);

            $_SESSION['inventario_exito'] = '✅ Entrada de inventario (lote) anulada exitosamente.';
            header("Location: index.php?url=inventario/kardex&medicamento_id=$medId");
            exit;

        } catch (\RuntimeException $e) {
            $_SESSION['inventario_error'] = '⚠️ ' . $e->getMessage();
            header('Location: index.php?url=inventario/kardex');
            exit;
        }
    }
}


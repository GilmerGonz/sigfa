<?php
/**
 * =====================================================
 * SIGFA - Controlador: Dashboard
 * =====================================================
 * Gestiona la vista principal del panel de control
 * con métricas en tiempo real.
 * =====================================================
 */

require_once __DIR__ . '/../models/Medicamento.php';
require_once __DIR__ . '/../models/Inventario.php';
require_once __DIR__ . '/../models/Despacho.php';
require_once __DIR__ . '/../models/Asegurado.php';

class DashboardController
{
    private Medicamento $medicamento;
    private Inventario $inventario;
    private Despacho $despacho;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->medicamento = new Medicamento();
        $this->inventario  = new Inventario();
        $this->despacho    = new Despacho();
    }

    /**
     * Obtener todas las métricas del dashboard.
     */
    public function obtenerMetricas(): array
    {
        return [
            'total_medicamentos'    => $this->medicamento->contarActivos(),
            'despachos_hoy'         => $this->despacho->contarDespachosHoy(),
            'alertas_vencimiento'   => $this->inventario->contarAlertasVencimiento(),
            'lotes_stock_bajo'      => $this->inventario->contarLotesStockBajo(),
            'lotes_por_vencer'      => $this->inventario->obtenerLotesPorVencer(30),
            'medicamentos_stock_bajo' => $this->medicamento->obtenerStockBajo(),
            'despachos_recientes'   => $this->despacho->listarDespachosHoy(),
        ];
    }

    /**
     * Mostrar el dashboard con datos actualizados.
     */
    public function mostrar(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php');
            exit;
        }

        $metricas = $this->obtenerMetricas();
        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}

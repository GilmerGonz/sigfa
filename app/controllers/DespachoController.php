<?php
/**
 * =====================================================
 * SIGFA - Controlador: Despacho
 * =====================================================
 * Gestiona la creación, consulta y anulación de despachos.
 * Incluye validaciones de duplicidad y FIFO.
 * =====================================================
 */

require_once __DIR__ . '/../models/Despacho.php';
require_once __DIR__ . '/../models/Asegurado.php';
require_once __DIR__ . '/../models/Medico.php';
require_once __DIR__ . '/../models/Medicamento.php';

class DespachoController
{
    private Despacho $despacho;
    private Asegurado $asegurado;
    private Medico $medico;
    private Medicamento $medicamento;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->despacho    = new Despacho();
        $this->asegurado   = new Asegurado();
        $this->medico      = new Medico();
        $this->medicamento = new Medicamento();
    }

    /**
     * Verificar que el usuario esté autenticado.
     */
    private function verificarAutenticacion(): void
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php');
            exit;
        }
    }

    private function verificarAcceso(array $rolesPermitidos = []): void
    {
        $this->verificarAutenticacion();
        $rolUsuario = $_SESSION['usuario_rol'] ?? '';

        if ($rolUsuario === 'Almacenista' && in_array('Almacenista', $rolesPermitidos) === false) {
            $_SESSION['despacho_error'] = 'El Almacenista no tiene permisos para despachar medicamentos...';
            header('Location: index.php?url=dashboard');
            exit;
        }

        if (!empty($rolesPermitidos)) {
            if (!in_array($rolUsuario, $rolesPermitidos, true)) {
                $_SESSION['despacho_error'] = 'Acceso denegado. Su rol no tiene permisos para realizar esta acción.';
                header('Location: index.php?url=dashboard');
                exit;
            }
        }
    }

    /**
     * Mostrar formulario de nuevo despacho.
     */
    public function mostrarFormulario(): void
    {
        $this->verificarAcceso(['Administrador', 'Auxiliar_General', 'Auxiliar_Alto_Costo', 'Farmaceutico']);

        $medicamentos = $this->medicamento->listarConStock();
        $medicos      = $this->medico->listarActivos();
        $error         = $_SESSION['despacho_error'] ?? null;
        $exito         = $_SESSION['despacho_exito'] ?? null;
        unset($_SESSION['despacho_error'], $_SESSION['despacho_exito']);

        require_once __DIR__ . '/../views/despachos/nuevo.php';
    }

    /**
     * Procesar la creación de un nuevo despacho.
     */
    public function procesarDespacho(): void
    {
        $this->verificarAcceso(['Administrador', 'Auxiliar_General', 'Auxiliar_Alto_Costo', 'Farmaceutico']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?url=despachos/nuevo');
            exit;
        }

        try {
            // Validar CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                throw new \RuntimeException('Token de seguridad inválido.');
            }

            $aseguradoId = (int) ($_POST['asegurado_id'] ?? 0);
            $medicoId    = (int) ($_POST['medico_id'] ?? 0);
            $diagnostico = trim($_POST['diagnostico'] ?? '');

            if ($aseguradoId <= 0) {
                // Bloque 4.1 Redirección automática si paciente NO existe
                header('Location: index.php?url=asegurados/crear&msg=paciente_no_existe');
                exit;
            }

            // Verificar que el paciente existe
            $paciente = $this->asegurado->buscarPorId($aseguradoId);
            if (!$paciente) {
                header('Location: index.php?url=asegurados/crear&msg=paciente_no_existe');
                exit;
            }

            // Verificar ALERTAS
            if (!empty($paciente['alerta_identidad'])) {
                $_SESSION['alerta_identidad'] = $paciente['alerta_identidad'];
            }
            if (!empty($paciente['alerta_edad_partida'])) {
                $_SESSION['alerta_edad_partida'] = $paciente['alerta_edad_partida'];
            }

            // Preparar detalles del despacho
            $detalles = [];
            $medicamentosIds    = $_POST['medicamento_id'] ?? [];
            $cantidades         = $_POST['cantidad'] ?? [];
            $cantidadesRecetada = $_POST['cantidad_recetada'] ?? [];
            $ciclosAsignados    = $_POST['ciclo_asignado'] ?? [];
            $fechasProximas     = $_POST['fecha_proxima_manual'] ?? [];
            $confirmarEmergencia = $_POST['confirmar_emergencia'] ?? [];

            $rolUsuario = $_SESSION['usuario_rol'] ?? '';
            
            for ($i = 0; $i < count($medicamentosIds); $i++) {
                $medId = (int) ($medicamentosIds[$i] ?? 0);
                $cant  = (int) ($cantidades[$i] ?? 0);

                if ($medId > 0 && $cant > 0) {
                    $medInfo = $this->medicamento->buscarPorId($medId);
                    if ($medInfo && $rolUsuario === 'Auxiliar_General') {
                        $grupo = $medInfo['grupo_codigo'] ?? '';
                        if (in_array($grupo, ['003', '004', '005'])) {
                            throw new \RuntimeException("Acceso denegado: este medicamento solo puede ser despachado por Auxiliar de Alto Costo o Farmacéutico");
                        }
                    }

                    $detalles[] = [
                        'medicamento_id'      => $medId,
                        'cantidad'            => $cant,
                        'cantidad_recetada'   => (int) ($cantidadesRecetada[$i] ?? $cant),
                        'ciclo_asignado'      => (!empty($ciclosAsignados[$i]) && $ciclosAsignados[$i] !== 'custom') ? (int) $ciclosAsignados[$i] : null,
                        'fecha_proxima'       => (!empty($fechasProximas[$i]) && $ciclosAsignados[$i] === 'custom') ? $fechasProximas[$i] : null,
                        'confirmar_emergencia' => !empty($confirmarEmergencia[$i]),
                    ];
                }
            }

            if (empty($detalles)) {
                throw new \RuntimeException('Debe agregar al menos un medicamento al despacho.');
            }

            $resultado = $this->despacho->crearDespacho(
                [
                    'asegurado_id' => $aseguradoId,
                    'medico_id'    => $medicoId > 0 ? $medicoId : null,
                    'servicio_id'  => !empty($_POST['servicio_id']) ? (int) $_POST['servicio_id'] : null,
                    'patologia_id' => !empty($_POST['patologia_id']) ? (int) $_POST['patologia_id'] : null,
                    'diagnostico'  => $diagnostico,
                    'observaciones' => trim($_POST['observaciones'] ?? ''),
                ],
                $detalles,
                $_SESSION['usuario_id']
            );

            $msg = "✅ Despacho registrado exitosamente. Ticket: {$resultado['ticket']}";
            if (!empty($resultado['items_bloqueados'])) {
                $nombres = array_map(fn($b) => $b['nombre'], $resultado['items_bloqueados']);
                $msg .= " | ⚠️ Ítems no procesados (bloqueados): " . implode(', ', $nombres);
            }
            $_SESSION['despacho_exito'] = $msg;
            header('Location: index.php?url=despachos/nuevo');
            exit;

        } catch (\RuntimeException $e) {
            $_SESSION['despacho_error'] = $e->getMessage();
            header('Location: index.php?url=despachos/nuevo');
            exit;
        }
    }

    /**
     * Buscar paciente por cédula, historia médica o partida (para AJAX).
     */
    public function buscarPacienteAjax(): void
    {
        $this->verificarAutenticacion();
        header('Content-Type: application/json; charset=utf-8');

        $identificador = trim($_GET['cedula'] ?? $_GET['identificador'] ?? '');
        if (empty($identificador)) {
            echo json_encode(['error' => 'Identificador requerido (cédula, historia médica o partida)']);
            return;
        }

        // Buscar por cualquier identificador
        $paciente = $this->asegurado->buscarPorIdentificador($identificador);
        if (!$paciente) {
            echo json_encode(['error' => 'Paciente no encontrado']);
            return;
        }

        // Remover campos sensibles
        unset($paciente['direccion']);

        echo json_encode(['paciente' => $paciente]);
    }

    /**
     * Verificar duplicidad por ítem específico (para AJAX).
     */
    public function verificarDuplicidadItemAjax(): void
    {
        $this->verificarAutenticacion();
        header('Content-Type: application/json; charset=utf-8');

        $aseguradoId = (int) ($_GET['asegurado_id'] ?? 0);
        $medicamentoId = (int) ($_GET['medicamento_id'] ?? 0);

        if ($aseguradoId <= 0 || $medicamentoId <= 0) {
            echo json_encode(['error' => 'Parámetros inválidos']);
            return;
        }

        $resultado = $this->despacho->verificarDuplicidadPorItem($aseguradoId, $medicamentoId);
        echo json_encode($resultado);
    }




    /**
     * Verificar recurrencia/bloqueo de dosis (para AJAX).
     */
    public function verificarCicloDosisAjax(): void
    {
        $this->verificarAutenticacion();
        header('Content-Type: application/json; charset=utf-8');

        $aseguradoId = (int) ($_GET['asegurado_id'] ?? 0);
        $medicamentoId = (int) ($_GET['medicamento_id'] ?? 0);

        if ($aseguradoId <= 0 || $medicamentoId <= 0) {
            echo json_encode(['error' => 'Parámetros inválidos']);
            return;
        }

        $resultado = $this->despacho->verificarRecurrencia($aseguradoId, $medicamentoId);
        echo json_encode($resultado);
    }

    /**
     * Listar despachos del día.
     */
    public function listarHoy(): void
    {
        $this->verificarAcceso(['Administrador', 'Auxiliar_General', 'Auxiliar_Alto_Costo', 'Farmaceutico', 'Kardista']);

        $despachos = $this->despacho->listarDespachosHoy();
        $medicamentos = $this->medicamento->listarConStock();
        $medicos      = $this->medico->listarActivos();
        
        $error = $_SESSION['despacho_error'] ?? null;
        $exito = $_SESSION['despacho_exito'] ?? null;
        unset($_SESSION['despacho_error'], $_SESSION['despacho_exito']);

        require_once __DIR__ . '/../views/despachos/index.php';
    }

    /**
     * Anular un despacho (Solo Administrador).
     * Revierte stock y registra auditoría completa.
     */
    public function anularDespacho(): void
    {
        $this->verificarAutenticacion();
        $rolUsuario = $_SESSION['usuario_rol'] ?? '';

        if ($rolUsuario !== 'Administrador') {
            $_SESSION['despacho_error'] = 'La anulación de transacciones solo está permitida para el Administrador por razones de auditoría.';
            header('Location: index.php?url=despachos');
            exit;
        }

        // Si es GET, mostrar el historial de anulaciones
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require_once __DIR__ . '/../models/Reporte.php';
            $reporteModel = new Reporte();
            
            $filtros = [
                'fecha_desde' => $_GET['fecha_desde'] ?? null,
                'fecha_hasta' => $_GET['fecha_hasta'] ?? null
            ];
            
            $anulaciones = $reporteModel->listarAnulaciones($filtros);
            $paginaActual = 'anular';
            $tituloPagina = 'Anulación de Transacciones';
            
            require_once __DIR__ . '/../views/despachos/anulaciones.php';
            exit;
        }

        // Si es POST, procesar la anulación (Código existente con mejoras)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validar CSRF
                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                    throw new \RuntimeException('Token de seguridad inválido.');
                }

                $despachoId = (int) ($_POST['despacho_id'] ?? 0);
                $motivo     = trim($_POST['motivo_anulacion'] ?? '');

                if ($despachoId <= 0) {
                    throw new \RuntimeException('ID de despacho inválido.');
                }
                if (empty($motivo)) {
                    throw new \RuntimeException('Debe proporcionar un motivo para la anulación.');
                }

                $this->despacho->anularDespacho($despachoId, $_SESSION['usuario_id'], $motivo);
                $_SESSION['despacho_exito'] = '✅ Despacho anulado exitosamente. El stock ha sido revertido.';

            } catch (\RuntimeException $e) {
                $_SESSION['despacho_error'] = '⚠️ ' . $e->getMessage();
            }

            header('Location: index.php?url=despachos');
            exit;
        }
    }
}


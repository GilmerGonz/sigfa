<?php
require_once __DIR__ . '/../models/Reporte.php';

class ReporteController
{
    private $modelo;
    private const MEMBRETE_TITULO = 'SISTEMA DE GESTIÓN FARMACÉUTICA – SIGFA';
    private const MEMBRETE_HOSPITAL = 'HOSPITAL GENERAL MUNICIPAL “DR. JUAN DAZA PEREYRA”';

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: index.php');
            exit;
        }
        $this->modelo = new Reporte();
    }

    private function generarMembrete(string $titulo): string
    {
        $fecha = date('d/m/Y H:i:s');
        return "<div style='text-align:center;margin-bottom:20px;'>
            <h2 style='margin:0;'>" . self::MEMBRETE_TITULO . "</h2>
            <h3 style='margin:5px 0;'>" . self::MEMBRETE_HOSPITAL . "</h3>
            <h4 style='margin:5px 0;'>{$titulo}</h4>
            <small style='color:#666;'>Fecha de generación: {$fecha}</small>
        </div>";
    }

    public function exportarPDF(string $reporte, array $datos, array $headers, string $titulo): void
    {
        $this->registrarDescarga('PDF', $reporte);
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: "Helvetica", "Arial", sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2c5282; padding-bottom: 10px; }
                .header h1 { margin: 0; font-size: 18px; color: #1a365d; }
                .header h2 { margin: 5px 0; font-size: 14px; color: #2c5282; }
                .header h3 { margin: 5px 0; font-size: 16px; text-transform: uppercase; }
                .header p { margin: 0; font-size: 10px; color: #666; }
                
                table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: auto; }
                th { background-color: #2c5282; color: #ffffff; padding: 8px 5px; text-align: left; font-weight: bold; border: 1px solid #1a365d; }
                td { padding: 6px 5px; border: 1px solid #e2e8f0; vertical-align: top; }
                tr:nth-child(even) { background-color: #f7fafc; }
                
                .footer { position: fixed; bottom: -30px; left: 0px; right: 0px; height: 30px; text-align: right; font-size: 9px; color: #aaa; }
                .page-number:after { content: counter(page); }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>SISTEMA DE GESTIÓN FARMACÉUTICA – SIGFA</h1>
                <h2>HOSPITAL GENERAL MUNICIPAL DE JUAN DAZA PEREYRA</h2>
                <h3>' . $titulo . '</h3>
                <p>Generado el: ' . date('d/m/Y H:i:s') . ' | Usuario: ' . ($_SESSION['usuario_nombre'] ?? 'Administrador') . '</p>
            </div>
            
            <table>
                <thead>
                    <tr>';
        foreach ($headers as $h) {
            $html .= '<th>' . htmlspecialchars($h) . '</th>';
        }
        $html .= '  </tr>
                </thead>
                <tbody>';
        
        foreach ($datos as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars((string)$cell) . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '
                </tbody>
            </table>
            
            <div class="footer">
                Página <span class="page-number"></span> | SIGFA - Reporte de Auditoría
            </div>
        </body>
        </html>';

        try {
            $dompdf = new \Dompdf\Dompdf([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Helvetica'
            ]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $reporte . '_' . date('Ymd_His') . '.pdf"');
            echo $dompdf->output();
            exit;
        } catch (\Exception $e) {
            die("Error generando PDF: " . $e->getMessage());
        }
    }

    public function exportarExcel(string $reporte, array $datos, array $headers, string $titulo): void
    {
        $this->registrarDescarga('Excel', $reporte);
        
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $reporte . '_' . date('Ymd_His') . '.xls"');
        
        // Estructura XML de Excel con Estilos
        echo '<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:microsoft:office"
 xmlns:x="urn:schemas-microsoft-com:office:microsoft:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Arial" x:Family="Swiss"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="sHeader">
   <Font ss:FontName="Arial" x:Family="Swiss" ss:Size="14" ss:Bold="1"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
  </Style>
  <Style ss:ID="sTitle">
   <Font ss:FontName="Arial" x:Family="Swiss" ss:Size="12" ss:Bold="1" ss:Color="#2C5282"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
  </Style>
  <Style ss:ID="sTableHeader">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Arial" x:Family="Swiss" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#2C5282" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="sCell">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
 </Styles>
 <Worksheet ss:Name="Reporte">
  <Table>';
        
        // Membrete
        echo '<Row ss:Height="20"><Cell ss:MergeAcross="' . (count($headers)-1) . '" ss:StyleID="sHeader"><Data ss:Type="String">SISTEMA DE GESTIÓN FARMACÉUTICA – SIGFA</Data></Cell></Row>';
        echo '<Row ss:Height="18"><Cell ss:MergeAcross="' . (count($headers)-1) . '" ss:StyleID="sHeader"><Data ss:Type="String">HOSPITAL GENERAL MUNICIPAL DE JUAN DAZA PEREYRA</Data></Cell></Row>';
        echo '<Row ss:Height="16"><Cell ss:MergeAcross="' . (count($headers)-1) . '" ss:StyleID="sTitle"><Data ss:Type="String">' . htmlspecialchars($titulo) . '</Data></Cell></Row>';
        echo '<Row><Cell ss:MergeAcross="' . (count($headers)-1) . '" ss:StyleID="Default"><Data ss:Type="String">Fecha: ' . date('d/m/Y H:i:s') . '</Data></Cell></Row>';
        echo '<Row></Row>';
        
        // Encabezados
        echo '<Row>';
        foreach ($headers as $h) {
            echo '<Cell ss:StyleID="sTableHeader"><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
        }
        echo '</Row>';
        
        // Datos
        foreach ($datos as $row) {
            echo '<Row>';
            foreach ($row as $cell) {
                $val = htmlspecialchars((string)($cell ?? ''));
                $type = is_numeric($cell) ? 'Number' : 'String';
                echo '<Cell ss:StyleID="sCell"><Data ss:Type="' . $type . '">' . $val . '</Data></Cell>';
            }
            echo '</Row>';
        }
        
        echo '  </Table>
 </Worksheet>
</Workbook>';
        exit;
    }

    private function registrarDescarga(string $formato, string $reporte): void
    {
        try {
            $pdo = \Conexion::obtenerInstancia()->obtenerPDO();
            $sql = "INSERT INTO logs_descargas (reporte, formato, usuario_id, fecha_descarga) VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$reporte, $formato, $_SESSION['usuario_id']]);
        } catch (\Exception $e) {}
    }

    public function recetasDiarias(): void
    {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $datos = $this->modelo->recetasDiarias($fecha);
        
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'pdf') {
            $this->exportarPDF('recetas_diarias', $datos, ['Ticket', 'Paciente', 'Cédula', 'Medicamento', 'Cantidad', 'Servicio', 'Estatus'], 'Recetas Diarias - ' . $fecha);
            return;
        }
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
            $this->exportarExcel('recetas_diarias', $datos, ['Ticket', 'Paciente', 'Cédula', 'Medicamento', 'Cantidad', 'Servicio', 'Estatus'], 'Recetas Diarias');
            return;
        }
        
        $tituloPagina = 'Recetas Diarias';
        require_once __DIR__ . '/../views/reportes/recetas_diarias.php';
    }

    public function consumoMasivo(): void
    {
        $filtros = [
            'fecha_desde' => $_GET['fecha_desde'] ?? date('Y-m-01'),
            'fecha_hasta' => $_GET['fecha_hasta'] ?? date('Y-m-d'),
            'grupo_id' => $_GET['grupo_id'] ?? null
        ];
        $datos = $this->modelo->consumoMasivo($filtros);
        $grupos = $this->modelo->obtenerGrupos();
        
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'pdf') {
            $this->exportarPDF('consumo_masivo', $datos, ['Código', 'Medicamento', 'Presentación', 'Cant. Despachada', 'Cant. Recetas', 'Stock Actual'], 'Reporte de Consumo Masivo');
            return;
        }
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
            $this->exportarExcel('consumo_masivo', $datos, ['Código', 'Medicamento', 'Presentación', 'Cant. Despachada', 'Cant. Recetas', 'Stock Actual'], 'Consumo Masivo');
            return;
        }
        
        $tituloPagina = 'Consumo Masivo';
        require_once __DIR__ . '/../views/reportes/consumo_masivo.php';
    }

    public function costoPromedio(): void
    {
        $filtros = [
            'fecha_desde' => $_GET['fecha_desde'] ?? date('Y-m-01'),
            'fecha_hasta' => $_GET['fecha_hasta'] ?? date('Y-m-d'),
            'grupo_id' => $_GET['grupo_id'] ?? null
        ];
        $datos = $this->modelo->costoPromedio($filtros);
        $grupos = $this->modelo->obtenerGrupos();
        
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'pdf') {
            $this->exportarPDF('costo_promedio', $datos, ['Medicamento', 'Concentración', 'Costo Prom.', 'Stock', 'Valor Total'], 'Reporte Costo Promedio');
            return;
        }
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
            $this->exportarExcel('costo_promedio', $datos, ['Medicamento', 'Concentración', 'Costo Prom.', 'Stock', 'Valor Total'], 'Costo Promedio');
            return;
        }
        
        $tituloPagina = 'Costo Promedio';
        require_once __DIR__ . '/../views/reportes/costo_promedio.php';
    }

    public function porServicio(): void
    {
        $filtros = [
            'fecha_desde' => $_GET['fecha_desde'] ?? date('Y-m-01'),
            'fecha_hasta' => $_GET['fecha_hasta'] ?? date('Y-m-d'),
            'servicio_id' => $_GET['servicio_id'] ?? null
        ];
        $datos = $this->modelo->porServicio($filtros);
        $servicios = $this->modelo->obtenerServicios();
        
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'pdf') {
            $this->exportarPDF('reporte_servicio', $datos, ['Servicio', 'Cant. Despachos', 'Cant. Meds', 'Monto (Bs)', 'Género', 'Grupo Etario'], 'Despachos por Servicio');
            return;
        }
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
            $this->exportarExcel('reporte_servicio', $datos, ['Servicio', 'Cant. Despachos', 'Cant. Meds', 'Monto (Bs)', 'Género', 'Grupo Etario'], 'Despachos por Servicio');
            return;
        }

        $tituloPagina = 'Reportes por Servicio';
        require_once __DIR__ . '/../views/reportes/servicio.php';
    }

    public function porMedicamento(): void
    {
        $filtros = [
            'fecha_desde' => $_GET['fecha_desde'] ?? date('Y-m-01'),
            'fecha_hasta' => $_GET['fecha_hasta'] ?? date('Y-m-d'),
            'medicamento_id' => $_GET['medicamento_id'] ?? null
        ];
        $datos = $this->modelo->porMedicamento($filtros);
        $medicamentos = $this->modelo->obtenerMedicamentos();

        if (isset($_GET['exportar']) && $_GET['exportar'] === 'pdf') {
            $this->exportarPDF('reporte_medicamento', $datos, ['Código', 'Medicamento', 'Concentración', 'Servicio', 'Cant. Despachada'], 'Movimientos por Medicamento');
            return;
        }
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
            $this->exportarExcel('reporte_medicamento', $datos, ['Código', 'Medicamento', 'Concentración', 'Servicio', 'Cant. Despachada'], 'Movimientos por Medicamento');
            return;
        }
        
        $tituloPagina = 'Reportes por Medicamento';
        require_once __DIR__ . '/../views/reportes/medicamento.php';
    }

    public function porPeriodo(): void
    {
        $filtros = [
            'fecha_desde' => $_GET['fecha_desde'] ?? date('Y-m-01'),
            'fecha_hasta' => $_GET['fecha_hasta'] ?? date('Y-m-d'),
            'paciente_id' => $_GET['paciente_id'] ?? null,
            'medicamento_id' => $_GET['medicamento_id'] ?? null
        ];
        $datos = $this->modelo->porPeriodo($filtros);
        $medicamentos = $this->modelo->obtenerMedicamentos();

        if (isset($_GET['exportar']) && $_GET['exportar'] === 'pdf') {
            $this->exportarPDF('reporte_periodo', $datos, ['Ticket', 'Fecha', 'Paciente', 'Cédula', 'Medicamento', 'Cantidad'], 'Movimientos por Período');
            return;
        }
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
            $this->exportarExcel('reporte_periodo', $datos, ['Ticket', 'Fecha', 'Paciente', 'Cédula', 'Medicamento', 'Cantidad'], 'Movimientos por Período');
            return;
        }
        
        $tituloPagina = 'Reportes por Período';
        require_once __DIR__ . '/../views/reportes/periodo.php';
    }

    public function consumo(): void
    {
        $filtros = [
            'fecha_desde' => $_GET['fecha_desde'] ?? date('Y-m-01'),
            'fecha_hasta' => $_GET['fecha_hasta'] ?? date('Y-m-d')
        ];
        $datos = $this->modelo->consumoBolivares($filtros);

        if (isset($_GET['exportar']) && $_GET['exportar'] === 'pdf') {
            $this->exportarPDF('consumo_bolivares', $datos, ['Fecha', 'Servicio', 'Despachos', 'Medicamentos', 'Monto (Bs)'], 'Consumo en Bolívares');
            return;
        }
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
            $this->exportarExcel('consumo_bolivares', $datos, ['Fecha', 'Servicio', 'Despachos', 'Medicamentos', 'Monto (Bs)'], 'Consumo en Bolívares');
            return;
        }
        
        $tituloPagina = 'Consumo en Bolivares';
        require_once __DIR__ . '/../views/reportes/consumo.php';
    }

    public function inventario(): void
    {
        $datos = $this->modelo->inventarioValorizado();

        if (isset($_GET['exportar']) && $_GET['exportar'] === 'pdf') {
            $this->exportarPDF('inventario_valorizado', $datos, ['Código', 'Medicamento', 'Concentración', 'Tipo', 'Stock', 'Precio (Bs)', 'Valor Total (Bs)'], 'Inventario Valorizado');
            return;
        }
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
            $this->exportarExcel('inventario_valorizado', $datos, ['Código', 'Medicamento', 'Concentración', 'Tipo', 'Stock', 'Precio (Bs)', 'Valor Total (Bs)'], 'Inventario Valorizado');
            return;
        }
        
        $tituloPagina = 'Inventario Valorizado';
        require_once __DIR__ . '/../views/reportes/inventario.php';
    }

    public function kardex(): void
    {
        $filtros = [
            'medicamento_id' => (int)($_GET['medicamento_id'] ?? 0)
        ];
        
        $medicamentoId = $filtros['medicamento_id'];
        $medicamentos = $this->modelo->obtenerMedicamentos();
        $datos = $medicamentoId > 0 ? $this->modelo->kardexCompleto($medicamentoId) : [];

        if ($medicamentoId > 0 && isset($_GET['exportar'])) {
            $mInfo = $this->modelo->buscarMedicamento($medicamentoId);
            $nombreMed = $mInfo ? $mInfo['nombre_generico'] : 'Medicamento';
            
            if ($_GET['exportar'] === 'pdf') {
                $this->exportarPDF('kardex_reporte', $datos, ['Fecha', 'Movimiento', 'Cant.', 'Anterior', 'Posterior', 'Motivo', 'Usuario'], 'Kardex Completo: ' . $nombreMed);
                return;
            }
            if ($_GET['exportar'] === 'excel') {
                $this->exportarExcel('kardex_reporte', $datos, ['Fecha', 'Movimiento', 'Cant.', 'Anterior', 'Posterior', 'Motivo', 'Usuario'], 'Kardex: ' . $nombreMed);
                return;
            }
        }
        
        $tituloPagina = 'Kardex Completo';
        require_once __DIR__ . '/../views/reportes/kardex.php';
    }

    public function auditoria(): void
    {
        $filtros = [
            'fecha_desde' => $_GET['fecha_desde'] ?? date('Y-m-01'),
            'fecha_hasta' => $_GET['fecha_hasta'] ?? date('Y-m-d'),
            'accion' => $_GET['accion'] ?? null,
            'modulo' => $_GET['modulo'] ?? null
        ];
        $datos = $this->modelo->auditoriaMovimientos($filtros);

        if (isset($_GET['exportar']) && $_GET['exportar'] === 'pdf') {
            $this->exportarPDF('auditoria_movimientos', $datos, ['Fecha', 'Usuario', 'Módulo', 'Acción', 'Detalles', 'IP'], 'Auditoria de Movimientos');
            return;
        }
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
            $this->exportarExcel('auditoria_movimientos', $datos, ['Fecha', 'Usuario', 'Módulo', 'Acción', 'Detalles', 'IP'], 'Auditoria Movimientos');
            return;
        }
        
        $tituloPagina = 'Auditoría de Movimientos';
        require_once __DIR__ . '/../views/reportes/auditoria.php';
    }

    public function porPatologia(): void
    {
        $filtros = [
            'patologia_id' => $_GET['patologia_id'] ?? null,
            'fecha_desde' => $_GET['fecha_desde'] ?? date('Y-m-01'),
            'fecha_hasta' => $_GET['fecha_hasta'] ?? date('Y-m-d')
        ];
        $datos = $this->modelo->porPatologia($filtros);
        $patologias = $this->modelo->obtenerPatologias();

        if (isset($_GET['exportar']) && $_GET['exportar'] === 'pdf') {
            $this->exportarPDF('reporte_patologia', $datos, ['Patología', 'Medicamento', 'Cantidad', 'Paciente', 'Cédula', 'Fecha'], 'Reporte por Patología');
            return;
        }
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
            $this->exportarExcel('reporte_patologia', $datos, ['Patología', 'Medicamento', 'Cantidad', 'Paciente', 'Cédula', 'Fecha'], 'Reporte Patología');
            return;
        }
        
        $tituloPagina = 'Reportes por Patología';
        require_once __DIR__ . '/../views/reportes/patologia.php';
    }

    public function prescripcionPaciente(): void
    {
        $cedula = $_GET['cedula'] ?? '';
        $fecha_desde = $_GET['fecha_desde'] ?? date('Y-m-01');
        $fecha_hasta = $_GET['fecha_hasta'] ?? date('Y-m-d');
        
        $datos = [];
        $anomalias = [];
        $paciente = null;

        if (!empty($cedula)) {
            $paciente = $this->modelo->buscarPacientePorCedula($cedula);
            $nombreCompleto = $paciente ? ($paciente['nombre'] . ' ' . $paciente['apellido']) : 'No encontrado';
            
            $datos = $this->modelo->prescripcionPaciente($cedula, $fecha_desde, $fecha_hasta);
            $anomalias = $this->analizarAnomalias($datos);

            if (isset($_GET['exportar']) && $_GET['exportar'] === 'pdf') {
                $titulo = "Relación de Prescripción: $nombreCompleto ($cedula)";
                $this->exportarPDF('prescripcion_paciente', $datos, ['Fecha', 'Ticket', 'Medicamento', 'Cantidad', 'Médico', 'Servicio'], $titulo);
                return;
            }
            if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
                $titulo = "Prescripción Paciente: $nombreCompleto ($cedula)";
                $this->exportarExcel('prescripcion_paciente', $datos, ['Fecha', 'Ticket', 'Medicamento', 'Cantidad', 'Médico', 'Servicio'], $titulo);
                return;
            }
        }

        $tituloPagina = 'Relación de Prescripción por Paciente';
        require_once __DIR__ . '/../views/reportes/paciente.php';
    }

    private function analizarAnomalias(array $datos): array
    {
        $anomalias = [];
        $conteo_medicamentos = [];
        $ultimas_fechas = [];

        foreach ($datos as $d) {
            $med = $d['nombre_generico'];
            
            // Frecuencia
            if (!isset($conteo_medicamentos[$med])) {
                $conteo_medicamentos[$med] = 0;
            }
            $conteo_medicamentos[$med]++;

            // Intervalo entre despachos
            if (isset($ultimas_fechas[$med])) {
                $dias = (int) ((strtotime($ultimas_fechas[$med]) - strtotime($d['fecha'])) / 86400);
                if ($dias < 15 && $dias > 0) {
                    $anomalias[] = "Alerta de Frecuencia: El paciente recibió '$med' con solo $dias días de diferencia (Tickets: {$d['ticket']} y el posterior).";
                }
            }
            $ultimas_fechas[$med] = $d['fecha'];
        }

        foreach ($conteo_medicamentos as $med => $count) {
            if ($count > 3) {
                $anomalias[] = "Sobredemanda: '$med' ha sido despachado $count veces en el período seleccionado.";
            }
        }

        return $anomalias;
    }

    public function alertasCalidad(): void
    {
        $alertas = $this->modelo->alertasGlobal();
        
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'pdf') {
            $this->exportarPDF('alertas_calidad', $alertas, ['Médulo', 'Registro ID', 'Alerta', 'Gravedad', 'Fecha'], 'Alertas de Calidad de Datos');
            return;
        }
        if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
            $this->exportarExcel('alertas_calidad', $alertas, ['Médulo', 'Registro ID', 'Alerta', 'Gravedad', 'Fecha'], 'Alertas Calidad');
            return;
        }

        $tituloPagina = 'Alertas de Calidad de Datos';
        require_once __DIR__ . '/../views/reportes/alertas.php';
    }
}